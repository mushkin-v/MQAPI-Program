<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testStatus()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/status');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("homepage")')->count() > 0);
    }

    public function testPercentage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/percentage');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("0")')->count() > 0);
    }
}
