<?php

namespace Melk\ExtendedLoginBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class RedisClientCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $serviceDefinition = $container->getDefinition('melk_extended_login.service.captcha_login_service');

        $redisClient = 'snc_redis.'.$container->getParameter('melk_extended_login.redis_client');
        if ($container->hasDefinition($redisClient)) $serviceDefinition->addArgument($container->getDefinition($redisClient));
    }
}
