<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/category');
    }

    public function testEdit()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/category/{id}/edit');
    }

    public function testShow()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/category/{id}');
    }

    public function testNew()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/category/new');
    }

    public function testDelete()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/category/{id}');
    }

}
