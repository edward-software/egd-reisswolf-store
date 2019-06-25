<?php

namespace Goondi\ToolsBundle\Services;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\Process;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Respect\Validation\Validator as V;

use Goondi\ToolsBundle\Entity\UpdateTask;

class UpdateTaskManager
{

    private $em;
    private $container;
    private $logger;

    public function __construct($em, Container $container, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->container = $container;
        $this->logger = $logger;
    }

    public function get($id)
    {
        if(! $id || ! V::int()->validate($id))
        {
            throw new Exception('updateTaskIdNotValid', 400);
        }

        $updateTask = $this->em->getRepository('GoondiToolsBundle:UpdateTask')->find($id);

        if(! $updateTask || $updateTask->getDeleted() !== null)
        {
            throw new Exception('noUpdateTask', 404);
        }

        return $updateTask;

    }

    public function add($userCreation, $action, $objectId, $currentValue, $newValue)
    {

        $this->container->get('goondi_tools.logger')->info('updateTask', 'Add');

        try {

            $statusList = $this->getStatusList();
            $pendingStatus = $statusList['pending'];

            $actionList = $this->container->getParameter('goondi_tools_updateTask.actions');
            if(! array_key_exists($action, $actionList))
            {
                throw new Exception('actionNotAuthorized', 500);
            }

            $updateTask = new UpdateTask();
            $updateTask->setUserCreation($userCreation);
            $updateTask->setDateCreation(new \DateTime);
            $updateTask->setStatus($pendingStatus);
            $updateTask->setCurrentValue($currentValue);
            $updateTask->setNewValue($newValue);
            $updateTask->setObjectId($objectId);
            $updateTask->setObject($actionList[$action]['object']);
            $updateTask->setAction($action);

            try {
                $this->em->persist($updateTask);
                $this->em->flush();

                $this->container->get('goondi_tools.logger')->info('updateTask', 'Add Done', array('id' => $updateTask->getId()));

                return $updateTask;
            }
            catch (ORMException $e) {
                $this->container->get('goondi_tools.logger')->error('updateTask', 'Add Error', array('errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()));
                throw new Exception($e->getMessage(), $e->getCode());
            }

        } catch (Exception $e) {
            $this->container->get('goondi_tools.logger')->error('updateTask', 'Add Error', array('errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()));
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }

    public function accept($id, $systemUser)
    {

        $this->container->get('goondi_tools.logger')->info('updateTask', 'Accept '.$id);

        $statusList = $this->getStatusList();
        $acceptedStatus = $statusList['accepted'];
        $pendingStatus = $statusList['pending'];

        try {

            $updateTask = $this->get($id);

            if($updateTask->getStatus() != $pendingStatus)
            {
                throw new Exception('updateTaskIsNotPending', 500);
            }

            if($updateTask->getUserCreation()->getId() == $systemUser->getId())
            {
                throw new Exception('thisUserCannotAcceptThisTask', 500);
            }

            try {
                $actions = $this->container->getParameter('goondi_tools_updateTask.actions');
                $action = $actions[$updateTask->getAction()];

                $service = $this->container->get($action['service']);

                $service->{$action['method']}($updateTask);

                $updateTask->setUserValidation($systemUser);
                $updateTask->setDateValidation(new \DateTime);
                $updateTask->setStatus($acceptedStatus);

                $this->em->persist($updateTask);
                $this->em->flush();

                $this->container->get('goondi_tools.logger')->info('updateTask', 'Add Accept Done '.$id);

                return $updateTask;
            }
            catch (ORMException $e) {
                $this->container->get('goondi_tools.logger')->error('updateTask', 'Add Accept Error '.$id, array('errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()));
                throw new Exception($e->getMessage(), $e->getCode());
            }

        } catch (Exception $e) {
            $this->container->get('goondi_tools.logger')->error('updateTask', 'Add Accept Error '.$id, array('errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()));
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }

    public function isValidStatus($status)
    {
        $statusList = $this->getStatusList();

        if(in_array($status, $statusList))
        {
            return true;
        }

        return false;		
    }

    public function getStatusList()
    {
        return $this->container->getParameter('goondi_tools_updateTask.status');
    }

	
}