<?php

namespace FOM\UserBundle\Controller;

use FOM\Component\Test\SharedApplicationWebTestCase;

class RegistrationControllerTest extends SharedApplicationWebTestCase
{
    public function testIndex() {
        $crawler = self::$client->request('GET', '/user/registration');
        $this->assertEquals(self::$client->getResponse()->getStatusCode(), 403);
    }
}
