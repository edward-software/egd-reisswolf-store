<?php
namespace Goondi\ToolsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Buzz\Message\Request as BuzzRequest;
use Buzz\Message\Response as BuzzResponse;
use Buzz\Client\Curl as BuzzCurl;

class AuthCommand extends Command implements ContainerAwareInterface
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
		->setName('goondi:tools:auth')
		->setDescription('Generate a Token')
		->addArgument(
                    'username',
                    InputArgument::REQUIRED,
                    'Enter a username'
		)
		->addArgument(
                    'password',
                    InputArgument::REQUIRED,
                    'Enter a password'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
            $api_url = $this->container->getParameter('api_url');
            
            if(! $api_url)
            {
                die('No api url');
            }
            
            $post = array(
                'username' =>  $input->getArgument('username'),
                'password' =>  $input->getArgument('password')
            );
        
        
            $request = new BuzzRequest('POST', '/login_check', $api_url);
            $request->setContent(http_build_query($post));
            $response = new BuzzResponse();

            $client = new BuzzCurl();
            $client->setVerifyPeer(true);
            $client->send($request, $response);
            $response = $response->getContent();
            $response = json_decode($response, true);
            if(isset($response['token']))
            {
                exit("\n\n".$response['token']."\n\n");
            }
            die(print_r($response, true));

	}
}