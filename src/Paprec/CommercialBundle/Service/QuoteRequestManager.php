<?php

namespace Paprec\CommercialBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use DoctrineExtensions\Query\Mysql\Date;
use Exception;
use iio\libmergepdf\Merger;
use Knp\Snappy\Pdf;
use Paprec\CommercialBundle\Entity\ProductDIQuote;
use Paprec\CommercialBundle\Entity\QuoteRequest;
use Paprec\CommercialBundle\Entity\QuoteRequestLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuoteRequestManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($quoteRequest, $throwException = true)
    {
        $id = $quoteRequest;
        if ($quoteRequest instanceof QuoteRequest) {
            $id = $quoteRequest->getId();
        }
        try {

            $quoteRequest = $this->em->getRepository('PaprecCommercialBundle:QuoteRequest')->find($id);

            /**
             * Vérification que le quoteRequest existe ou ne soit pas supprimé
             */
            if ($quoteRequest === null || $this->isDeleted($quoteRequest)) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }


            return $quoteRequest;

        } catch (Exception $e) {
            if ($throwException) {
                throw new Exception($e->getMessage(), $e->getCode());
            } else {
                return null;
            }
        }
    }

    /**
     * Vérifie qu'à ce jour, le quoteRequest ce soit pas supprimé
     *
     * @param QuoteRequest $quoteRequestl
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(QuoteRequest $quoteRequest, $throwException = false)
    {
        $now = new \DateTime();

        if ($quoteRequest->getDeleted() !== null && $quoteRequest->getDeleted() instanceof \DateTime && $quoteRequest->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * Ajoute une quoteRequestLine à un quoteRequest
     * @param QuoteRequest $quoteRequest
     * @param QuoteRequestLine $quoteRequestLine
     * @param null $user
     * @throws Exception
     */
    public function addLine(QuoteRequest $quoteRequest, QuoteRequestLine $quoteRequestLine, $user = null, $doFlush = true)
    {
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentQuoteLine = $this->em->getRepository('PaprecCommercialBundle:QuoteRequestLine')->findOneBy(
            array(
                'quoteRequest' => $quoteRequest,
                'product' => $quoteRequestLine->getProduct()
            )
        );

        if ($currentQuoteLine) {
            $quantity = $quoteRequestLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentQuoteLine);
            $currentQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            $quoteRequestLine->setQuoteRequest($quoteRequest);
            $quoteRequest->addQuoteRequestLine($quoteRequestLine);

            $quoteRequestLine->setSetUpPrice($quoteRequestLine->getProduct()->getSetUpPrice());
            $quoteRequestLine->setRentalUnitPrice($quoteRequestLine->getProduct()->getRentalUnitPrice());
            $quoteRequestLine->setTransportUnitPrice($quoteRequestLine->getProduct()->getTransportUnitPrice());
            $quoteRequestLine->setTreatmentUnitPrice($quoteRequestLine->getProduct()->getTreatmentUnitPrice());
            $quoteRequestLine->setTraceabilityUnitPrice($quoteRequestLine->getProduct()->getTraceabilityUnitPrice());
            $quoteRequestLine->setProductName($quoteRequestLine->getProduct()->getId());

            if ($quoteRequest->getPostalCode()) {
                $quoteRequestLine->setTransportRate($quoteRequest->getPostalCode()->getTransportRate());
                $quoteRequestLine->setTreatmentRate($quoteRequest->getPostalCode()->getTreatmentRate());
                $quoteRequestLine->setTraceabilityRate($quoteRequest->getPostalCode()->getTraceabilityRate());
            } else {
                $quoteRequestLine->setTransportRate($numberManager->normalize15(1));
                $quoteRequestLine->setTreatmentRate($numberManager->normalize15(1));
                $quoteRequestLine->setTraceabilityRate($numberManager->normalize15(1));
            }

            $this->em->persist($quoteRequestLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($quoteRequestLine);
            $quoteRequestLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($quoteRequest);
        $quoteRequest->setTotalAmount($total);
        $quoteRequest->setDateUpdate(new \DateTime());
        $quoteRequest->setUserUpdate($user);
        if ($doFlush) {
            $this->em->flush();
        }
    }


    /**
     * Pour ajouter une QuoteRequestLine depuis le Cart, il faut d'abord retrouver le Product
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(QuoteRequest $quoteRequest, $productId, $qtty, $doFlush = true)
    {
        $productManager = $this->container->get('paprec_catalog.product_manager');

        try {
            $product = $productManager->get($productId);
            $quoteRequestLine = new QuoteRequestLine();

            $quoteRequestLine->setProduct($product);
            $quoteRequestLine->setQuantity($qtty);
            $this->addLine($quoteRequest, $quoteRequestLine, null, $doFlush);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param QuoteRequest $quoteRequest
     * @param QuoteRequestLine $quoteRequestLine
     * @param $user
     * @param bool $doFlush
     * @throws Exception
     */
    public function editLine(QuoteRequest $quoteRequest, QuoteRequestLine $quoteRequestLine, $user, $doFlush = true, $editQuoteRequest = true)
    {
        $now = new \DateTime();

        $totalLine = $this->calculateTotalLine($quoteRequestLine);
        $quoteRequestLine->setTotalAmount($totalLine);
        $quoteRequestLine->setDateUpdate($now);

        if ($editQuoteRequest) {
            $total = $this->calculateTotal($quoteRequest);
            $quoteRequest->setTotalAmount($total);
            $quoteRequest->setDateUpdate($now);
            $quoteRequest->setUserUpdate($user);
        }

        if ($doFlush) {
            $this->em->flush();
        }
    }

    /**
     * Retourne le montant total d'une QuoteRequestLine
     * @param QuoteRequestLine $quoteRequestLine
     * @return float|int
     * @throws Exception
     */
    public function calculateTotalLine(QuoteRequestLine $quoteRequestLine)
    {
        $numberManager = $this->container->get('paprec_catalog.number_manager');
        $productManager = $this->container->get('paprec_catalog.product_manager');

        return $numberManager->normalize(
            $productManager->calculatePrice($quoteRequestLine)
        );
    }

    /**
     * Calcule le montant total d'un QuoteRequest
     * @param QuoteRequest $quoteRequest
     * @return float|int
     */
    public function calculateTotal(QuoteRequest $quoteRequest)
    {
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        $totalAmount = 0;
        if ($quoteRequest->getQuoteRequestLines() && count($quoteRequest->getQuoteRequestLines())) {

            foreach ($quoteRequest->getQuoteRequestLines() as $quoteRequestLine) {
                $totalAmount += $quoteRequestLine->getTotalAmount();
            }
        }
        return $totalAmount * (1 - $numberManager->denormalize($quoteRequest->getOverallDiscount() / 100));
    }


    /**
     * Envoie un mail à la personne ayant fait une demande de devis
     * @throws Exception
     */
    public function sendConfirmRequestEmail(QuoteRequest $quoteRequest, $locale)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $this->get($quoteRequest);

            $rcptTo = $quoteRequest->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $message = \Swift_Message::newInstance()
                ->setSubject('Reisswolf E-shop : Votre demande de devis' . ' N°' . $quoteRequest->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/emails/confirmQuoteEmail.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest,
                            'locale' => $locale
                        )
                    ),
                    'text/html'
                );
            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendConfirmQuoteRequest', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Envoie un mail au commercial associé lui indiquant la nouvelle demande de devis créée
     * @throws Exception
     */
    public function sendNewRequestEmail(QuoteRequest $quoteRequest, $locale)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $this->get($quoteRequest);

            /**
             * Si la quoteRequest est associé à un commercial, on lui envoie le mail d'information de la création d'une nouvelle demande
             * Sinon,
             *      si la demande est multisite alors on envoie au mail générique des demandes multisites
             *          sinon on envoie au mail générique de la région associée au code postal de la demande
             */
            $rcptTo = !is_null($quoteRequest->getUserInCharge()) ? $quoteRequest->getUserInCharge()->getEmail() :
                (!is_null($quoteRequest->getIsMultisite()) ? $this->container->getParameter('reisswolf_salesman_multisite_email') : $quoteRequest->getPostalCode()->getRegion()->getEmail());


            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-Reisswolf-Devis-' . $quoteRequest->getNumber() . '.pdf';

            $pdfFile = $this->generatePDF($quoteRequest, $locale);

            if (!$pdfFile) {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');


            $message = \Swift_Message::newInstance()
                ->setSubject('Reisswolf E-shop : Nouvelle demande de devis' . ' N°' . $quoteRequest->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/emails/newQuoteEmail.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest,
                            'locale' => $locale
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
            throw new Exception('unableToSendNewQuoteRequest', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Génération du numéro de l'offre
     * @param QuoteRequest $quoteRequest
     * @return int
     */
    public function generateNumber(QuoteRequest $quoteRequest)
    {
        return time();
    }


    /**
     * Envoi de l'offre contrat généré au client
     *
     * @param QuoteRequest $quoteRequest
     * @return bool
     * @throws Exception
     */
    public function sendGeneratedQuoteEmail(QuoteRequest $quoteRequest, $locale)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');


            $rcptTo = $quoteRequest->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-Reisswolf-Devis-' . $quoteRequest->getNumber() . '.pdf';

            $pdfFile = $this->generatePDF($quoteRequest, $locale);

            if (!$pdfFile) {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');


            $message = \Swift_Message::newInstance()
                ->setSubject('Reisswolf : Votre devis de prestation ponctuelle pour déchets non dangereux')
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/emails/generatedQuoteEmail.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest,
                            'locale' => $locale
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
            throw new Exception('unableToSendGeneratedQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Génère le devis au format PDF et retoune le nom du fichier généré (placé dans /data/tmp)
     *
     * @param QuoteRequest $quoteRequest
     * @return bool|string
     * @throws Exception
     */
    public function generatePDF(QuoteRequest $quoteRequest, $locale)
    {
        try {
            $pdfTmpFolder = $this->container->getParameter('paprec_commercial.data_tmp_directory');

            if (!is_dir($pdfTmpFolder)) {
                mkdir($pdfTmpFolder, 0755, true);
            }

            $filenameOffer = $pdfTmpFolder . '/' . md5(uniqid()) . '.pdf';
            $filename = $pdfTmpFolder . '/' . md5(uniqid()) . '.pdf';

            $today = new \DateTime();

            $snappy = new Pdf($this->container->getParameter('wkhtmltopdf_path'));
            $snappy->setOption('javascript-delay', 3000);
            $snappy->setOption('dpi', 72);
//            $snappy->setOption('footer-html', $this->container->get('templating')->render('@PaprecCommercial/QuoteRequest/PDF/fr/_footer.html.twig'));


            /**
             * On génère la page d'offre
             */
            $snappy->generateFromHtml(
                array(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/PDF/printQuoteOffer.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest,
                            'date' => $today,
                            'locale' => $locale
                        )
                    )
                ),
                $filenameOffer
            );


            /**
             * On génère la page d'offre
             */
            $snappy->generateFromHtml(
                array(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/PDF/fr/printQuoteContract.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest,
                            'date' => $today
                        )
                    )
                ),
                $filename
            );


            /**
             * Concaténation des notices
             */
            $pdfArray = array();
            $pdfArray[] = $filenameOffer;
            $pdfArray[] = $filename;


            if (count($pdfArray)) {
                $merger = new Merger();
                $merger->addIterator($pdfArray);
                file_put_contents($filename, $merger->merge());
            }

            if (file_exists($filenameOffer)) {
                unlink($filenameOffer);
            }


            if (!file_exists($filename)) {
                return false;
            }

            return $filename;

        } catch (ORMException $e) {
            throw new Exception('unableToGenerateProductQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}
