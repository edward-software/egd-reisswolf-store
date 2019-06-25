<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 16/11/2018
 * Time: 12:08
 */

namespace Paprec\CommercialBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use Exception;
use Knp\Snappy\Pdf;
use Paprec\CatalogBundle\Entity\ProductChantier;
use Paprec\CommercialBundle\Entity\ProductChantierOrder;
use Paprec\CommercialBundle\Entity\ProductChantierOrderLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductChantierOrderManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productChantierOrder)
    {
        $id = $productChantierOrder;
        if ($productChantierOrder instanceof ProductChantierOrder) {
            $id = $productChantierOrder->getId();
        }
        try {

            $productChantierOrder = $this->em->getRepository('PaprecCatalogBundle:ProductChantierOrder')->find($id);

            if ($productChantierOrder === null || $this->isDeleted($productChantierOrder)) {
                throw new EntityNotFoundException('productChantierOrderNotFound');
            }

            return $productChantierOrder;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le productChantierOrder ne soit pas supprimé
     *
     * @param ProductChantierOrder $productChantierOrder
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductChantierOrder $productChantierOrder, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($productChantierOrder->getDeleted() !== null && $productChantierOrder->getDeleted() instanceof \DateTime && $productChantierOrder->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productChantierOrderNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * Ajoute une productChantierOrderLine à un productChantierOrder
     * @param ProductChantierOrder $productChantierOrder
     * @param ProductChantierOrderLine $productChantierOrderLine
     */
    public function addLine(ProductChantierOrder $productChantierOrder, ProductChantierOrderLine $productChantierOrderLine)
    {
        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentOrderLine = $this->em->getRepository('PaprecCommercialBundle:ProductChantierOrderLine')->findOneBy(
            array(
                'productChantierOrder' => $productChantierOrder,
                'productChantier' => $productChantierOrderLine->getProductChantier()
            )
        );

        if ($currentOrderLine) {
            $quantity = $productChantierOrderLine->getQuantity() + $currentOrderLine->getQuantity();
            $currentOrderLine->setQuantity($quantity);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentOrderLine);
            $currentOrderLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            // On lie la grille et la ligne
            $productChantierOrderLine->setProductChantierOrder($productChantierOrder);
            $productChantierOrder->addProductChantierOrderLine($productChantierOrderLine);

            $productChantierOrderLine->setProductName($productChantierOrderLine->getProductChantier()->getName());
            $productChantierOrderLine->setProductSubName($productChantierOrderLine->getProductChantier()->getSubName());


            // Récupération du prix unitaire du produit
            $productChantierOrderLine->setUnitPrice($productChantierOrderLine->getProductChantier()->getPackageUnitPrice());
            $this->em->persist($productChantierOrderLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($productChantierOrderLine);
            $productChantierOrderLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($productChantierOrder);
        $productChantierOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductChantierOrder $productChantierOrder
     * @param ProductChantierOrderLine $productChantierOrderLine
     */
    public function editLine(ProductChantierOrder $productChantierOrder, ProductChantierOrderLine $productChantierOrderLine)
    {
        $totalLine = $this->calculateTotalLine($productChantierOrderLine);
        $productChantierOrderLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productChantierOrder);
        $productChantierOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productChantierOrderLine depuis le Cart, il faut d'abord retrouver le ProductChantier et la catégorie
     * @param ProductChantierOrder $productChantierOrder
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductChantierOrder $productChantierOrder, $productId, $qtty)
    {
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');

        try {
            $productChantier = $productChantierManager->get($productId);
            $productChantierOrderLine = new ProductChantierOrderLine();


            $productChantierOrderLine->setProductChantier($productChantier);
            $productChantierOrderLine->setQuantity($qtty);
            $this->addLine($productChantierOrder, $productChantierOrderLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductChantierOrder
     * @param ProductChantierOrder $productChantierOrder
     * @return float|int
     */
    public function calculateTotal(ProductChantierOrder $productChantierOrder)
    {
        $totalAmount = 0;
        foreach ($productChantierOrder->getProductChantierOrderLines() as $productChantierOrderLine) {
            // Ici, c'est une addition de valeur normalisée donc on retourne la somme telle quelle qui sera bien normalisée
            $totalAmount += $this->calculateTotalLine($productChantierOrderLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductChantierOrderLine
     * La valeur de retour est normalisée
     *
     * @param ProductChantierOrder $productChantierOrder
     * @param ProductChantierOrderLine $productChantierOrderLine
     * @return float|int
     */
    public function calculateTotalLine(ProductChantierOrderLine $productChantierOrderLine)
    {
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        // on normalise le résultat retourné
        return $numberManager->normalize(
            $productChantierManager->calculatePrice(
                $productChantierOrderLine->getProductChantierOrder()->getPostalCode(),
                $productChantierOrderLine->getUnitPrice(),
                $productChantierOrderLine->getQuantity()
            )
        );
    }

    /**
     * Envoie un mail au responsable Chantier avec les données de la commande Chantier
     *
     * @param ProductChantierOrder $productChantierOrder
     * @return bool
     * @throws Exception
     */
    public function sendNewProductChantierOrderEmail(ProductChantierOrder $productChantierOrder)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_manager_chantier_email');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouvelle commande Chantier - N°' . $productChantierOrder->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierOrder/emails/sendNewOrderEmail.html.twig',
                        array(
                            'productChantierOrder' => $productChantierOrder
                        )
                    ),
                    'text/html'
                );

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewProductChantierOrder', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Envoi à l'internante de la facture uploadée par le manager
     *
     * @param ProductChantierOrder $productChantierOrder
     * @return bool
     * @throws Exception
     */
    public function sendAssociatedInvoiceMail(ProductChantierOrder $productChantierOrder)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');

            $rcptTo = $productChantierOrder->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-EasyRecyclage-Facture-Chantier-' . $productChantierOrder->getId() . '.pdf';

            if ($productChantierOrder->getAssociatedInvoice()) {
                $filename = $productChantierOrder->getAssociatedInvoice();
                $path = $this->container->getParameter('paprec_commercial.product_chantier_order.files_path');
                $pdfFile = $path . '/' . $filename;
            } else {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre facture pour déchets de chantier')
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierOrder/emails/sendAssociatedInvoiceEmail.html.twig',
                        array(
                            'productChantierOrder' => $productChantierOrder
                        )
                    ),
                    'text/html'
                )
                ->attach($attachment);

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewProductChantierOrder', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Envoi un récapitulatif de commande au client
     *
     * @param ProductChantierOrder $productChantierOrder
     * @return bool
     * @throws Exception
     */
    public function sendOrderSummaryEmail(ProductChantierOrder $productChantierOrder)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $productChantierOrder->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-EasyRecyclage-Récapitulatif-Commande-' . $productChantierOrder->getId() . '.pdf';

            $pdfFile = $this->generateOrderSummaryPDF($productChantierOrder);

            if (!$pdfFile) {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre récapitulatif de commande N°' . $productChantierOrder->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierOrder/emails/sendOrderSummaryEmail.html.twig',
                        array(
                            'productChantierOrder' => $productChantierOrder
                        )
                    ),
                    'text/html'
                )
                ->attach($attachment);

            if ($this->container->get('mailer')->send($message)) {
                if (file_exists($pdfFile)) {
                    unlink($pdfFile);
                }
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendOrderSummaryProductChantierOrder', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Génère le récapitulatif de commande au format PDF et retoune le nom du fichier généré (placé dans /data/tmp)
     *
     * @param ProductChantierOrder $productChantierOrder
     * @return bool|string
     * @throws Exception
     */
    public function generateOrderSummaryPDF(ProductChantierOrder $productChantierOrder)
    {
        try {
            $pdfTmpFolder = $this->container->getParameter('paprec_commercial.data_tmp_directory');

            if (!is_dir($pdfTmpFolder)) {
                mkdir($pdfTmpFolder, 0755, true);
            }

            $filename = $pdfTmpFolder . '/' . md5(uniqid()) . '.pdf';

            $snappy = new Pdf($this->container->getParameter('wkhtmltopdf_path'));
            $snappy->generateFromHtml(
                array(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierOrder/PDF/orderSummaryPDF.html.twig',
                        array(
                            'productChantierOrder' => $productChantierOrder
                        )
                    )
                ),
                $filename
            );

            /**
             * Concaténation des notices
             */
            $pdfArray = array();
            $pdfArray[] = $filename;

            if (!file_exists($filename)) {
                return false;
            }
            return $filename;

        } catch (ORMException $e) {
            throw new Exception('unableToGenerateOrderSummaryProductChantierOrder', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
