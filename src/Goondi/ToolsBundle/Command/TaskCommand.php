<?php
namespace Goondi\ToolsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Goondi\ToolsBundle\Entity\Task;
use Symfony\Component\Process\Process;

class TaskCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    protected function configure()
    {
            $this
            ->setName('goondi:task:execute')
            ->setDescription('Execute a task')
            ->addArgument(
                            'queue',
                            InputArgument::REQUIRED,
                            'Which queue do you want to execute ?'
            )
            ->addArgument(
                            'executeType',
                            InputArgument::OPTIONAL,
                            '(empty) | intime : Execute only task with dateToExecute'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getArgument('queue');
        $executeType = $input->getArgument('executeType');

        $taskManager = $this->container->get('goondi_tools.task');

        if($taskManager->isValidQueue($queue))
        {
            if($executeType == 'intime')
            {
                $tasks = $taskManager->getInTimeTaskToExecute($queue);
                if(is_array($tasks) && count($tasks))
                {
                    print('Task to execute :');
                    foreach($tasks as $task)
                    {
                        print($task->getId().', ');
                    }
                    
                    
                    foreach($tasks as $task)
                    {
                        $taskManager->process($task);
                    }
                }
            }
            else
            {
                $processingTask = $taskManager->getProcessingTask($queue);

                if(! $processingTask)
                {
                    $nextTask = $taskManager->getNextTaskToExecute($queue);

                    while($nextTask)
                    {	
                        $taskManager->process($nextTask);
                        $nextTask = $taskManager->getNextTaskToExecute($queue);
                    }

                }
                else
                {
                    print("task is processing\n");	
                }
            }
        }
        else
        {
            print("no valid queue name\n");	
        }

    }
}