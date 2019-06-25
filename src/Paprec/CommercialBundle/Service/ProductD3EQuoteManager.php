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
use Paprec\CommercialBundle\Entity\ProductD3EQuote;
use Paprec\CommercialBundle\Entity\ProductD3EQuoteLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductD3EQuoteManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productD3EQuote)
    {
        $id = $productD3EQuote;
        if ($productD3EQuote instanceof ProductD3EQuote) {
            $id = $productD3EQuote->getId();
        }
        try {

            $productD3EQuote = $this->em->getRepository('PaprecCatalogBundle:ProductD3EQuote')->find($id);

            if ($productD3EQuote === null || $this->isDeleted($productD3EQuote)) {
                throw new EntityNotFoundException('productD3EQuoteNotFound');
            }

            return $productD3EQuote;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le productD3EQuote ne soit pas supprimé
     *
     * @param ProductD3EQuote $productD3EQuote
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductD3EQuote $productD3EQuote, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($productD3EQuote->getDeleted() !== null && $productD3EQuote->getDeleted() instanceof \DateTime && $productD3EQuote->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productD3EQuoteNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * Ajoute une productD3EQuoteLine à un productD3EQuote
     * @param ProductD3EQuote $productD3EQuote
     * @param ProductD3EQuoteLine $productD3EQuoteLine
     */
    public function addLine(ProductD3EQuote $productD3EQuote, ProductD3EQuoteLine $productD3EQuoteLine)
    {
        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentQuoteLine = $this->em->getRepository('PaprecCommercialBundle:ProductD3EQuoteLine')->findOneBy(
            array(
                'productD3EQuote' => $productD3EQuote,
                'productD3E' => $productD3EQuoteLine->getProductD3E(),
                'type' => $productD3EQuoteLine->getType()
            )
        );

        if ($currentQuoteLine) {
            $quantity = $productD3EQuoteLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);
            
            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentQuoteLine);
            $currentQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            $productD3EQuoteLine->setProductD3EQuote($productD3EQuote);
            $productD3EQuote->addProductD3EQuoteLine($productD3EQuoteLine);
            $productD3EType = $this->em->getRepository('PaprecCatalogBundle:ProductD3EType')->findOneBy(
                array(
                    'productD3E' => $productD3EQuoteLine->getProductD3E(),
                    'type' => $productD3EQuoteLine->getType()
                )
            );
            $productD3EQuoteLine->setUnitPrice($productD3EType->getUnitPrice());
            $productD3EQuoteLine->setProductName($productD3EQuoteLine->getProductD3E()->getName());
            $productD3EQuoteLine->setTypeName($productD3EQuoteLine->getType()->getName());

            $this->em->persist($productD3EQuoteLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($productD3EQuoteLine);
            $productD3EQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($productD3EQuote);
        $productD3EQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductD3EQuote $productD3EQuote
     * @param ProductD3EQuoteLine $productD3EQuoteLine
     */
    public function editLine(ProductD3EQuote $productD3EQuote, ProductD3EQuoteLine $productD3EQuoteLine)
    {
        $totalLine = $this->calculateTotalLine($productD3EQuoteLine);
        $productD3EQuoteLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productD3EQuote);
        $productD3EQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productD3EQuoteLine depuis le Cart, il faut d'abord retrouver le ProductD3E
     * @param ProductD3EQuote $productD3EQuote
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductD3EQuote $productD3EQuote, $productId, $typeId, $qtty, $optHandling, $optSerialNumberStmt, $optDestruction)
    {
        $productD3EManager = $this->container->get('paprec_catalog.product_d3e_manager');
        $typeManager = $this->container->get('paprec_catalog.type_manager');

        try {
            $productD3E = $productD3EManager->get($productId);
            $type = $typeManager->get($typeId);

            $productD3EQuoteLine = new ProductD3EQuoteLine();

            $productD3EQuoteLine->setOptHandling($optHandling);
            $productD3EQuoteLine->setOptSerialNumberStmt($optSerialNumberStmt);
            $productD3EQuoteLine->setOptDestruction($optDestruction);
            $productD3EQuoteLine->setProductD3E($productD3E);
            $productD3EQuoteLine->setType($type);
            $productD3EQuoteLine->setQuantity($qtty);
            $this->addLine($productD3EQuote, $productD3EQuoteLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductD3EQuote
     * @param ProductD3EQuote $productD3EQuote
     * @return float|int
     */
    public function calculateTotal(ProductD3EQuote $productD3EQuote)
    {
        $totalAmount = 0;
        foreach ($productD3EQuote->getProductD3EQuoteLines() as $productD3EQuoteLine) {
            $totalAmount += $this->calculateTotalLine($productD3EQuoteLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductD3EQuoteLine
     * @param ProductD3EQuoteLine $productD3EQuoteLine
     * @return float|int
     */
    public function calculateTotalLine(ProductD3EQuoteLine $productD3EQuoteLine)
    {
        $numberManager = $this->container->get('paprec_catalog.number_manager');
        $productD3EManager = $this->container->get('paprec_catalog.product_d3e_manager');

        return $numberManager->normalize(
            $productD3EManager->calculatePrice(
                $productD3EQuoteLine->getProductD3E(),
                $productD3EQuoteLine->getProductD3EQuote()->getPostalCode(),
                $productD3EQuoteLine->getUnitPrice(),
                $productD3EQuoteLine->getQuantity(),
                $productD3EQuoteLine->getOptHandling(),
                $productD3EQuoteLine->getOptSerialNumberStmt(),
                $productD3EQuoteLine->getOptDestruction()
            )
        );
    }

    /**
     * Envoie un mail au responsable D3E avec les données du du devis D3E
     *
     * @param ProductD3EQuote $productD3EQuote
     * @return bool
     * @throws Exception
     */
    public function sendNewProductD3EQuoteEmail(ProductD3EQuote $productD3EQuote)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_manager_d3e_email');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouveau devis D3E - N°' . $productD3EQuote->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductD3EQuote/emails/sendNewQuoteEmail.html.twig',
                        array(
                            'productD3EQuote' => $productD3EQuote
                        )
                    ),
                    'text/html'
                );

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewProductD3EQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Envoie du devis généré au client
     *
     * @param ProductD3EQuote $productD3EQuote
     * @return bool
     * @throws Exception
     */
    public function sendGeneratedQuoteEmail(ProductD3EQuote $productD3EQuote)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');


            $rcptTo = $productD3EQuote->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            /*$pdfFilename = date('Y-m-d') . '-EasyRecyclage-Devis-' . $productD3EQuote->getId() . '.pdf';

            $pdfFile = $this->generatePDF($productD3EQuote);

            if (!$pdfFile) {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');*/


            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre devis de prestation ponctuelle pour D3E')
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductD3EQuote/emails/sendGeneratedQuoteEmail.html.twig',
                        array(
                            'productD3EQuote' => $productD3EQuote
                        )
                    ),
                    'text/html'
                );
//                ->attach($attachment);

            if ($this->container->get('mailer')->send($message)) {
//                if(file_exists($pdfFile)) {
//                    unlink($pdfFile);
//                }
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendGeneratedProductD3EQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Génère le devis au format PDF et retoune le nom du fichier généré (placé dans /data/tmp)
     *
     * @param ProductD3EQuote $productD3EQuote
     * @return bool|string
     * @throws Exception
     */
    public function generatePDF(ProductD3EQuote $productD3EQuote)
    {
        try {
            $pdfTmpFolder = $this->container->getParameter('paprec_commercial.data_tmp_directory');
            $noticeFileDirectory = $this->container->getParameter('paprec_commercial.quote_pdf_notice_directory');
            $noticeFiles = $this->container->getParameter('paprec_commercial.quote_pdf_notices');

            if (!is_dir($pdfTmpFolder)) {
                mkdir($pdfTmpFolder, 0755, true);
            }

            $filename = $pdfTmpFolder . '/' . md5(uniqid()) . '.pdf';

            $today = new \DateTime();

            $snappy = new Pdf($this->container->getParameter('wkhtmltopdf_path'));
            $snappy->generateFromHtml(
                array(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductD3EQuote/PDF/printQuoteCover.html.twig',
                        array(
                            'productD3EQuote' => $productD3EQuote,
                            'date' => $today
                        )
                    ),
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductD3EQuote/PDF/printQuoteLetter.html.twig',
                        array(
                            'productD3EQuote' => $productD3EQuote,
                            'date' => $today
                        )
                    ),
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ProductD3EQuote/PDF/printQuoteProducts.html.twig',
                        array(
                            'productD3EQuote' => $productD3EQuote,
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

            if (!file_exists($filename)) {
                return false;
            }
            return $filename;

        } catch (ORMException $e) {
            throw new Exception('unableToGenerateProductD3EQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
