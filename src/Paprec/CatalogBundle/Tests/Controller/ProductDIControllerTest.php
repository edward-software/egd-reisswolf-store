<?php

namespace Paprec\CatalogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductDIControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/productDI');
    }

    public function testLoadlist()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/productDI/loadList');
    }

}
