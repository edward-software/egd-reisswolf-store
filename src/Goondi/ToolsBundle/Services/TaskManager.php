<?php

namespace Goondi\ToolsBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\Process;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Respect\Validation\Validator as V;

use Goondi\ToolsBundle\Entity\Task;

class TaskManager
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

    public function add($command, $args, $title, $priority, $queue, $user, $dateToExecute = null)
    {
        
        $this->container->get('goondi_tools.logger')->info('task', 'Add');
        
        if(! V::notEmpty()->validate($command) || ! V::stringType()->validate($command))
        {
            $this->container->get('goondi_tools.logger')->error('task', 'Add', array('errorCode' => 400, 'errorMessage' => 'commandIsNotValid', 'command' => $command));
            return;
        }
        
        if(! V::arrayType()->validate($args))
        {
            $this->container->get('goondi_tools.logger')->error('task', 'Add', array('errorCode' => 400, 'errorMessage' => 'argsIsNotValid', 'args' => $args));
            return;
        }
        
        if(! V::notEmpty()->validate($title) || ! V::stringType()->validate($title))
        {
            $this->container->get('goondi_tools.logger')->error('task', 'Add', array('errorCode' => 400, 'errorMessage' => 'titleIsNotValid', 'title' => $title));
            return;
        }
        
        if(! V::notEmpty()->validate($priority) || ! V::intType()->validate($priority))
        {
            $this->container->get('goondi_tools.logger')->error('task', 'Add', array('errorCode' => 400, 'errorMessage' => 'priorityIsNotValid', 'priority' => $priority));
            return;
        }
        
        if(! $this->isValidQueue($queue))
        {
            $this->container->get('goondi_tools.logger')->error('task', 'Add', array('errorCode' => 400, 'errorMessage' => 'queueIsNotValid', 'queue' => $queue));
            return;
        }
        
        try {
            
            $statusList = $this->container->getParameter('goondi_tools.task.status');
            $pendingStatus = $statusList['pending'];
        
            $task = new Task();
            if($user)
            {
                $task->setUserCreation($user);
                $task->setUserUpdate($user);
            }
            $task->setIsValid(true);
            $task->setCommand($command);
            $task->setArgs($args);
            $task->setTitle($title);
            $task->setPriority($priority);
            $task->setQueue($queue);
            $task->setStatus($pendingStatus);
            
            if($dateToExecute instanceof \DateTime)
            {
                $task->setDateToExecute($dateToExecute);
            }

            $this->em->persist($task);
            $this->em->flush();

            return $task;
            
        } catch (ORMException $e) {
            $this->container->get('goondi_tools.logger')->error('task', 'Add', array('errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()));
            return;
        }

    }

    public function isValidQueue($queue)
    {
        $queues = $this->container->getParameter('goondi_tools.task.queues');
        if(in_array($queue, $queues))
        {
            return true;
        }

        return false;		
    }

    public function isValidStatus($status)
    {
        $statusList = $this->container->getParameter('goondi_tools.task.status');

        if(in_array($status, $statusList))
        {
            return true;
        }

        return false;		
    }

    public function getProcessingTask($queue)
    {
        $statusList = $this->container->getParameter('goondi_tools.task.status');
        $processingStatus = $statusList['processing'];
        
        $task = $this->em->getRepository('GoondiToolsBundle:Task')->findOneBy(array(
            'queue' => $queue,
            'status' => $processingStatus
            )
        );
        
        return $task;
    }

    public function getNextTaskToExecute($queue)
    {
        $statusList = $this->container->getParameter('goondi_tools.task.status');
        $pendingStatus = $statusList['pending'];
        
        $queryBuilder = $this->em->createQueryBuilder();
         
        $queryBuilder->select(array('t'))
            ->from('GoondiToolsBundle:Task', 't')
            ->Where($queryBuilder->expr()->eq('t.queue', '?1'))
            ->andWhere($queryBuilder->expr()->eq('t.status', '?2'))
            ->andWhere('t.dateToExecute IS NULL')
            ->setParameter(1, $queue)
            ->setParameter(2, $pendingStatus)
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.dateCreation', 'ASC')
            ->setMaxResults(1);
        
        try
        {
            $task = $queryBuilder->getQuery()->getSingleResult();
            return $task;
        } catch (NoResultException $ex) {

        }
        
        return false;
    }
    
    public function getInTimeTaskToExecute($queue)
    {
        $statusList = $this->container->getParameter('goondi_tools.task.status');
        $pendingStatus = $statusList['pending'];
        
        $queryBuilder = $this->em->createQueryBuilder();
         
        $queryBuilder->select(array('t'))
            ->from('GoondiToolsBundle:Task', 't')
            ->Where($queryBuilder->expr()->eq('t.queue', '?1'))
            ->andWhere($queryBuilder->expr()->eq('t.status', '?2'))
            ->andWhere('t.dateToExecute < :now')
            ->setParameter(1, $queue)
            ->setParameter(2, $pendingStatus)
            ->setParameter('now', new \DateTime)
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.dateCreation', 'ASC');
        
        try
        {
            $tasks = $queryBuilder->getQuery()->getResult();
            return $tasks;
        } catch (NoResultException $ex) {

        }
        
        return false;
    }

    public function process($task)
    {
        $this->container->get('goondi_tools.logger')->info('task', 'Start', array('taskId' => $task->getId()));
        
        $statusList = $this->container->getParameter('goondi_tools.task.status');
        $timeout = $this->container->getParameter('goondi_tools.task.timeout');
        $pendingStatus = $statusList['pending'];
        $processingStatus = $statusList['processing'];
        $completedStatus = $statusList['completed'];
        $errorStatus = $statusList['error'];
        $canceledStatus = $statusList['canceled'];
        $logger = $this->logger;
        
        // Important reload task information from database, to get the real current status
        $this->em->refresh($task);
        
        if($task->getStatus() != $pendingStatus && $task->getStatus() != $errorStatus)
        {
            // Skip this task
            return true;
        }
        
        
        try
        {
            $task->setStatus($processingStatus);
            $task->setDateStart(new \DateTime);
            $this->em->flush();            
        }
        catch(ORMException $e)
        {
            $this->container->get('goondi_tools.logger')->error('task', 'Process - Unable to change task status', array('taskId' => $task->getId(), 'errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()));
            return false;
        }
            
        $command = $task->getCommand();

        if($command != '')
        {
            $command = $command.' '.$task->getId().' '.implode(' ', $task->getArgs());
            $process = new Process($command);
            $process->setTimeout($timeout);
            $process->run(
                function ($type, $buffer) use ($logger) {
                    if ('err' === $type) {
                        $logger->error($buffer);
                        $this->container->get('goondi_tools.logger')->error('task', $buffer, array());
                        
                    } else {
                        $this->container->get('goondi_tools.logger')->info('task', $buffer, array());
                    }
                }
            );
        
            try
            {
                $this->em->refresh($task);
                if($task->getStatus() != $canceledStatus)
                {
                    $task->setStatus($completedStatus);                
                }
                if (! $process->isSuccessful())
                {
                    $task->setStatus($errorStatus);
                }                
                $task->setResult($process->getOutput());                
                $task->setDateEnd(new \DateTime);
                $this->em->flush();
                return true;
            }
            catch(ORMException $e)
            {
                $this->container->get('goondi_tools.logger')->error('task', 'Process - Unable to save task result', array('taskId' => $task->getId(), 'errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()));
            }
            
        }
        else
        {
            $this->container->get('goondi_tools.logger')->error('task', 'Process - Command is empty', array('taskId' => $task->getId()));

        }
        
        return false;
    }


    public function changeStatus(Task $task, $status, $user)
    {

        if(! $task)
        {
            throw new Exception('taskNotFound', 404);
        }

        if(! $this->isValidStatus($status))
        {
            throw new Exception('statusIsNotValid', 500);
        }

        if($this->isCompleted($task))
        {
            throw new Exception('taskIsAlreadyCompleted', 500);
        }

        try
        {
            $task->setStatus($status);

            $this->em->flush();

            return $task;
        }
        catch(ORMException $e)
        {
            $this->logger->error('Task:TaskManager:ChangeStatus - Unable to change task status : '.$e->getMessage());
            throw new Exception('databaseError', 500);
        }
    }


    public function isStopped($taskId)
    {
        if($taskId)
        {
            $statusList = $this->container->getParameter('goondi_tools.task.status');
            $stoppedStatus = $statusList['stopped'];

            $status = $this->getStatus($taskId);
            if($status && $status == $stoppedStatus)
            {
                return true;
            }
        }

        return false;
    }

    public function isCanceled($taskId)
    {
        if($taskId)
        {
            $statusList = $this->container->getParameter('goondi_tools.task.status');
            $canceledStatus = $statusList['canceled'];

            $status = $this->getStatus($taskId);
            if($status && $status == $canceledStatus)
            {
                return true;
            }
        }

        return false;
    }


    public function isCompleted($task)
    {
        if($task)
        {
            $statusList = $this->container->getParameter('goondi_tools.task.status');
            $completedStatus = $statusList['completed'];
            if($task->getStatus() == $completedStatus)
            {
                return true;
            }
        }

        return false;
    }

    /*
     * Get Status without create object, important for bulk use process
     */
    public function getStatus($id)
    {
        $queryBuilder = $this->em->createQueryBuilder();

        $queryBuilder
            ->select('t.status')
            ->from('GoondiToolsBundle:Task', 't')
            ->where('t.id = :id')
            ->setParameter('id', $id);

        $task = $queryBuilder->getQuery()->getSingleResult();

        return $task['status'];
    }
	
}