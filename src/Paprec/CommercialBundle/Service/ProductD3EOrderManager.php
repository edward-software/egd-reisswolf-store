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
use Paprec\CommercialBundle\Entity\ProductD3EOrder;
use Paprec\CommercialBundle\Entity\ProductD3EOrderLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductD3EOrderManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productD3EOrder)
    {
        $id = $productD3EOrder;
        if ($productD3EOrder instanceof ProductD3EOrder) {
            $id = $productD3EOrder->getId();
        }
        try {

            $productD3EOrder = $this->em->getRepository('PaprecCatalogBundle:ProductD3EOrder')->find($id);

            if ($productD3EOrder === null || $this->isDeleted($productD3EOrder)) {
                throw new EntityNotFoundException('productD3EOrderNotFound');
            }

            return $productD3EOrder;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification que le ProductD3EOrder ne soit pas supprimé
     *
     * @param ProductD3EOrder $productD3EOrder
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductD3EOrder $productD3EOrder, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($productD3EOrder->getDeleted() !== null && $productD3EOrder->getDeleted() instanceof \DateTime && $productD3EOrder->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productD3EOrderNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * Ajoute une productD3EOrderLine à un productD3EOrder
     * @param ProductD3EOrder $productD3EOrder
     * @param ProductD3EOrderLine $productD3EOrderLine
     */
    public function addLine(ProductD3EOrder $productD3EOrder, ProductD3EOrderLine $productD3EOrderLine)
    {

        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentOrderLine = $this->em->getRepository('PaprecCommercialBundle:ProductD3EOrderLine')->findOneBy(
            array(
                'productD3EOrder' => $productD3EOrder,
                'productD3E' => $productD3EOrderLine->getProductD3E()
            )
        );

        if ($currentOrderLine) {
            $quantity = $productD3EOrderLine->getQuantity() + $currentOrderLine->getQuantity();
            $currentOrderLine->setQuantity($quantity);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentOrderLine);
            $currentOrderLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            // On lie la grille et la ligne
            $productD3EOrderLine->setProductD3EOrder($productD3EOrder);
            $productD3EOrder->addProductD3EOrderLine($productD3EOrderLine);

            $productD3EOrderLine->setProductName($productD3EOrderLine->getProductD3E()->getName());
            $productD3EOrderLine->setProductSubName($productD3EOrderLine->getProductD3E()->getSubName());


            // Récupération du prix unitaire du produit
            $productD3EOrderLine->setUnitPrice($productD3EOrderLine->getProductD3E()->getPackageUnitPrice());
            $this->em->persist($productD3EOrderLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($productD3EOrderLine);
            $productD3EOrderLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($productD3EOrder);
        $productD3EOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductD3EOrder $productD3EOrder
     * @param ProductD3EOrderLine $productD3EOrderLine
     */
    public function editLine(ProductD3EOrder $productD3EOrder, ProductD3EOrderLine $productD3EOrderLine)
    {

        $totalLine = $this->calculateTotalLine($productD3EOrderLine);
        $productD3EOrderLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productD3EOrder);
        $productD3EOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productD3EOrderLine depuis le Cart, il faut d'abord retrouver le ProductD3EOrder
     * @param ProductD3EOrder $productD3EOrder
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductD3EOrder $productD3EOrder, $productId, $qtty)
    {
        $productD3EManager = $this->container->get('paprec_catalog.product_d3e_manager');

        try {
            $productD3E = $productD3EManager->get($productId);
            $productD3EOrderLine = new ProductD3EOrderLine();

            $productD3EOrderLine->setProductD3E($productD3E);
            $productD3EOrderLine->setQuantity($qtty);

            $this->addLine($productD3EOrder, $productD3EOrderLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductD3EOrder
     * @param ProductD3EOrder $productD3EOrder
     * @return float|int
     */
    public function calculateTotal(ProductD3EOrder $productD3EOrder)
    {
        $totalAmount = 0;
        foreach ($productD3EOrder->getProductD3EOrderLines() as $productD3EOrderLine) {
            $totalAmount += $this->calculateTotalLine($productD3EOrderLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductD3EOrderLine
     * @param ProductD3EOrderLine $productD3EOrderLine
     * @return float|int
     */
    public function calculateTotalLine(ProductD3EOrderLine $productD3EOrderLine)
    {
        $productD3EManager = $this->container->get('paprec_catalog.product_d3e_manager');
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        return round($numberManager->normalize(
                $productD3EManager->calculatePricePackage(
                $productD3EOrderLine->getProductD3EOrder()->getPostalCode(),
                $productD3EOrderLine->getUnitPrice(),
                $productD3EOrderLine->getQuantity()
            )
        ), 2);
    }

    /**
     * Envoie un mail au responsable D3E avec les données de la commande D3E
     *
     * @param ProductD3EOrder $productD3EOrder
     * @return bool
     * @throws Exception
     */
    public function sendNewProductD3EOrderEmail(ProductD3EOrder $productD3EOrder)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_manager_d3e_email');


            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouvelle commande D3E - N°' . $productD3EOrder->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductD3EOrder/emails/sendNewOrderEmail.html.twig',
                        array(
                            'productD3EOrder' => $productD3EOrder
                        )
                    ),
                    'text/html'
                );

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewProductD3EOrder', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Envoi à l'internante de la facture uploadée par le manager
     *
     * @param ProductD3EOrder $productD3EOrder
     * @return bool
     * @throws Exception
     */
    public function sendAssociatedInvoiceMail(ProductD3EOrder $productD3EOrder) {
        try {
            $from = $this->container->getParameter('paprec_email_sender');

            $rcptTo = $productD3EOrder->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-EasyRecyclage-Facture-D3E-'  . $productD3EOrder->getId() . '.pdf';

            if ($productD3EOrder->getAssociatedInvoice()) {
                $filename = $productD3EOrder->getAssociatedInvoice();
                $path = $this->container->getParameter('paprec_commercial.product_d3e_order.files_path');
                $pdfFile = $path . '/' . $filename;
            } else {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre facture pour déchets D3E')
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductD3EOrder/emails/sendAssociatedInvoiceEmail.html.twig',
                        array(
                            'productD3EOrder' => $productD3EOrder
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
            throw new Exception('unableToSendNewProductD3EOrder', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Envoi un récapitulatif de commande au client
     *
     * @param ProductD3EOrder $productD3EOrder
     * @return bool
     * @throws Exception
     */
    public function sendOrderSummaryEmail(ProductD3EOrder $productD3EOrder)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $productD3EOrder->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-EasyRecyclage-Récapitulatif-Commande-' . $productD3EOrder->getId() . '.pdf';

            $pdfFile = $this->generateOrderSummaryPDF($productD3EOrder);

            if (!$pdfFile) {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre récapitulatif de commande N°' . $productD3EOrder->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductD3EOrder/emails/sendOrderSummaryEmail.html.twig',
                        array(
                            'productD3EOrder' => $productD3EOrder
                        )
                    ),
                    'text/html'
                )
                ->attach($attachment);

            if ($this->container->get('mailer')->send($message)) {
                if(file_exists($pdfFile)) {
                    unlink($pdfFile);
                }
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendOrderSummaryProductD3EOrder', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Génère le récapitulatif de commande au format PDF et retoune le nom du fichier généré (placé dans /data/tmp)
     *
     * @param ProductD3EOrder $productD3EOrder
     * @return bool|string
     * @throws Exception
     */
    public function generateOrderSummaryPDF(ProductD3EOrder $productD3EOrder)
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
                        '@PaprecCommercial/ProductD3EOrder/PDF/orderSummaryPDF.html.twig',
                        array(
                            'productD3EOrder' => $productD3EOrder
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
            throw new Exception('unableToGenerateOrderSummaryProductD3EOrder', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
