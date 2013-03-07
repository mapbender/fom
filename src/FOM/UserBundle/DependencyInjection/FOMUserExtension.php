<?php

namespace FOM\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FOMUserExtension extends Extension {
    public function load(array $configs, ContainerBuilder $container) {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter("fom_user.selfregister", $config["selfregister"]);
        $container->setParameter("fom_user.reset_password", $config["reset_password"]);
        $container->setParameter("fom_user.max_registration_time", intval($config["max_registration_time"]));
        $container->setParameter("fom_user.max_reset_time", intval($config["max_reset_time"]));
        $container->setParameter("fom_user.mail_from_name", $config["mail_from_name"]);
        $container->setParameter("fom_user.mail_from_address", $config["mail_from_address"]);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('security.xml');
        $loader->load('services.xml');
    }

    public function getAlias() {
        return 'fom_user';
    }
}

