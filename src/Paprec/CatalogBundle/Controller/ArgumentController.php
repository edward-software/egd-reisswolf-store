<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\Argument;
use Paprec\CatalogBundle\Form\ArgumentType;
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

class ArgumentController extends Controller
{
    /**
     * @Route("/argument",  name="paprec_catalog_argument_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:Argument:index.html.twig');
    }

    /**
     * @Route("/argument/loadList",  name="paprec_catalog_argument_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'a.id', 'method' => array('getId'));
        $cols['description'] = array('label' => 'description', 'id' => 'a.description', 'method' => array('getDescription'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'a.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('a'))
            ->from('PaprecCatalogBundle:Argument', 'a')
            ->where('a.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('a.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('a.description', '?1'),
                    $queryBuilder->expr()->like('a.dateCreation', '?1')
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
     * @Route("/argument/export",  name="paprec_catalog_argument_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function exportAction()
    {
        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('a'))
            ->from('PaprecCatalogBundle:Argument', 'a')
            ->where('a.deleted IS NULL');

        $arguments = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Arguments")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Texte')
            ->setCellValue('C1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Arguments');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($arguments as $argument) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $argument->getId())
                ->setCellValue('B' . $i, $argument->getDescription())
                ->setCellValue('C' . $i, $argument->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Arguments-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/argument/view/{id}",  name="paprec_catalog_argument_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, Argument $argument)
    {
        $argumentManager = $this->get('paprec_catalog.argument_manager');
        $argumentManager->isDeleted($argument, true);

        return $this->render('PaprecCatalogBundle:Argument:view.html.twig', array(
            'argument' => $argument
        ));
    }

    /**
     * @Route("/argument/add",  name="paprec_catalog_argument_add")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function addAction(Request $request)
    {
        $argument = new Argument();

        $form = $this->createForm(ArgumentType::class, $argument);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $argument = $form->getData();
            $argument->setDateCreation(new \DateTime);

            if ($argument->getPicto() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto" de l'argument
                 */
                $picto = $argument->getPicto();
                $pictoFileName = md5(uniqid()) . '.' . $picto->guessExtension();

                $picto->move($this->getParameter('paprec_catalog.argument.picto_path'), $pictoFileName);

                $argument->setPicto($pictoFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($argument);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_argument_view', array(
                'id' => $argument->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Argument:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/argument/edit/{id}",  name="paprec_catalog_argument_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, Argument $argument)
    {
        $argumentManager = $this->get('paprec_catalog.argument_manager');
        $argumentManager->isDeleted($argument, true);

        $form = $this->createForm(ArgumentType::class, $argument);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $argument = $form->getData();
            $argument->setDateUpdate(new \DateTime);

            if ($argument->getPicto() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de l'argument
                 */
                $picto = $argument->getPicto();
                $pictoFileName = md5(uniqid()) . '.' . $picto->guessExtension();

                $picto->move($this->getParameter('paprec_catalog.argument.picto_path'), $pictoFileName);

                $argument->setPicto($pictoFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_argument_view', array(
                'id' => $argument->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Argument:edit.html.twig', array(
            'form' => $form->createView(),
            'argument' => $argument
        ));
    }

    /**
     * @Route("/argument/remove/{id}", name="paprec_catalog_argument_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function removeAction(Request $request, Argument $argument)
    {
        $em = $this->getDoctrine()->getManager();

        $this->removeFile($this->getParameter('paprec_catalog.category.picto_path') . '/' . $argument->getPicto());
        $argument->setPicto();
        $argument->setDeleted(new \DateTime());
        foreach ($argument->getProductChantiers() as $productChantier) {
            $argument->removeProductChantier($productChantier);
        }
        foreach ($argument->getProductDIs() as $productDI) {
            $argument->removeProductChantier($productDI);
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_argument_index');
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
     * @Route("/argument/removeMany/{ids}", name="paprec_catalog_argument_removeMany")
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
            $arguments = $em->getRepository('PaprecCatalogBundle:Argument')->findById($ids);
            foreach ($arguments as $argument) {
                $this->removeFile($this->getParameter('paprec_catalog.category.picto_path') . '/' . $argument->getPicto());
                $argument->setPicto();
                $argument->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_argument_index');
    }
}
