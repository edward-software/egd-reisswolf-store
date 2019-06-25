<?php
namespace Goondi\ToolsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

use Buzz\Message\Request as BuzzRequest;
use Buzz\Message\Response as BuzzResponse;
use Buzz\Client\Curl as BuzzCurl;

class TestCommand extends Command implements ContainerAwareInterface
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
		->setName('goondi:tools:test')
		->setDescription('Make a test')
		->addArgument(
			'var1',
			InputArgument::OPTIONAL,
			'Enter a var1'
		)
		->addArgument(
			'var2',
			InputArgument::OPTIONAL,
			'Enter a var2'
		)
		->addArgument(
			'var3',
			InputArgument::OPTIONAL,
			'Enter a var3'
		)
		->addArgument(
			'var4',
			InputArgument::OPTIONAL,
			'Enter a var4'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $var1 = $input->getArgument('var1');
		$var2 = $input->getArgument('var2');
		$var3 = $input->getArgument('var3');
		$var4 = $input->getArgument('var4');

		/*$userInfoManager = $this->container->get('wizall_eew.userInfo');
		$transactionManager = $this->container->get('wizall_wallet.transaction');
		$userManager = $this->container->get('wizall_user.user');

		$transactionId = $var1;
		$userId = $var2;

		$user = $userManager->get($userId);

		$transaction = $transactionManager->get($transactionId, $user);

		$userInfoClass = $userInfoManager->getClass($user);
		if (!$userInfoClass) {
			throw new Exception('userError', 500);
		}

		$userInfoClass->createTransaction($transaction, $user);
		$userInfoClass->debitTransaction($transaction, $user);*/


		$restManager = $this->container->get('wizall_api.rest_manager');

		$wallet = $restManager->send('emi', 'post', '/transfers', array(), array(
			'tag' => 'test',
			'currency_code' => 978,
			'value' => 100,
			'fee_value' => 10,
			'debited_wallet_id' => 3,
			'credited_wallet_id' => 4,
			'commissions' => array(
				array(
					'type' => 'CASHOUT',
					'value' => 25,
					'value_type' => 'RATE',
					'wallet_id' => 7
				),
				array(
					'type' => 'CASHIN',
					'value' => 5,
					'value_type' => 'AMOUNT',
					'wallet_id' => 2
				)
			)
		));


		print_r($wallet);

	}
}














