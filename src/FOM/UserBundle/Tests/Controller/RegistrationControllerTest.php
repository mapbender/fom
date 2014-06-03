<?php

namespace FOM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase {

    private static $client;

    private static $application;

    public function setUp() {
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:create');
        self::runCommand('doctrine:fixtures:load --fixtures=./mapbender/src/Mapbender/CoreBundle/DataFixtures/ORM/Epsg/ --append');
    }

    private static function runCommand($command) {
        $command = sprintf('%s --quiet', $command);
        return self::getApplication()->run(new StringInput($command));
    }

    private static function getApplication() {
        if(!self::$application) {
            self::$client = static::createClient();

            self::$application = new Application(self::$client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    public function testIndex() {
        $crawler = self::$client->request('GET', '/user/registration');
        $this->assertEquals(self::$client->getResponse()->getStatusCode(), 403);
    }

}
