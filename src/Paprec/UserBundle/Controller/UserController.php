<?php

namespace Paprec\UserBundle\Controller;

use Paprec\UserBundle\Form\UserMyProfileType;
use Paprec\UserBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Paprec\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{

    /**
     * @Route("/", name="paprec_user_user_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecUserBundle:User:index.html.twig');
    }

    /**
     * @Route("/loadList", name="paprec_user_user_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'u.id', 'method' => array('getId'));
        $cols['username'] = array('label' => 'username', 'id' => 'u.username', 'method' => array('getUsername'));
        $cols['firstName'] = array('label' => 'firstName', 'id' => 'u.firstName', 'method' => array('getFirstName'));
        $cols['lastName'] = array('label' => 'lastName', 'id' => 'u.lastName', 'method' => array('getLastName'));
        $cols['email'] = array('label' => 'email', 'id' => 'u.email', 'method' => array('getEmail'));
        $cols['enabled'] = array('label' => 'enabled', 'id' => 'u.enabled', 'method' => array('isEnabled'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'u.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('u'))
            ->from('PaprecUserBundle:User', 'u')
            ->where('u.deleted IS NULL')
        ;

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('u.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('u.username', '?1'),
                    $queryBuilder->expr()->like('u.firstName', '?1'),
                    $queryBuilder->expr()->like('u.lastName', '?1'),
                    $queryBuilder->expr()->like('u.email', '?1'),
                    $queryBuilder->expr()->like('u.dateCreation', '?1')
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
     * @Route("/export", name="paprec_user_user_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function exportAction(Request $request)
    {

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('u'))
            ->from('PaprecUserBundle:User', 'u')
            ->where('u.deleted is NULL');

        $users = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Utilisateurs")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Société')
            ->setCellValue('C1', 'Prénom')
            ->setCellValue('D1', 'Nom')
            ->setCellValue('E1', 'E-mail')
            ->setCellValue('F1', 'Identifiant')
            ->setCellValue('G1', 'Rôle')
            ->setCellValue('H1', 'Activé')
            ->setCellValue('I1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Utilisateurs');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach($users as $user) {

            $roles = array();

            foreach($user->getRoles() as $role) {
                if($role != 'ROLE_USER') {
                    $roles[] = $translator->trans($role);
                }
            }

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $user->getId())
                ->setCellValue('B'.$i, $user->getCompanyName())
                ->setCellValue('C'.$i, $user->getFirstName())
                ->setCellValue('D'.$i, $user->getLastName())
                ->setCellValue('E'.$i, $user->getEmail())
                ->setCellValue('F'.$i, $user->getUsername())
                ->setCellValue('G'.$i, implode(',', $roles))
                ->setCellValue('H'.$i, $user->isEnabled())
                ->setCellValue('I'.$i, $user->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Utilisateurs-'.date('Y-m-d').'.xlsx';

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
     * @Route("/view/{id}", name="paprec_user_user_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function viewAction(Request $request, User $user)
    {
        if($user->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        return $this->render('PaprecUserBundle:User:view.html.twig', array(
            'user' => $user
        ));
    }

    /**
     * @Route("/add", name="paprec_user_user_add")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function addAction(Request $request)
    {

        $user = new User();

        $roles = array();
        foreach($this->getParameter('security.role_hierarchy.roles') as $role => $children) {
            $roles[$role] = $role;
        }

        $divisions = array();
        foreach($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(UserType::class, $user, array(
            'roles' => $roles,
            'divisions' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $user->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('paprec_user_user_view', array(
                'id' => $user->getId()
            ));

        }

        return $this->render('PaprecUserBundle:User:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/edit/{id}", name="paprec_user_user_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function editAction(Request $request, User $user)
    {
        if($user->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $roles = array();
        foreach($this->getParameter('security.role_hierarchy.roles') as $role => $children) {
            $roles[$role] = $role;
        }

        $divisions = array();
        foreach($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(UserType::class, $user, array(
            'roles' => $roles,
            'divisions' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $user->setDateUpdate(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_user_user_view', array(
                'id' => $user->getId()
            ));

        }

        return $this->render('PaprecUserBundle:User:edit.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }

    /**
     * @Route("/editMyProfile", name="paprec_user_user_editMyProfile")
     * @Security("has_role('ROLE_USER')")
     */
    public function editMyProfileAction(Request $request)
    {

        $user = $this->getUser();

        $form = $this->createForm(UserMyProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $user->setDateUpdate(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_home_dashboard');

        }

        return $this->render('PaprecUserBundle:User:editMyProfile.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }

    /**
     * @Route("/sendAccess/{id}", name="paprec_user_user_sendAccess")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function sendAccessAction(Request $request, User $user)
    {

        if(! $user->isEnabled()) {

            $this->get('session')->getFlashBag()->add('errors', 'userIsNotEnabled');

            return $this->redirectToRoute('paprec_user_user_view', array(
                'id' => $user->getId()
            ));
        }

        $tokenGenerator = $this->container->get('fos_user.util.token_generator');
        $password = substr($tokenGenerator->generateToken(), 0, 8);

        $user->setPlainPassword($password);
        $user->setDateUpdate(new \DateTime);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $message = \Swift_Message::newInstance()
            ->setSubject('Easy-Recyclage : Identifiants')
            ->setFrom($this->getParameter('paprec_email_sender'))
            ->setTo($user->getEmail())
            ->setBody($this->container->get('templating')->render('PaprecUserBundle:User:sendAccessEmail.html.twig', array(
                'user' => $user,
                'password' => $password,
            )), 'text/html')
        ;

        if($this->container->get('mailer')->send($message)) {
            $this->get('session')->getFlashBag()->add('success', 'accessHasBeenSent');
        }
        else {
            $this->get('session')->getFlashBag()->add('error', 'accessCannotBeSent');
        }
        return $this->redirectToRoute('paprec_user_user_view', array(
            'id' => $user->getId()
        ));
    }

    /**
     * @Route("/sendAccessMany/{ids}", name="paprec_user_user_sendAccessMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function sendAccessManyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids = $request->get('ids');

        if(! $ids) {
            throw new NotFoundHttpException();
        }

        $ids = explode(',', $ids);

        if(is_array($ids) && count($ids)) {
            $users = $em->getRepository('PaprecUserBundle:User')->findById($ids);
            foreach ($users as $user) {

                if($user->isEnabled()) {

                    $tokenGenerator = $this->container->get('fos_user.util.token_generator');
                    $password = substr($tokenGenerator->generateToken(), 0, 8);

                    $user->setPlainPassword($password);
                    $user->setDateUpdate(new \DateTime);
                    $em->flush();

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Easy-Recyclage : Identifiants')
                        ->setFrom($this->getParameter('paprec_email_sender'))
                        ->setTo($user->getEmail())
                        ->setBody($this->container->get('templating')->render('PaprecUserBundle:User:sendAccessEmail.html.twig', array(
                            'user' => $user,
                            'password' => $password,
                        )), 'text/html')
                    ;


                    if($this->container->get('mailer')->send($message)) {
                        $this->get('session')->getFlashBag()->add('success', array('msg' => 'accessHasBeenSent', 'var' => $user->getEmail()));
                    }
                    else {
                        $this->get('session')->getFlashBag()->add('error', array('msg' => 'accessCannotBeSent', 'var' => $user->getEmail()));
                    }
                }

            }
        }

        return $this->redirectToRoute('paprec_user_user_index');
    }

    /**
     * @Route("/remove/{id}", name="paprec_user_user_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function removeAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $user->setDeleted(new \DateTime);
        $user->setEnabled(false);
        $em->flush();

        return $this->redirectToRoute('paprec_user_user_index');
    }

    /**
     * @Route("/removeMany/{ids}", name="paprec_user_user_removeMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function removeManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if(! $ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if(is_array($ids) && count($ids)) {
            $users = $em->getRepository('PaprecUserBundle:User')->findById($ids);
            foreach ($users as $user){
                $user->setDeleted(new \DateTime);
                $user->setEnabled(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_user_user_index');
    }


}
