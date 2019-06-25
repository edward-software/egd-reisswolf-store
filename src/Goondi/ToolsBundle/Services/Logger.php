<?php

namespace Goondi\ToolsBundle\Services;

use Symfony\Component\DependencyInjection\Container;

class Logger
{

    private $em;
    private $container;

    public function __construct($em, Container $container)
    {
        $this->em = $em;
        $this->container = $container;
    }
    
    private function log($level, $channel, $message, $data)
    {
        $userId = 0;
        $username = 'noUserOrCron';
        $userIp = null;
        $query = null;
        $params = null;
        
        if($this->container->get('security.token_storage')->getToken() != null)
        {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $userId = $user->getId();
            $username = $user->getUsername();
            $request = $this->container->get('request');
            $userIp = $request->getClientIp();
            $query = $request->query->all();
            $params = $request->request->all();
        }
        
        
        $context = array(
            "userId" => $userId,
            "user" => $username,
            "userIp" => $userIp,
            "query" => $query,
            "params" => $params,
            "data" => $data
        );        
        
        $this->container->get('monolog.logger.'.$channel)->log($level, $message, $context);
        
    }
    
    public function info($channel, $message, $data = null)
    {
        $this->log('info', $channel, $message, $data);        
    }
    
    
    public function error($channel, $message, $data = null)
    {
        $this->log('error', $channel, $message, $data); 
    }
}
