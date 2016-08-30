<?php

namespace Melk\ExtendedLoginBundle;

use Melk\ExtendedLoginBundle\DependencyInjection\CompilerPass\RedisClientCompilerPass;
use Melk\ExtendedLoginBundle\DependencyInjection\Security\Factory\MelkFormLoginFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class MelkExtendedLoginBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension SecurityExtension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new MelkFormLoginFactory());

        $container->addCompilerPass(new RedisClientCompilerPass());
    }
}
