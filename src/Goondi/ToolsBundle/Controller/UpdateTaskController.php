<?php

namespace Goondi\ToolsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Goondi\ToolsBundle\Entity\UpdateTask;
use Wizall\UserBundle\Entity\User;

class UpdateTaskController extends Controller
{

	/**
	 * @Secure(roles="ROLE_ADMIN_WIZALL")
	 */
	public function viewAction(UpdateTask $updateTask)
	{
        if($updateTask->getDeleted() !== null)
        {
            throw $this->createNotFoundException('Object not existing');                
        }

        $systemUser = $this->getUser();
        $timezone = $systemUser->getTimezone();

        return $this->render('GoondiToolsBundle:UpdateTask:view.html.twig', array(
            'updateTask' => $updateTask,
            'timezone' => $timezone
        ));
	}
	
    /**
     * @Secure(roles="ROLE_ADMIN_WIZALL")
     */
    public function listAction()
    {
        return $this->render('GoondiToolsBundle:UpdateTask:list.html.twig');
    }

    /**
     * @Secure(roles="ROLE_ADMIN_WIZALL")
     */
    public function loadListAction(Request $request)
    {
        $return = array();

        $status = $request->get('status');
        
        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = array('label' => 'id', 'id' => 'up.id', 'method' => array('getId'));
        $cols['status'] = array('label' => 'status', 'id' => 'up.status', 'method' => array('getStatus'));
        $cols['action'] = array('label' => 'action', 'id' => 'up.action', 'method' => array('getAction'));
        $cols['object'] = array('label' => 'object', 'id' => 'up.object', 'method' => array('getObject'));
        $cols['objectId'] = array('label' => 'objectId', 'id' => 'up.objectId', 'method' => array('getObjectId'));
        $cols['userCreationUsername'] = array('label' => 'userCreationUsername', 'id' => 'up.userCreation', 'method' => array('getUserCreation', 'getUsername'));
        
        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();
        
        $queryBuilder->select(array('up'))
            ->from('GoondiToolsBundle:UpdateTask', 'up')
            ->where('up.deleted IS NULL');

        if($status)
        {
            $queryBuilder->andWhere('up.status = :status')->setParameter('status', $status);
        }
        
        
        if(is_array($search) && isset($search['value']) && $search['value'] != '')
        {           
            $queryBuilder->andWhere($queryBuilder->expr()->orx(
                $queryBuilder->expr()->like('up.action', '?1'),
                $queryBuilder->expr()->like('up.currentValue', '?1'),
                $queryBuilder->expr()->like('up.newValue', '?1')
            ))->setParameter(1, '%'.$search['value'].'%');
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        $response = new Response(json_encode($return));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Secure(roles="ROLE_ADMIN_WIZALL")
     */
    public function removeAction(UpdateTask $updateTask)
    {
        if($updateTask->getDeleted() !== null)
        {
            throw $this->createNotFoundException('Object not existing');
        }

        try {

            $updateTask->setDeleted(new \DateTime);
            $em = $this->getDoctrine()->getManager();
            $em->persist($updateTask);
            $em->flush();

            return $this->redirect($this->generateUrl('goondi_tools_updateTask_list'));

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }

    /**
     * @Secure(roles="ROLE_ADMIN_WIZALL")
     */
    public function acceptAction(UpdateTask $updateTask)
    {

        $systemUser = $this->getUser();

        try {

            $updateTaskManager = $this->container->get('goondi_tools.updateTask');

            $updateTaskManager->accept($updateTask->getId(), $systemUser);

            return $this->redirect($this->generateUrl('goondi_tools_updateTask_list'));

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }
}
