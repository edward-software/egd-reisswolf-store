<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\Category;
use Paprec\CatalogBundle\Entity\ProductChantierCategory;
use Paprec\CatalogBundle\Entity\ProductDICategory;
use Paprec\CatalogBundle\Form\CategoryType;
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


class CategoryController extends Controller
{
    /**
     * @Route("/category/", name="paprec_catalog_category_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:Category:index.html.twig');
    }

    /**
     * LoadList modifiée pour passer en parametre le type de Catégorie que l'on veut (DI ou CHANTIER)
     * @Route("/category/loadList/{typeCategory}", name="paprec_catalog_category_loadList")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
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
        // Récupération du type de catégorie souhaité (DI ou CHANTIER)
        $typeCategory = $request->get('typeCategory');

        $cols['id'] = array('label' => 'id', 'id' => 'c.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'c.name', 'method' => array('getName'));
        $cols['description'] = array('label' => 'description', 'id' => 'c.description', 'method' => array('getDescription'));
        $cols['position'] = array('label' => 'position', 'id' => 'c.position', 'method' => array('getPosition'));
        $cols['enabled'] = array('label' => 'enabled', 'id' => 'c.enabled', 'method' => array('getEnabled'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'c.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('c'))
            ->from('PaprecCatalogBundle:Category', 'c')
            ->where('c.deleted IS NULL')
            ->andWhere('c.division LIKE \'%' . $typeCategory . '%\''); // Récupération des catégories du type voulu

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('c.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('c.name', '?1'),
                    $queryBuilder->expr()->like('c.description', '?1'),
                    $queryBuilder->expr()->like('c.position', '?1'),
                    $queryBuilder->expr()->like('c.enabled', '?1'),
                    $queryBuilder->expr()->like('c.dateCreation', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);
        // Reformatage de certaines données
        $tmp = array();
        foreach ($datatable['data'] as $data) {
            $line = $data;
            $line['enabled'] = ($line['enabled'] === true) ? 'Oui' : 'Non';
            $tmp[] = $line;
        }
        $datatable['data'] = $tmp;

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);
    }


    /**
     * @Route("/category/export", name="paprec_catalog_category_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function exportAction(Request $request)
    {

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('c'))
            ->from('PaprecCatalogBundle:Category', 'c')
            ->where('c.deleted IS NULL');

        $categories = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Catégories")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nom')
            ->setCellValue('C1', 'Description')
            ->setCellValue('D1', 'Division')
            ->setCellValue('E1', 'Position')
            ->setCellValue('F1', 'Activé')
            ->setCellValue('G1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Catégories');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($categories as $category) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $category->getId())
                ->setCellValue('B' . $i, $category->getName())
                ->setCellValue('C' . $i, $category->getDescription())
                ->setCellValue('D' . $i, $category->getDivision())
                ->setCellValue('E' . $i, $category->getPosition())
                ->setCellValue('F' . $i, $category->getEnabled())
                ->setCellValue('G' . $i, $category->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Categories-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/category/view/{id}", name="paprec_catalog_category_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function viewAction(Request $request, Category $category)
    {
        $categoryManager = $this->get('paprec_catalog.category_manager');
        $categoryManager->isDeleted($category, true);

        return $this->render('PaprecCatalogBundle:Category:view.html.twig', array(
            'category' => $category
        ));
    }

    /**
     * @Route("/category/add", name="paprec_catalog_category_add")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function addAction(Request $request)
    {

        $category = new Category();

        $divisions = array();
        foreach ($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(CategoryType::class, $category, array(
            'division' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $category->setDateCreation(new \DateTime);

            if ($category->getPicto() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto" de la catégorie
                 */
                $picto = $category->getPicto();
                $pictoFileName = md5(uniqid()) . '.' . $picto->guessExtension();

                $picto->move($this->getParameter('paprec_catalog.category.picto_path'), $pictoFileName);

