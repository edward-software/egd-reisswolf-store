<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\PriceListD3E;
use Paprec\CatalogBundle\Entity\PriceListLineD3E;
use Paprec\CatalogBundle\Form\PriceListD3EType;
use Paprec\CatalogBundle\Form\PriceListLineD3EType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PriceListD3EController extends Controller
{

    /**
     * @Route("/priceListD3E",  name="paprec_catalog_priceListD3E_index")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:PriceListD3E:index.html.twig');
    }


    /**
     * @Route("/priceListD3E/loadList",  name="paprec_catalog_priceListD3E_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'g.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'g.name', 'method' => array('getName'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'g.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('g'))
            ->from('PaprecCatalogBundle:PriceListD3E', 'g')
            ->where('g.deleted is null');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('g.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('g.name', '?1'),
                    $queryBuilder->expr()->like('g.dateCreation', '?1')
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
     * @Route("/priceListD3E/export",  name="paprec_catalog_priceListD3E_export")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function exportAction()
    {
        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('g'))
            ->from('PaprecCatalogBundle:PriceListD3E', 'g')
            ->where('g.deleted IS NULL');

        $priceListD3Es = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Grilles tarifaires D3E")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Texte')
            ->setCellValue('C1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Grilles tarifaires D3E');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($priceListD3Es as $priceListD3E) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $priceListD3E->getId())
                ->setCellValue('B' . $i, $priceListD3E->getName())
                ->setCellValue('C' . $i, $priceListD3E->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-PriceListD3Es-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/priceListD3E/view/{id}",  name="paprec_catalog_priceListD3E_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function viewAction(Request $request, PriceListD3E $priceListD3E)
    {

        $priceListD3EManager = $this->get('paprec_catalog.price_list_d3e_manager');
        $priceListD3EManager->isDeleted($priceListD3E, true);


        return $this->render('PaprecCatalogBundle:PriceListD3E:view.html.twig', array(
            'priceListD3E' => $priceListD3E
        ));
    }

    /**
     * @Route("/priceListD3E/add",  name="paprec_catalog_priceListD3E_add")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Exception
     */
    public function addAction(Request $request)
    {
        $priceListD3E = new PriceListD3E();

        $form = $this->createForm(PriceListD3EType::class, $priceListD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $priceListD3E = $form->getData();
            $priceListD3E->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($priceListD3E);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_priceListD3E_view', array(
                'id' => $priceListD3E->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:PriceListD3E:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/priceListD3E/edit/{id}",  name="paprec_catalog_priceListD3E_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, PriceListD3E $priceListD3E)
    {
        $priceListD3EManager = $this->get('paprec_catalog.price_list_d3e_manager');
        $priceListD3EManager->isDeleted($priceListD3E, true);

        $form = $this->createForm(PriceListD3EType::class, $priceListD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $priceListD3E = $form->getData();
            $priceListD3E->setDateUpdate(new \DateTime);


            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_priceListD3E_view', array(
                'id' => $priceListD3E->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:PriceListD3E:edit.html.twig', array(
            'form' => $form->createView(),
            'priceListD3E' => $priceListD3E
        ));
    }

    /**
     * @Route("/priceListD3E/remove/{id}", name="paprec_catalog_priceListD3E_remove")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Exception
     */
    public function removeAction(Request $request, PriceListD3E $priceListD3E)
    {
        $priceListD3EManager = $this->get('paprec_catalog.price_list_d3e_manager');

        $em = $this->getDoctrine()->getManager();
        if (!$priceListD3EManager->hasRelatedProductD3E($priceListD3E->getId())) {
            $priceListD3E->setDeleted(new \DateTime());
            $em->flush();
        } else {
            $this->get('session')->getFlashBag()->add('error', 'priceListHasRelatedProduct');
            return $this->redirectToRoute('paprec_catalog_priceListD3E_view', array(
                'id' => $priceListD3E->getId()
            ));
        }

        return $this->redirectToRoute('paprec_catalog_priceListD3E_index');
    }

    /**
     * @Route("/priceListD3E/removeMany/{ids}", name="paprec_catalog_priceListD3E_removeMany")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Exception
     */
    public function removeManyAction(Request $request)
    {
        $priceListD3EManager = $this->get('paprec_catalog.price_list_d3e_manager');

        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            $priceListD3Es = $em->getRepository('PaprecCatalogBundle:PriceListD3E')->findById($ids);
            $listUndeletable= '';
            foreach ($priceListD3Es as $priceListD3E) {
                if (!$priceListD3EManager->hasRelatedProductD3E($priceListD3E->getId())) {
                    $priceListD3E->setDeleted(new \DateTime);
                } else {
                    $listUndeletable  .=  "\"". $priceListD3E->getName() . "\" ";
                }
            }
            if ($listUndeletable !== null && $listUndeletable !== '') {
                $this->get('session')->getFlashBag()->add('error', array('var' =>'priceListsHaveRelatedProduct', 'msg' => $listUndeletable));
                return $this->redirectToRoute('paprec_catalog_priceListD3E_index');
            } else {
                $em->flush();
            }
        }
        return $this->redirectToRoute('paprec_catalog_priceListD3E_index');
    }

    /**
     * @Route("/priceListD3E/{id}/addLine", name="paprec_catalog_priceListD3E_addLine")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function addLineAction(Request $request, PriceListD3E $priceListD3E)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $priceListLineD3E = new PriceListLineD3E();
        $form = $this->createForm(PriceListLineD3EType::class, $priceListLineD3E);

        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $priceListLineD3E = $form->getData();
            $priceListLineD3E->setPriceListD3E($priceListD3E);
            $priceListD3E->addPriceListLineD3E($priceListLineD3E);

            $priceListLineD3E->setPrice($numberManager->normalize($priceListLineD3E->getPrice()));
            if ($priceListLineD3E->getMaxQuantity() == null) {
                $priceListLineD3E->setMaxQuantity($priceListLineD3E->getMinQuantity());
            }
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_priceListD3E_view', array(
                'id' => $priceListD3E->getId()
            ));
        }

        return $this->render('PaprecCatalogBundle:PriceListLineD3E:add.html.twig', array(
            'priceListD3E' => $priceListD3E,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/priceListD3E/{id}/editLine/{lineId}", name="paprec_catalog_priceListD3E_editLine")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function editLineAction(Request $request, PriceListD3E $priceListD3E)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $em = $this->getDoctrine()->getManager();
        $lineId = $request->get('lineId');
        $priceListLineD3E = $em->getRepository(PriceListLineD3E::class)->find($lineId);
        $priceListLineD3E->setPrice($numberManager->denormalize($priceListLineD3E->getPrice()));

        $form = $this->createForm(PriceListLineD3EType::class, $priceListLineD3E);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $priceListLineD3E = $form->getData();

            $priceListLineD3E->setPrice($numberManager->normalize($priceListLineD3E->getPrice()));
            if ($priceListLineD3E->getMaxQuantity() == null) {
                $priceListLineD3E->setMaxQuantity($priceListLineD3E->getMinQuantity());
            }
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_priceListD3E_view', array(
                'id' => $priceListD3E->getId()
            ));
        }

        return $this->render('PaprecCatalogBundle:PriceListLineD3E:edit.html.twig', array(
            'priceListD3E' => $priceListD3E,
            'priceListLineD3E' => $priceListLineD3E,
            'form' => $form->createView()
        ));
    }


    /**
     * @Route("/priceListD3E/removeLine/{id}/{lineId}", name="paprec_catalog_priceListD3E_removeLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function removeLineAction(Request $request, PriceListD3E $priceListD3E)
    {

        $em = $this->getDoctrine()->getManager();

        $lineId = $request->get('lineId');

        $priceListLineD3Es = $priceListD3E->getPriceListLineD3Es();
        foreach ($priceListLineD3Es as $priceListLineD3E) {
            if ($priceListLineD3E->getId() == $lineId) {
                $priceListD3E->setDateUpdate(new \DateTime());
                $em->remove($priceListLineD3E);
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_priceListD3E_view', array(
            'id' => $priceListD3E->getId()
        ));
    }
}
