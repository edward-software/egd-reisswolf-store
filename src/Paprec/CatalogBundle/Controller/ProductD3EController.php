<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\Picture;
use Paprec\CatalogBundle\Entity\ProductD3E;
use Paprec\CatalogBundle\Entity\ProductD3EType;

use Paprec\CatalogBundle\Form\PictureProductType;
use Paprec\CatalogBundle\Form\ProductD3EPackageType;
use Paprec\CatalogBundle\Form\ProductD3EType as ProductD3ETypeForm;

use Paprec\CatalogBundle\Form\ProductD3ETypeAddType;
use Paprec\CatalogBundle\Form\ProductD3ETypeEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductD3EController extends Controller
{
    /**
     * @Route("/productD3E", name="paprec_catalog_productD3E_index")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:ProductD3E:index.html.twig');
    }

    /**
     * @Route("/productD3E/loadList", name="paprec_catalog_productD3E_loadList")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function loadListAction(Request $request)
    {
        $return = array();

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');
        // Récupération du type de ProductD3E souhaité (package ou non)
        $isPackage = $request->get('isPackage');

        $cols['id'] = array('label' => 'id', 'id' => 'p.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'p.name', 'method' => array('getName'));
        $cols['description'] = array('label' => 'description', 'id' => 'p.description', 'method' => array('getDescription'));
        $cols['position'] = array('label' => 'position', 'id' => 'p.position', 'method' => array('getPosition'));
        $cols['isDisplayed'] = array('label' => 'isDisplayed', 'id' => 'p.isDisplayed', 'method' => array('getIsDisplayed'));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:ProductD3E', 'p')
            ->where('p.deleted IS NULL')
            ->andWhere('p.isPackage = ' . $isPackage); // Récupération des productD3E du type voulu


        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('p.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('p.name', '?1'),
                    $queryBuilder->expr()->like('p.description', '?1'),
                    $queryBuilder->expr()->like('p.position', '?1'),
                    $queryBuilder->expr()->like('p.isDisplayed', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);

    }

    /**
     * @Route("/productD3E/export",  name="paprec_catalog_productD3E_export")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function exportAction()
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:ProductD3E', 'p')
            ->where('p.deleted IS NULL');

        $productD3Es = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Produits D3E")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nom')
            ->setCellValue('C1', 'Description')
            ->setCellValue('D1', 'Coef. manutention')
            ->setCellValue('E1', 'Coef. relevé n° série')
            ->setCellValue('F1', 'Coef destruction')
            ->setCellValue('G1', 'Lien description')
            ->setCellValue('H1', 'Statut affichage')
            ->setCellValue('I1', 'Position')
            ->setCellValue('J1', 'Dispo géographique')
            ->setCellValue('K1', 'Date création');


        $phpExcelObject->getActiveSheet()->setTitle('Produits D3E');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productD3Es as $productD3E) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productD3E->getId())
                ->setCellValue('B' . $i, $productD3E->getName())
                ->setCellValue('C' . $i, $productD3E->getDescription())
                ->setCellValue('D' . $i, $numberManager->denormalize($productD3E->getCoefHandling()))
                ->setCellValue('E' . $i, $numberManager->denormalize($productD3E->getCoefSerialNumberStmt()))
                ->setCellValue('F' . $i, $numberManager->denormalize($productD3E->getCoefDestruction()))
                ->setCellValue('G' . $i, $productD3E->getReference())
                ->setCellValue('H' . $i, $productD3E->getIsDisplayed())
                ->setCellValue('I' . $i, $productD3E->getPosition())
                ->setCellValue('J' . $i, $productD3E->getAvailablePostalCodes())
                ->setCellValue('K' . $i, $productD3E->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Produits-D3E-' . date('Y-m-d') . '.xlsx';

        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * Vue d'une produit D3E sur mesure
     * @Route("/productD3E/view/{id}",  name="paprec_catalog_productD3E_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function viewAction(Request $request, ProductD3E $productD3E)
    {
        $productD3EManager = $this->get('paprec_catalog.product_d3e_manager');
        $productD3EManager->isDeleted($productD3E, true);

        foreach ($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $picture = new Picture();

        $formAddPicture = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));

        $formEditPicture = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));

        return $this->render('PaprecCatalogBundle:ProductD3E:view.html.twig', array(
            'productD3E' => $productD3E,
            'formAddPicture' => $formAddPicture->createView(),
            'formEditPicture' => $formEditPicture->createView()
        ));
    }

    /**
     * Vue d'une produit D3E packagé
     * @Route("/productD3E/view/packaged/{id}",  name="paprec_catalog_productD3E_packaged_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function viewPackagedAction(Request $request, ProductD3E $productD3E)
    {
        $productD3EManager = $this->get('paprec_catalog.product_d3e_manager');
        $productD3EManager->isDeleted($productD3E, true);

        foreach ($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $picture = new Picture();

        $formAddPicture = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));

        $formEditPicture = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));

        return $this->render('@PaprecCatalog/ProductD3E/package/viewPackage.html.twig', array(
            'productD3E' => $productD3E,
            'formAddPicture' => $formAddPicture->createView(),
            'formEditPicture' => $formEditPicture->createView()
        ));
    }

    /**
     * Ajoute un produit D3E sur mesure
     * @Route("/productD3E/add",  name="paprec_catalog_productD3E_add")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Exception
     */
    public function addAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $productD3E = new ProductD3E();

        $form = $this->createForm(ProductD3ETypeForm::class, $productD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productD3E = $form->getData();
            $productD3E->setIsPackage(false);

            $productD3E->setCoefHandling($numberManager->normalize($productD3E->getCoefHandling()));
            $productD3E->setCoefSerialNumberStmt($numberManager->normalize($productD3E->getCoefSerialNumberStmt()));
            $productD3E->setCoefDestruction($numberManager->normalize($productD3E->getCoefDestruction()));

            $productD3E->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($productD3E);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:ProductD3E:add.html.twig', array(
            'form' => $form->createView()
        ));
    }


    /**
     * Ajoute un produit D3E packagé
     * @Route("/productD3E/packaged/add",  name="paprec_catalog_productD3E_packaged_add")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Exception
     */
    public function addPackagedAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $productD3E = new ProductD3E();

        $form = $this->createForm(ProductD3EPackageType::class, $productD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productD3E = $form->getData();

            $productD3E->setIsPackage(true);
            $productD3E->setPackageUnitPrice($numberManager->normalize($productD3E->getPackageUnitPrice()));
            $productD3E->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($productD3E);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productD3E_packaged_view', array(
                'id' => $productD3E->getId(),
                'isPackage' => true
            ));

        }

        return $this->render('@PaprecCatalog/ProductD3E/package/addPackage.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/productD3E/edit/{id}",  name="paprec_catalog_productD3E_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, ProductD3E $productD3E)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $productD3EManager = $this->get('paprec_catalog.product_d3e_manager');
        $productD3EManager->isDeleted($productD3E, true);

        $productD3E->setCoefHandling($numberManager->denormalize($productD3E->getCoefHandling()));
        $productD3E->setCoefSerialNumberStmt($numberManager->denormalize($productD3E->getCoefSerialNumberStmt()));
        $productD3E->setCoefDestruction($numberManager->denormalize($productD3E->getCoefDestruction()));
        $form = $this->createForm(ProductD3ETypeForm::class, $productD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productD3E = $form->getData();

            $productD3E->setCoefHandling($numberManager->normalize($productD3E->getCoefHandling()));
            $productD3E->setCoefSerialNumberStmt($numberManager->normalize($productD3E->getCoefSerialNumberStmt()));
            $productD3E->setCoefDestruction($numberManager->normalize($productD3E->getCoefDestruction()));

            $productD3E->setDateUpdate(new \DateTime);

            $em = $this->getDoctrine()->getManager();

            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductD3E:edit.html.twig', array(
            'form' => $form->createView(),
            'productD3E' => $productD3E
        ));
    }


    /**
     * Edition d'un produit packagé
     * @Route("/productD3E/edit/packaged/{id}",  name="paprec_catalog_productD3E_packaged_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editPackagedAction(Request $request, ProductD3E $productD3E)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $productD3EManager = $this->get('paprec_catalog.product_d3e_manager');
        $productD3EManager->isDeleted($productD3E, true);

        $productD3E->setPackageUnitPrice($numberManager->denormalize($productD3E->getPackageUnitPrice()));

        $form = $this->createForm(ProductD3EPackageType::class, $productD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productD3E = $form->getData();

            $productD3E->setPackageUnitPrice($numberManager->normalize($productD3E->getPackageUnitPrice()));
            $productD3E->setDateUpdate(new \DateTime);

            $em = $this->getDoctrine()->getManager();

            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productD3E_packaged_view', array(
                'id' => $productD3E->getId()
            ));
        }
        return $this->render('@PaprecCatalog/ProductD3E/package/editPackage.html.twig', array(
            'form' => $form->createView(),
            'productD3E' => $productD3E
        ));
    }

    /**
     * @Route("/productD3E/remove/{id}", name="paprec_catalog_productD3E_remove")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function removeAction(Request $request, ProductD3E $productD3E)
    {
        $em = $this->getDoctrine()->getManager();

        /*
         * Suppression des images
         */
        foreach ($productD3E->getPictures() as $picture) {
            $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
            $productD3E->removePicture($picture);
        }

        $productD3E->setDeleted(new \DateTime);
        $productD3E->setIsDisplayed(false);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productD3E_index');
    }

    /**
     * @Route("/productD3E/removeMany/{ids}", name="paprec_catalog_productD3E_removeMany")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function removeManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            $productD3Es = $em->getRepository('PaprecCatalogBundle:ProductD3E')->findById($ids);
            foreach ($productD3Es as $productD3E) {
                foreach ($productD3E->getPictures() as $picture) {
                    $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
                    $productD3E->removePicture($picture);
                }

                $productD3E->setDeleted(new \DateTime);
                $productD3E->setIsDisplayed(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_productD3E_index');
    }

    /**
     * Supprimme un fichier du sytème de fichiers
     *
     * @param $path
     */
    public function removeFile($path)
    {
        $fs = new Filesystem();
        try {
            $fs->remove($path);
        } catch (IOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @Route("/productD3E/addPicture/{id}/{type}", name="paprec_catalog_productD3E_addPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @param Request $request
     * @param ProductD3E $productD3E
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function addPictureAction(Request $request, ProductD3E $productD3E)
    {
        $isPackage = $productD3E->getIsPackage();
        $picture = new Picture();
        foreach ($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));

        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $productD3E->setDateUpdate(new \DateTime());
            $picture = $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.d3e.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $picture->setType($request->get('type'));
                $picture->setProductD3E($productD3E);
                $productD3E->addPicture($picture);
                $em->flush();
            }
            if ($isPackage) {
                return $this->redirectToRoute('paprec_catalog_productD3E_packaged_view', array(
                    'id' => $productD3E->getId()
                ));
            } else {
                return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                    'id' => $productD3E->getId()
                ));
            }
        }
        if ($isPackage) {
            return $this->render('PaprecCatalogBundle:ProductD3E:viewPackage.html.twig', array(
                'productD3E' => $productD3E,
                'formAddPicture' => $form->createView()
            ));
        } else {
            return $this->render('PaprecCatalogBundle:ProductD3E:view.html.twig', array(
                'productD3E' => $productD3E,
                'formAddPicture' => $form->createView()
            ));
        }
    }

    /**
     * @Route("/productD3E/editPicture/{id}/{pictureID}", name="paprec_catalog_productD3E_editPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function editPictureAction(Request $request, ProductD3E $productD3E)
    {
        $isPackage = $productD3E->getIsPackage();

        $em = $this->getDoctrine()->getManager();
        $pictureID = $request->get('pictureID');
        $picture = $em->getRepository('PaprecCatalogBundle:Picture')->find($pictureID);
        $oldPath = $picture->getPath();

        $em = $this->getDoctrine()->getManager();

        foreach ($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));


        $form->handleRequest($request);
        if ($form->isValid()) {
            $productD3E->setDateUpdate(new \DateTime());
            $picture = $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.d3e.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $this->removeFile($this->getParameter('paprec_catalog.product.d3e.picto_path') . '/' . $oldPath);
                $em->flush();
            }

            if($isPackage) {
                return $this->redirectToRoute('paprec_catalog_productD3E_packaged_view', array(
                    'id' => $productD3E->getId()
                ));
            } else {
                return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                    'id' => $productD3E->getId()
                ));
            }

        }
        if ($isPackage) {
            return $this->render('PaprecCatalogBundle:ProductD3E:viewPackage.html.twig', array(
                'productD3E' => $productD3E,
                'formEditPicture' => $form->createView()
            ));
        } else {
            return $this->render('PaprecCatalogBundle:ProductD3E:view.html.twig', array(
                'productD3E' => $productD3E,
                'formEditPicture' => $form->createView()
            ));
        }
    }

    /**
     * @Route("/productD3E/removePicture/{id}/{pictureID}", name="paprec_catalog_productD3E_removePicture")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws Exception
     * @throws \Exception
     */
    public function removePictureAction(Request $request, ProductD3E $productD3E)
    {
        $isPackage = $productD3E->getIsPackage();

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');

        $pictures = $productD3E->getPictures();
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productD3E->setDateUpdate(new \DateTime());
                $this->removeFile($this->getParameter('paprec_catalog.product.d3e.picto_path') . '/' . $picture->getPath());
                $em->remove($picture);
                continue;
            }
        }
        $em->flush();

        if ($isPackage) {
            return $this->redirectToRoute('paprec_catalog_productD3E_packaged_view', array(
                'id' => $productD3E->getId()
            ));
        } else {
            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));
        }
    }

    /**
     * @Route("/productD3E/setPilotPicture/{id}/{pictureID}", name="paprec_catalog_productD3E_setPilotPicture")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'CHANTIER' in user.getDivisions())")
     */
    public function setPilotPictureAction(Request $request, ProductD3E $productD3E)
    {
        $isPackage = $productD3E->getIsPackage();

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $productD3E->getPictures();
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productD3E->setDateUpdate(new \DateTime());
                $picture->setType('PILOTPICTURE');
                continue;
            }
        }
        $em->flush();

        if ($isPackage) {
            return $this->redirectToRoute('paprec_catalog_productD3E_packaged_view', array(
                'id' => $productD3E->getId()
            ));
        } else {
            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));
        }
    }

    /**
     * @Route("/productD3E/setPicture/{id}/{pictureID}", name="paprec_catalog_productD3E_setPicture")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'CHANTIER' in user.getDivisions())")
     */
    public function setPictureAction(Request $request, ProductD3E $productD3E)
    {
        $isPackage = $productD3E->getIsPackage();

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $productD3E->getPictures();
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productD3E->setDateUpdate(new \DateTime());
                $picture->setType('PICTURE');
                continue;
            }
        }
        $em->flush();

        if ($isPackage) {
            return $this->redirectToRoute('paprec_catalog_productD3E_packaged_view', array(
                'id' => $productD3E->getId()
            ));
        } else {
            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));
        }
    }


    /**
     * @Route("/productD3E/{id}/addType", name="paprec_catalog_productD3E_addType")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'CHANTIER' in user.getDivisions())")
     * @throws Exception
     */
    public function addTypeAction(Request $request, ProductD3E $productD3E)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $em = $this->getDoctrine()->getManager();
        $productD3ETypeRepo = $em->getRepository('PaprecCatalogBundle:ProductD3EType');


        if ($productD3E->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $productD3EType = new ProductD3EType();

        $form = $this->createForm(ProductD3ETypeAddType::class, $productD3EType,
            array(
                'productId' => $productD3E->getId(),
                'productD3ETypeRepo' => $productD3ETypeRepo
            ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productD3EType = $form->getData();
            $productD3EType->setUnitPrice($numberManager->normalize($productD3EType->getUnitPrice()));
            $productD3EType->setProductD3E($productD3E);
            $productD3E->addProductD3EType($productD3EType);
            $productD3E->setDateUpdate(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:ProductD3EType:add.html.twig', array(
            'form' => $form->createView(),
            'productD3E' => $productD3E,
        ));
    }

    /**
     * @Route("/productD3E/{id}/editType/{productD3ETypeId}", name="paprec_catalog_productD3E_editType")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'CHANTIER' in user.getDivisions())")
     * @ParamConverter("productD3E", options={"id" = "id"})
     * @ParamConverter("productD3EType", options={"id" = "productD3ETypeId"})
     * @param Request $request
     * @param ProductD3E $productD3E
     * @param ProductD3EType $productD3EType
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editTypeAction(Request $request, ProductD3E $productD3E, ProductD3EType $productD3EType)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $em = $this->getDoctrine()->getManager();

        if ($productD3E->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productD3EType->getProductD3E() !== $productD3E) {
            throw new NotFoundHttpException();
        }

        $productD3EType->setUnitPrice($numberManager->denormalize($productD3EType->getUnitPrice()));

        $form = $this->createForm(ProductD3ETypeEditType::class, $productD3EType);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productD3EType = $form->getData();
            $productD3EType->setUnitPrice($numberManager->normalize($productD3EType->getUnitPrice()));

            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));
        }

        return $this->render('PaprecCatalogBundle:ProductD3EType:edit.html.twig', array(
            'form' => $form->createView(),
            'productD3E' => $productD3E,
            'productD3EType' => $productD3EType
        ));
    }

    /**
     * @Route("/productD3E/{id}/removeType/{productD3ETypeId}", name="paprec_catalog_productD3E_removeType")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'CHANTIER' in user.getDivisions())")
     * @ParamConverter("productD3E", options={"id" = "id"})
     * @ParamConverter("productD3EType", options={"id" = "productD3ETypeId"})
     */
    public function removeLineAction(Request $request, ProductD3E $productD3E, ProductD3EType $productD3EType)
    {
        if ($productD3E->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productD3EType->getProductD3E() !== $productD3E) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $em->remove($productD3EType);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
            'id' => $productD3E->getId()
        ));
    }
}