                $category->setPicto($pictoFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_category_view', array(
                'id' => $category->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Category:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/category/enableMany/{ids}", name="paprec_catalog_category_enableMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
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
            $categories = $em->getRepository('PaprecCatalogBundle:Category')->findById($ids);
            foreach ($categories as $category) {
                $category->setEnabled(true);
                $category->setDateUpdate(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_category_index');

    }


    /**
     * @Route("/category/edit/{id}", name="paprec_catalog_category_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function editAction(Request $request, Category $category)
    {
        $categoryManager = $this->get('paprec_catalog.category_manager');
        $categoryManager->isDeleted($category, true);

        $divisions = array();
        foreach ($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(CategoryType::class, $category, array(
            'division' => $divisions
        ));

        $currentPicto = $category->getPicto();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $category->setDateUpdate(new \DateTime);
            $newPicto = $category->getPicto();

            if ($newPicto instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads SI il y en a
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de la catégorie
                 */
                $pictoFileName = md5(uniqid()) . '.' . $newPicto->guessExtension();

                $newPicto->move($this->getParameter('paprec_catalog.category.picto_path'), $pictoFileName);

                $category->setPicto($pictoFileName);
            } else {
                $category->setPicto($currentPicto);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_category_view', array(
                'id' => $category->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Category:edit.html.twig', array(
            'form' => $form->createView(),
            'category' => $category
        ));
    }

    /**
     * @Route("/category/remove/{id}", name="paprec_catalog_category_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function removeAction(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $this->removeFile($this->getParameter('paprec_catalog.category.picto_path') . '/' . $category->getPicto());
        $category->setPicto();

        $category->setDeleted(new \DateTime);
        $category->setEnabled(false);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_category_index');
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
     * @Route("/category/removeMany/{ids}", name="paprec_catalog_category_removeMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
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
            $categories = $em->getRepository('PaprecCatalogBundle:Category')->findById($ids);
            foreach ($categories as $category) {
                $this->removeFile($this->getParameter('paprec_catalog.category.picto_path') . '/' . $category->getPicto());
                $category->setPicto();
                $category->setDeleted(new \DateTime);
                $category->setEnabled(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_category_index');
    }

    /**
     * On met à jour les positions des ProductDICategories en fonction de l'ordre du JQuery Sortable
     * @Route("/category/{id}/moveProductDICategory", name="paprec_catalog_category_moveProductDICategory")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function moveProductDICategoryAction(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $repoProductDICategory = $em->getRepository(ProductDICategory::class);

        $productDICategoryItemIds = $request->get('productDICategoryItemIds');
        try {
            if (is_array($productDICategoryItemIds) && count($productDICategoryItemIds)) {
                foreach ($productDICategoryItemIds as $position => $itemId) {
                    $productDICategory = $repoProductDICategory->find($itemId);
                    $productDICategory->setPosition($position);
                }
                $em->flush();
            }
        } catch (Exception $e) {
            return new JsonResponse(array(
                'status' => 500,
                'resultMessage' => $e->getMessage()
            ));
        }
        return new JsonResponse(array(
            'status' => 200
        ));
    }

    /**
     * On met à jour les positions des ProductChantierCategories en fonction de l'ordre du JQuery Sortable
     * @Route("/category/{id}/moveProductChantierCategory", name="paprec_catalog_category_moveProductChantierCategory")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function moveProductChantierCategoryAction(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $repoProductChantierCategory = $em->getRepository(ProductChantierCategory::class);

        $productChantierCategoryItemIds = $request->get('productChantierCategoryItemIds');
        try {
            if (is_array($productChantierCategoryItemIds) && count($productChantierCategoryItemIds)) {
                foreach ($productChantierCategoryItemIds as $position => $itemId) {
                    $productChantierCategory = $repoProductChantierCategory->find($itemId);
                    $productChantierCategory->setPosition($position);
                }
                $em->flush();
            }
        } catch (Exception $e) {
            return new JsonResponse(array(
                'status' => 500,
                'resultMessage' => $e->getMessage()
            ));
        }
        return new JsonResponse(array(
            'status' => 200
        ));
    }

    /**
     * @Route("/category/{id}/removeProductChantierCategory/{productChantierCategoryId}", name="paprec_catalog_category_removeProductChantierCategory")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function removeProductChantierCategory(Request $request, Category $category, ProductChantierCategory $productChantierCategoryId)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($productChantierCategoryId);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_category_view', array(
            'id' => $category->getId()
        ));
    }

    /**
     * @Route("/category/{id}/removeProductDICategory/{productDICategoryId}", name="paprec_catalog_category_removeProductDICategory")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function removeProductDICategory(Request $request, Category $category, ProductDICategory $productDICategoryId)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($productDICategoryId);
        $em->flush();


        return $this->redirectToRoute('paprec_catalog_category_view', array(
            'id' => $category->getId()
        ));
    }

    /**
     * Fonction appelée lorsque l'on réordonne les catégories dans 'index'
     * On reçoit en param un tableau [position => categoryID]
     * @Route("/category/orderCategories", name="paprec_catalog_category_orderCategories")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function orderCategories(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCategory = $em->getRepository(Category::class);

        //On récupère les catégories dont la position a changé
        $changedCategories = $request->get('IdPositionCategories');
        try {
            if (is_array($changedCategories) && count($changedCategories)) {

                foreach ($changedCategories as $position => $categoryId) {
                    $category = $repoCategory->find($categoryId);
                    $category->setPosition($position + 1);
                }
                $em->flush();
            }
        } catch (Exception $e) {
            return new JsonResponse(array(
                'status' => 500,
                'resultMessage' => $e->getMessage()
            ));
        }

        return new JsonResponse(array(
            'status' => 202
        ));
    }
}
