<?php

namespace Paprec\UserBundle\Twig\Extension;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AccessExtension extends \Twig_Extension
{

    private $container;
    private $token;

    public function __construct(Container $container, TokenStorage $token)
    {
        $this->container = $container;
        $this->token = $token;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('paprec_has_access', array($this, 'hasAccess')),
        );
    }


    /**
     * @param $role
     * @param null $division
     * @return bool
     */
    public function hasAccess($role, $division = null)
    {
        $token = $this->token->getToken();
        if ($token->isAuthenticated() && $token->getUser()) {
            if ($division && $division != null) {
                if(!$this->container->get('security.authorization_checker')->isGranted($role)) {
                    return false;
                }
                if (!in_array($division, $token->getUser()->getDivisions())) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'paprec_has_access';
    }
}
