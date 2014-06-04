<?php

namespace FOM\UserBundle\Controller;

use FOM\Component\Test\SharedApplicationWebTestCase;

class RegistrationControllerTest extends SharedApplicationWebTestCase
{
    /**
     * Test that self registration is disabled with the default configuration.
     */
    public function testIndex() {
        $crawler = self::$client->request('GET', '/user/registration');
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Test a full self registration workflow
     */
    public function testSelfRegistration() {
        $client = static::createClient(array(
            'environment' => 'test_selfregister'
        ));

        $crawler = $client->request('GET', '/user/registration');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
