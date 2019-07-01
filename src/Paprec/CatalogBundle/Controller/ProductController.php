<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\Picture;
use Paprec\CatalogBundle\Entity\Product;
use Paprec\CatalogBundle\Entity\ProductCategory;
use Paprec\CatalogBundle\Entity\ProductLabel;
use Paprec\CatalogBundle\Form\PictureProductType;
use Paprec\CatalogBundle\Form\ProductCategoryAddType;
use Paprec\CatalogBundle\Form\ProductLabelType;
use Paprec\CatalogBundle\Form\ProductPackageType;
use Paprec\CatalogBundle\Form\ProductType;
use Paprec\CommercialBundle\Form\ProductCategoryEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends Controller
{

    /**
     * @Route("/product", name="paprec_catalog_product_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:Product:index.html.twig');
    }

    /**
     * @Route("/product/loadList", name="paprec_catalog_product_loadList")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function loadListAction(Request $request)
    {
        $productManager = $this->get('paprec_catalog.product_manager');

        $return = array();
        $locale = $request->getLocale();

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = array('label' => 'id', 'id' => 'p.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'pL.name', 'method' => array(array('getProductLabels', 0), 'getName'));
        $cols['dimensions'] = array(
            'label' => 'dimensions',
            'id' => 'p.dimensions',
            'method' => array('getDimensions')
        );


        $queryBuilder = $this->getDoctrine()->getManager()->getRepository(Product::class)->createQueryBuilder('p');

        $queryBuilder->select(array('p', 'pL'))
            ->leftJoin('p.productLabels', 'pL')
            ->where('p.deleted IS NULL')
            ->andWhere('pL.language = :language')
            ->setParameter('language', 'EN');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('p.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('pL.name', '?1'),
                    $queryBuilder->expr()->like('p.dimensions', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start,
            $orders, $columns, $filters);

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);

    }

    /**
     * @Route("/product/export",  name="paprec_catalog_product_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction()
    {

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:Product', 'p')
            ->where('p.deleted IS NULL');

        $Products = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Reisswolf Shop")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Reisswolf Shop - Produits DI")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nom')
            ->setCellValue('C1', 'Description')
            ->setCellValue('D1', 'Volume')
            ->setCellValue('E1', 'Unité Vol')
            ->setCellValue('F1', 'Dimensions')
            ->setCellValue('G1', 'Lien description')
            ->setCellValue('H1', 'Statut affichage')
            ->setCellValue('I1', 'Dispo géographique')
            ->setCellValue('J1', 'Date création');


        $phpExcelObject->getActiveSheet()->setTitle('Produits DI');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($Products as $product) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $product->getId())
                ->setCellValue('B' . $i, $product->getName())
                ->setCellValue('C' . $i, $product->getDescription())
                ->setCellValue('D' . $i, $product->getCapacity())
                ->setCellValue('E' . $i, $product->getCapacityUnit())
                ->setCellValue('F' . $i, $product->getDimensions())
                ->setCellValue('G' . $i, $product->getReference())
                ->setCellValue('H' . $i, $product->getIsDisplayed())
                ->setCellValue('I' . $i, $product->getAvailablePostalCodes())
                ->setCellValue('J' . $i, $product->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'ReisswolfShop-Extraction-Produits-DI-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/product/view/{id}",  name="paprec_catalog_product_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, Product $product)
    {
        $productManager = $this->get('paprec_catalog.product_manager');
        $productManager->isDeleted($product, true);

        $language = $request->getLocale();
        $productLabel = $productManager->getProductLabelByProductAndLocale($product, strtoupper($language));

        $otherProductLabels = $productManager->getProductLabels($product);

        $tmp = array();
        foreach ($otherProductLabels as $pL) {
            if ($pL->getId() != $productLabel->getId()) {
                $tmp[] = $pL;
            }
        }
        $otherProductLabels = $tmp;


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


        return $this->render('PaprecCatalogBundle:Product:view.html.twig', array(
            'product' => $product,
            'productLabel' => $productLabel,
            'formAddPicture' => $formAddPicture->createView(),
            'formEditPicture' => $formEditPicture->createView(),
            'otherProductLabels' => $otherProductLabels
        ));
    }

    /**
     * @Route("/product/add",  name="paprec_catalog_product_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();

        $languages = array();
        foreach ($this->getParameter('paprec_product_languages') as $language) {
            $languages[$language] = $language;
        }

        $product = new Product();
        $productLabel = new ProductLabel();

        $form1 = $this->createForm(ProductType::class, $product);
        $form2 = $this->createForm(ProductLabelType::class, $productLabel, array(
            'languages' => $languages,
            'language' => 'EN'
        ));

        $form1->handleRequest($request);
        $form2->handleRequest($request);

        if ($form1->isSubmitted() && $form1->isValid() && $form2->isSubmitted() && $form2->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $product = $form1->getData();
            $product->setDateCreation(new \DateTime);
            $product->setUserCreation($user);

            $em->persist($product);
            $em->flush();

            $productLabel = $form2->getData();
            $productLabel->setDateCreation(new \DateTime);
            $productLabel->setUserCreation($user);
            $productLabel->setProduct($product);

            $em->persist($productLabel);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_product_view', array(
                'id' => $product->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Product:add.html.twig', array(
            'form1' => $form1->createView(),
            'form2' => $form2->createView()
        ));
    }

    /**
     * @Route("/product/edit/{id}",  name="paprec_catalog_product_edit")
     * @Security("has_role('ROLE_ADMIN')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, Product $product)
    {
        $productManager = $this->get('paprec_catalog.product_manager');
        $productManager->isDeleted($product, true);

        $user = $this->getUser();

        $languages = array();
        foreach ($this->getParameter('paprec_product_languages') as $language) {
            $languages[$language] = $language;
        }

        $language = $request->getLocale();
        $productLabel = $productManager->getProductLabelByProductAndLocale($product, strtoupper($language));


        $form1 = $this->createForm(ProductType::class, $product);
        $form2 = $this->createForm(ProductLabelType::class, $productLabel, array(
            'languages' => $languages,
            'language' => $productLabel->getLanguage()
        ));

        $form1->handleRequest($request);
        $form2->handleRequest($request);

        if ($form1->isSubmitted() && $form1->isValid() && $form2->isSubmitted() && $form2->isValid()) {


            $em = $this->getDoctrine()->getManager();

            $product = $form1->getData();
            $product->setDateUpdate(new \DateTime);
            $product->setUserUpdate($user);
            $em->flush();

            $productLabel = $form2->getData();
            $productLabel->setDateUpdate(new \DateTime);
            $productLabel->setUserUpdate($user);
            $productLabel->setProduct($product);

            $em->flush();

            return $this->redirectToRoute('paprec_catalog_product_view', array(
                'id' => $product->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:Product:edit.html.twig', array(
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
            'product' => $product,
            'productLabel' => $productLabel
        ));
    }

    /**
     * @Route("/product/remove/{id}", name="paprec_catalog_product_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        /*
         * Suppression des images
         */
        foreach ($product->getPictures() as $picture) {
            $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
            $product->removePicture($picture);
        }

        $product->setDeleted(new \DateTime);
        $product->setIsDisplayed(false);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_index');
    }

    /**
     * @Route("/product/removeMany/{ids}", name="paprec_catalog_product_removeMany")
     * @Security("has_role('ROLE_ADMIN')")
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
            $Products = $em->getRepository('PaprecCatalogBundle:Product')->findById($ids);
            foreach ($Products as $product) {
                foreach ($product->getPictures() as $picture) {
                    $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
                    $product->removePicture($picture);
                }

                $product->setDeleted(new \DateTime());
                $product->setIsEnabled(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_product_index');
    }

    /**
     * @Route("/product/enableMany/{ids}", name="paprec_catalog_product_enableMany")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function enableManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            $Products = $em->getRepository('PaprecCatalogBundle:Product')->findById($ids);
            foreach ($Products as $product) {
                $product->setIsEnabled(true);
            }
            $em->flush();
        }
        return $this->redirectToRoute('paprec_catalog_product_index');
    }

    /**
     * @Route("/product/disableMany/{ids}", name="paprec_catalog_product_disableMany")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function disableManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            $Products = $em->getRepository('PaprecCatalogBundle:Product')->findById($ids);
            foreach ($Products as $product) {
                $product->setIsEnabled(false);
            }
            $em->flush();
        }
        return $this->redirectToRoute('paprec_catalog_product_index');
    }

    /**
     * @Route("/product/{id}/addProductLabel",  name="paprec_catalog_product_addProductLabel")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addProductLabelAction(Request $request, Product $product)
    {
        $user = $this->getUser();

        $productManager = $this->get('paprec_catalog.product_manager');
        $productManager->isDeleted($product, true);

        $languages = array();
        foreach ($this->getParameter('paprec_product_languages') as $language) {
            $languages[$language] = $language;
        }
        $productLabel = new ProductLabel();

        $form = $this->createForm(ProductLabelType::class, $productLabel, array(
            'languages' => $languages,
            'language' => strtoupper($request->getLocale())
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $productLabel = $form->getData();
            $productLabel->setDateCreation(new \DateTime);
            $productLabel->setUserCreation($user);
            $productLabel->setProduct($product);

            $em->persist($productLabel);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_product_view', array(
                'id' => $product->getId()
            ));

        }

        return $this->render('@PaprecCatalog/Product/ProductLabel/add.html.twig', array(
            'form' => $form->createView(),
            'product' => $product,
        ));
    }

    /**
     * @Route("/product/{id}/editProductLabel/{productLabelId}",  name="paprec_catalog_product_editProductLabel")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param Product $product
     * @param $productLabelId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function editProductLabelAction(Request $request, Product $product, $productLabelId)
    {
        $user = $this->getUser();

        $productManager = $this->get('paprec_catalog.product_manager');
        $productLabelManager = $this->get('paprec_catalog.product_label_manager');

        $productManager->isDeleted($product, true);

        $productLabel = $productLabelManager->get($productLabelId);

        $languages = array();
        foreach ($this->getParameter('paprec_product_languages') as $language) {
            $languages[$language] = $language;
        }

        $form = $this->createForm(ProductLabelType::class, $productLabel, array(
            'languages' => $languages,
            'language' => $productLabel->getLanguage()
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $productLabel = $form->getData();
            $productLabel->setDateUpdate(new \DateTime);
            $productLabel->setUserUpdate($user);

//            $em->merge($productLabel);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_product_view', array(
                'id' => $product->getId()
            ));

        }

        return $this->render('@PaprecCatalog/Product/ProductLabel/edit.html.twig', array(
            'form' => $form->createView(),
            'product' => $product
        ));
    }

    /**
     * @Route("/product/{id}/removeProductLabel/{productLabelId}",  name="paprec_catalog_product_removeProductLabel")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param Product $product
     * @param $productLabelId
     */
    public function removeProductLabelAction(Request $request, Product $product, $productLabelId)
    {
        $em = $this->getDoctrine()->getManager();
        $productManager = $this->get('paprec_catalog.product_manager');
        $productLabelManager = $this->get('paprec_catalog.product_label_manager');

        $productManager->isDeleted($product, true);

        $productLabel = $productLabelManager->get($productLabelId);
        $em->remove($productLabel);

        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_view', array(
            'id' => $product->getId()
        ));
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
     * @Route("/product/addPicture/{id}/{type}", name="paprec_catalog_product_addPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addPictureAction(Request $request, Product $product)
    {
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
            $product->setDateUpdate(new \DateTime());
            $picture = $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $picture->setType($request->get('type'));
                $picture->setProduct($product);
                $product->addPicture($picture);
                $em->persist($picture);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_product_view', array(
                'id' => $product->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:Product:view.html.twig', array(
            'product' => $product,
            'formAddPicture' => $form->createView()
        ));
    }

    /**
     * @Route("/product/editPicture/{id}/{pictureID}", name="paprec_catalog_product_editPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editPictureAction(Request $request, Product $product)
    {
        $productManager = $this->get('paprec_catalog.product_manager');
        $pictureManager = $this->get('paprec_catalog.picture_manager');

        $em = $this->getDoctrine()->getManager();
        $pictureID = $request->get('pictureID');
        $picture = $pictureManager->get($pictureID);
        $oldPath = $picture->getPath();

        $em = $this->getDoctrine()->getEntityManager();

        foreach ($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));


        $form->handleRequest($request);
        if ($form->isValid()) {
            $product->setDateUpdate(new \DateTime());
            $picture = $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $this->removeFile($this->getParameter('paprec_catalog.product.picto_path') . '/' . $oldPath);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_product_view', array(
                'id' => $product->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:Product:view.html.twig', array(
            'product' => $product,
            'formEditPicture' => $form->createView()
        ));
    }


    /**
     * @Route("/product/removePicture/{id}/{pictureID}", name="paprec_catalog_product_removePicture")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removePictureAction(Request $request, Product $product)
    {

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');

        $pictures = $product->getPictures();
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $product->setDateUpdate(new \DateTime());
                $this->removeFile($this->getParameter('paprec_catalog.product.picto_path') . '/' . $picture->getPath());
                $em->remove($picture);
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_view', array(
            'id' => $product->getId()
        ));
    }

    /**
     * @Route("/product/setPilotPicture/{id}/{pictureID}", name="paprec_catalog_product_setPilotPicture")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function setPilotPictureAction(Request $request, Product $product)
    {

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $product->getPictures();
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $product->setDateUpdate(new \DateTime());
                $picture->setType('PILOTPICTURE');
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_view', array(
            'id' => $product->getId()
        ));
    }

    /**
     * @Route("/product/setPicture/{id}/{pictureID}", name="paprec_catalog_product_setPicture")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function setPictureAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $product->getPictures();
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $product->setDateUpdate(new \DateTime());
                $picture->setType('PICTURE');
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_view', array(
            'id' => $product->getId()
        ));
    }

}
