<?php

namespace Afpa\PokerGameBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TablePokerControllerControllerTest extends WebTestCase
{
    public function testListtable()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/listTable');
    }

    public function testPlay()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/play/{id}');
    }

}
