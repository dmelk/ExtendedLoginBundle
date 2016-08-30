<?php

namespace Melk\ExtendedLoginBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ExtendedFormLoginFactory creates a service for melk form login authentication: general symfony form login with
 * captcha validation.
 *
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class MelkFormLoginFactory extends FormLoginFactory
{
    public function __construct()
    {
        parent::__construct();

        $this->addOption('captcha_parameter', '_captcha');
    }

    public function getPosition()
    {
        return 'form';
    }

    public function getKey()
    {
        return 'melk-form-login';
    }

    protected function getListenerId()
    {
        return 'melk_extended_login.security.captcha_form_authentication_listener';
    }

    protected function createAuthenticationFailureHandler($container, $id, $config)
    {
        $id = $this->getFailureHandlerId($id);
        $options = array_intersect_key($config, $this->defaultFailureHandlerOptions);

        if (isset($config['failure_handler'])) {
            $failureHandler = $container->setDefinition($id, new DefinitionDecorator('security.authentication.custom_failure_handler'));
            $failureHandler->replaceArgument(0, new Reference($config['failure_handler']));
            $failureHandler->replaceArgument(1, $options);
        } else {
            $failureHandler = $container->setDefinition($id, new DefinitionDecorator('melk_extended_login.security.captcha_authentication_failure_handler'));
            $failureHandler->addMethodCall('setOptions', array($options));
        }

        return $id;
    }
}
