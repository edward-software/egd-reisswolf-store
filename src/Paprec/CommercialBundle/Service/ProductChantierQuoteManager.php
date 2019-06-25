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
use iio\libmergepdf\Merger;
use Knp\Snappy\Pdf;
use Paprec\CommercialBundle\Entity\ProductChantierQuote;
use Paprec\CommercialBundle\Entity\ProductChantierQuoteLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductChantierQuoteManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productChantierQuote)
    {
        $id = $productChantierQuote;
        if ($productChantierQuote instanceof ProductChantierQuote) {
            $id = $productChantierQuote->getId();
        }
        try {

            $productChantierQuote = $this->em->getRepository('PaprecCatalogBundle:ProductChantierQuote')->find($id);

            if ($productChantierQuote === null || $this->isDeleted($productChantierQuote)) {
                throw new EntityNotFoundException('productChantierQuoteNotFound');
            }

            return $productChantierQuote;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérificatiion que le ProductChantierQuote ne soit pas supprimé
     *
     * @param ProductChantierQuote $productChantierQuote
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductChantierQuote $productChantierQuote, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($productChantierQuote->getDeleted() !== null && $productChantierQuote->getDeleted() instanceof \DateTime && $productChantierQuote->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productChantierQuoteNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * Ajoute une productChantierQuoteLine à un productChantierQuote
     * @param ProductChantierQuote $productChantierQuote
     * @param ProductChantierQuoteLine $productChantierQuoteLine
     */
    public function addLine(ProductChantierQuote $productChantierQuote, ProductChantierQuoteLine $productChantierQuoteLine)
    {
        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentQuoteLine = $this->em->getRepository('PaprecCommercialBundle:ProductChantierQuoteLine')->findOneBy(
            array(
                'productChantierQuote' => $productChantierQuote,
                'productChantier' => $productChantierQuoteLine->getProductChantier(),
                'category' => $productChantierQuoteLine->getCategory()
            )
        );

        if ($currentQuoteLine) {
            $quantity = $productChantierQuoteLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentQuoteLine);
            $currentQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            $productChantierQuoteLine->setProductChantierQuote($productChantierQuote);
            $productChantierQuote->addProductChantierQuoteLine($productChantierQuoteLine);
            $productChantierCategory = $this->em->getRepository('PaprecCatalogBundle:ProductChantierCategory')->findOneBy(
                array(
                    'productChantier' => $productChantierQuoteLine->getProductChantier(),
                    'category' => $productChantierQuoteLine->getCategory()
                )
            );
            $productChantierQuoteLine->setUnitPrice($productChantierCategory->getUnitPrice());
            $productChantierQuoteLine->setProductName($productChantierQuoteLine->getProductChantier()->getName());
            $productChantierQuoteLine->setCategoryName($productChantierQuoteLine->getCategory()->getName());

            $this->em->persist($productChantierQuoteLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($productChantierQuoteLine);
            $productChantierQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($productChantierQuote);
        $productChantierQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductChantierQuote $productChantierQuote
     * @param ProductChantierQuoteLine $productChantierQuoteLine
     */
    public function editLine(ProductChantierQuote $productChantierQuote, ProductChantierQuoteLine $productChantierQuoteLine)
    {
        $totalLine = $this->calculateTotalLine($productChantierQuoteLine);
        $productChantierQuoteLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productChantierQuote);
        $productChantierQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productChantierQuoteLine depuis le Cart, il faut d'abord retrouver le ProductChantier
     * @param ProductChantierQuote $productChantierQuote
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductChantierQuote $productChantierQuote, $productId, $qtty, $categoryId)
    {
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        try {
            $productChantier = $productChantierManager->get($productId);
            $productChantierQuoteLine = new ProductChantierQuoteLine();
            $category = $categoryManager->get($categoryId);


            $productChantierQuoteLine->setProductChantier($productChantier);
            $productChantierQuoteLine->setCategory($category);
            $productChantierQuoteLine->setQuantity($qtty);
            $this->addLine($productChantierQuote, $productChantierQuoteLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductChantierQuote
     * @param ProductChantierQuote $productChantierQuote
     * @return float|int
     */
    public function calculateTotal(ProductChantierQuote $productChantierQuote)
    {
        $totalAmount = 0;
        foreach ($productChantierQuote->getProductChantierQuoteLines() as $productChantierQuoteLine) {
            // Ici, c'est une addition de valeur normalisée donc on retourne la somme telle quelle qui sera bien normalisée
            $totalAmount += $this->calculateTotalLine($productChantierQuoteLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductChantierQuoteLine
     * @param ProductChantierQuote $productChantierQuote
     * @param ProductChantierQuoteLine $productChantierQuoteLine
     * @return float|int
     */
    public function calculateTotalLine(ProductChantierQuoteLine $productChantierQuoteLine)
    {
        $numberManager = $this->container->get('paprec_catalog.number_manager');
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');

        return $numberManager->normalize(
            $productChantierManager->calculatePrice(
                $productChantierQuoteLine->getProductChantierQuote()->getPostalCode(),
                $productChantierQuoteLine->getUnitPrice(),
                $productChantierQuoteLine->getQuantity())
        );
    }

    /**
     * Envoie un mail au responsable Chantier avec les données du devis Chantier
     *
     * @param ProductChantierQuote $productChantierQuote
     * @return bool
     * @throws Exception
     */
    public function sendNewProductChantierQuoteEmail(ProductChantierQuote $productChantierQuote)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_manager_chantier_email');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouveau devis Chantier - N°' . $productChantierQuote->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierQuote/emails/sendNewQuoteEmail.html.twig',
                        array(
                            'productChantierQuote' => $productChantierQuote
                        )
                    ),
                    'text/html'
                );

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewProductChantierQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }



    /**
     * Envoie du devis généré au client
     *
     * @param ProductChantierQuote $productChantierQuote
     * @return bool
     * @throws Exception
     */
    public function sendGeneratedQuoteEmail(ProductChantierQuote $productChantierQuote)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');


            $rcptTo = $productChantierQuote->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-EasyRecyclage-Devis-' . $productChantierQuote->getId() . '.pdf';

            $pdfFile = $this->generatePDF($productChantierQuote);

            if (!$pdfFile) {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');


            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre devis de prestation ponctuelle pour déchets de chantier')
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierQuote/emails/sendGeneratedQuoteEmail.html.twig',
                        array(
                            'productChantierQuote' => $productChantierQuote
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
            throw new Exception('unableToSendGeneratedProductChantierQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Génère le devis au format PDF et retoune le nom du fichier généré (placé dans /data/tmp)
     *
     * @param ProductChantierQuote $productChantierQuote
     * @return bool|string
     * @throws Exception
     */
    public function generatePDF(ProductChantierQuote $productChantierQuote)
    {
        try {
            $pdfTmpFolder = $this->container->getParameter('paprec_commercial.data_tmp_directory');
            $noticeFileDirectory = $this->container->getParameter('paprec_commercial.quote_pdf_notice_directory');
            $noticeFiles = $this->container->getParameter('paprec_commercial.quote_pdf_notices');

            if (!is_dir($pdfTmpFolder)) {
                mkdir($pdfTmpFolder, 0755, true);
            }

            $filenameCover = $pdfTmpFolder . '/' . md5(uniqid()) . '.pdf';
            $filename = $pdfTmpFolder . '/' . md5(uniqid()) . '.pdf';

            $today = new \DateTime();

            $snappy = new Pdf($this->container->getParameter('wkhtmltopdf_path'));
            $snappy->setOption('margin-left', 3);
            $snappy->setOption('margin-right', 3);
            /**
             * On génère d'abord la page de couverture sans footer
             */
            $snappy->generateFromHtml(
                array(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierQuote/PDF/printQuoteCover.html.twig',
                        array(
                            'productChantierQuote' => $productChantierQuote,
                            'date' => $today
                        )
                    )
                ),
                $filenameCover
            );

            /**
             * Puis les pages suivantes avec le footer
             */
            $snappy->setOption('footer-html', $this->container->get('templating')->render('@PaprecCommercial/Common/PDF/partials/footer.html.twig'));
            $snappy->generateFromHtml(
                array(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierQuote/PDF/printQuoteLetter.html.twig',
                        array(
                            'productChantierQuote' => $productChantierQuote,
                            'date' => $today
                        )
                    ),
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductChantierQuote/PDF/printQuoteProducts.html.twig',
                        array(
                            'productChantierQuote' => $productChantierQuote,
                        )
                    )
                ),
                $filename
            );

            /**
             * Concaténation des notices
             */
            $pdfArray = array();
            $pdfArray[] = $filenameCover;
            $pdfArray[] = $filename;

            if (is_array($noticeFiles) && count($noticeFiles)) {
                foreach ($noticeFiles as $noticeFile) {
                    $noticeFilename = $noticeFileDirectory . '/' . $noticeFile;
                    if (file_exists($noticeFilename)) {
                        $pdfArray[] = $noticeFilename;
                    }
                }
            }

            if (count($pdfArray)) {
                $merger = new Merger();
                $merger->addIterator($pdfArray);
                file_put_contents($filename, $merger->merge());
            }

            if (file_exists($filenameCover)) {
                unlink($filenameCover);
            }

            if (!file_exists($filename)) {
                return false;
            }
            return $filename;

        } catch (ORMException $e) {
            throw new Exception('unableToGenerateProductChantierQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
