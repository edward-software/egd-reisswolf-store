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
use Paprec\CommercialBundle\Entity\ProductDIQuote;
use Paprec\CommercialBundle\Entity\ProductDIQuoteLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductDIQuoteManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productDIQuote)
    {
        $id = $productDIQuote;
        if ($productDIQuote instanceof ProductDIQuote) {
            $id = $productDIQuote->getId();
        }
        try {

            $productDIQuote = $this->em->getRepository('PaprecCatalogBundle:ProductDIQuote')->find($id);

            if ($productDIQuote === null || $this->isDeleted($productDIQuote)) {
                throw new EntityNotFoundException('productDIQuoteNotFound');
            }

            return $productDIQuote;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le ProductDIQuote ne soit pas supprimé
     *
     * @param ProductDIQuote $productDIQuote
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductDIQuote $productDIQuote, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($productDIQuote->getDeleted() !== null && $productDIQuote->getDeleted() instanceof \DateTime && $productDIQuote->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productDIQuoteNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductDIQuote $productDIQuote
     * @param ProductDIQuoteLine $productDIQuoteLine
     */
    public function editLine(ProductDIQuote $productDIQuote, ProductDIQuoteLine $productDIQuoteLine)
    {
        $totalLine = $this->calculateTotalLine($productDIQuoteLine);
        $productDIQuoteLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productDIQuote);
        $productDIQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Retourne le montant total d'une ProductDIQuoteLine
     * @param ProductDIQuoteLine $productDIQuoteLine
     * @return float|int
     */
    public function calculateTotalLine(ProductDIQuoteLine $productDIQuoteLine)
    {
        $numberManager = $this->container->get('paprec_catalog.number_manager');
        $productDIManager = $this->container->get('paprec_catalog.product_di_manager');

        return $numberManager->normalize(
            $productDIManager->calculatePrice(
                $productDIQuoteLine->getProductDIQuote()->getPostalCode(),
                $productDIQuoteLine->getUnitPrice(),
                $productDIQuoteLine->getQuantity()
            )
        );
    }

    /**
     * Calcule le montant total d'un ProductDIQuote
     * @param ProductDIQuote $productDIQuote
     * @return float|int
     */
    public function calculateTotal(ProductDIQuote $productDIQuote)
    {
        $totalAmount = 0;
        foreach ($productDIQuote->getProductDIQuoteLines() as $productDIQuoteLine) {
            $totalAmount += $this->calculateTotalLine($productDIQuoteLine);
        }
        return $totalAmount;

    }

    /**
     * Pour ajouter une productDIQuoteLine depuis le Cart, il faut d'abord retrouver le ProductDI
     * @param ProductDIQuote $productDIQuote
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductDIQuote $productDIQuote, $productId, $qtty, $categoryId)
    {
        $productDIManager = $this->container->get('paprec_catalog.product_di_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        try {
            $productDI = $productDIManager->get($productId);
            $productDIQuoteLine = new ProductDIQuoteLine();
            $category = $categoryManager->get($categoryId);


            $productDIQuoteLine->setProductDI($productDI);
            $productDIQuoteLine->setCategory($category);
            $productDIQuoteLine->setQuantity($qtty);
            $this->addLine($productDIQuote, $productDIQuoteLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Ajoute une productQuoteDiLine à un productDIQuote
     * @param ProductDIQuote $productDIQuote
     * @param ProductDIQuoteLine $productDIQuoteLine
     */
    public function addLine(ProductDIQuote $productDIQuote, ProductDIQuoteLine $productDIQuoteLine)
    {

        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentQuoteLine = $this->em->getRepository('PaprecCommercialBundle:ProductDIQuoteLine')->findOneBy(
            array(
                'productDIQuote' => $productDIQuote,
                'productDI' => $productDIQuoteLine->getProductDI(),
                'category' => $productDIQuoteLine->getCategory()
            )
        );

        if ($currentQuoteLine) {
            $quantity = $productDIQuoteLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentQuoteLine);
            $currentQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            $productDIQuoteLine->setProductDIQuote($productDIQuote);
            $productDIQuote->addProductDIQuoteLine($productDIQuoteLine);
            $productDICategory = $this->em->getRepository('PaprecCatalogBundle:ProductDICategory')->findOneBy(
                array(
                    'productDI' => $productDIQuoteLine->getProductDI(),
                    'category' => $productDIQuoteLine->getCategory()
                )
            );
            $productDIQuoteLine->setUnitPrice($productDICategory->getUnitPrice());
            $productDIQuoteLine->setProductName($productDIQuoteLine->getProductDI()->getName());
            $productDIQuoteLine->setCategoryName($productDIQuoteLine->getCategory()->getName());

            $this->em->persist($productDIQuoteLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($productDIQuoteLine);
            $productDIQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($productDIQuote);
        $productDIQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Envoie un mail au responsable DI avec les données du du devis DI
     *
     * @param ProductDIQuote $productDIQuote
     * @return bool
     * @throws Exception
     */
    public function sendNewProductDIQuoteEmail(ProductDIQuote $productDIQuote)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_manager_di_email');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouveau devis DI - N°' . $productDIQuote->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductDIQuote/emails/sendNewQuoteEmail.html.twig',
                        array(
                            'productDIQuote' => $productDIQuote
                        )
                    ),
                    'text/html'
                );

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewProductDIQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Envoie du devis généré au client
     *
     * @param ProductDIQuote $productDIQuote
     * @return bool
     * @throws Exception
     */
    public function sendGeneratedQuoteEmail(ProductDIQuote $productDIQuote)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');


            $rcptTo = $productDIQuote->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-EasyRecyclage-Devis-' . $productDIQuote->getId() . '.pdf';

            $pdfFile = $this->generatePDF($productDIQuote);

            if (!$pdfFile) {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');


            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre devis de prestation ponctuelle pour déchets non dangereux')
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductDIQuote/emails/sendGeneratedQuoteEmail.html.twig',
                        array(
                            'productDIQuote' => $productDIQuote
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
            throw new Exception('unableToSendGeneratedProductDIQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Génère le devis au format PDF et retoune le nom du fichier généré (placé dans /data/tmp)
     *
     * @param ProductDIQuote $productDIQuote
     * @return bool|string
     * @throws Exception
     */
    public function generatePDF(ProductDIQuote $productDIQuote)
    {
        try {
            $pdfTmpFolder = $this->container->getParameter('paprec_commercial.data_tmp_directory');
            $noticeFileDirectory = $this->container->getParameter('paprec_commercial.di_quote_pdf_notice_directory');
            $noticeFiles = $this->container->getParameter('paprec_commercial.di_quote_pdf_notices');

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
             * On génère les PDF
             */
            $snappy->generateFromHtml(
                array(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductDIQuote/PDF/printQuoteCover.html.twig',
                        array(
                            'productDIQuote' => $productDIQuote,
                            'date' => $today
                        )
                    ),
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductDIQuote/PDF/printQuoteLetter.html.twig',
                        array(
                            'productDIQuote' => $productDIQuote,
                            'date' => $today
                        )
                    ),
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductDIQuote/PDF/printQuoteProducts.html.twig',
                        array(
                            'productDIQuote' => $productDIQuote,
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
            throw new Exception('unableToGenerateProductDIQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
