<?php

namespace Paprec\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PaprecUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
