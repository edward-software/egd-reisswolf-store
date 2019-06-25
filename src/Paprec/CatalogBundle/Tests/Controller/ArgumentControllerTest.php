<?php

namespace Paprec\CatalogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArgumentControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/argument');
    }

    public function testLoadlist()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/argument/loadList');
    }

    public function testExport()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/argument/export');
    }

    public function testView()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/argument/view/{id}');
    }

    public function testAdd()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/argument/add');
    }

    public function testEdit()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/argument/edit');
    }

    public function testRemove()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/argument/remove/{id}');
    }

    public function testRemovemany()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/argument/removeMany/{ids}');
    }

}
