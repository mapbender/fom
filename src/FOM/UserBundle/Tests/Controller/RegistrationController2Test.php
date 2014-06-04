<?php

namespace FOM\UserBundle\Controller;

use FOM\Component\Test\SharedApplicationWebTestCase;

class RegistrationController2Test extends SharedApplicationWebTestCase
{
    // Using a different config environment. Due to PHP's crazy late static
    // binding for properties, we have to redeclare $application and $client
    // as well... Oh well...
    protected static $application;
    protected static $client;
    protected static $options = array(
        'environment' => 'test_selfregister');

    /**
     * Test a full self registration workflow
     */
    public function testSelfRegistration() {
        $client = static::$client;

        // Get registration form
        $crawler = $client->request('GET', '/user/registration');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Send form
        $button = $crawler->filter('form button');
        $form = $button->form(array(
            'User[username]' => 'testuser',
            'User[password][first]' => 'testingpass',
            'User[password][second]' => 'testingpass',
            'User[email]' => 'testuser@example.com',
        ));
        $client->submit($form);
        $this->assertContains($client->getResponse()->getStatusCode(), array(301, 302));

        // Check E-Mail
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting e-mail data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals(1, preg_match('/\/user\/activate\?token=(\w+)/',
            $message->getBody(), $matches));
        $token = $matches[1];
        // @todo: Go on with token
    }
}
