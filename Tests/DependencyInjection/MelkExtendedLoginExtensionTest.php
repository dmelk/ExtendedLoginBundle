<?php

namespace Melk\ExtendedLoginBundle\Tests\DependencyInjection;

use Melk\ExtendedLoginBundle\DependencyInjection\MelkExtendedLoginExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class MelkExtendedLoginExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $builder = new ContainerBuilder();
        $configs = [];

        $extension = new MelkExtendedLoginExtension();
        $extension->load($configs, $builder);

        $this->assertEquals('captcha_login', $builder->getParameter('melk_extended_login.redis_client'), 'Redis client');
        $this->assertEquals(5, $builder->getParameter('melk_extended_login.max_attempts'), 'Max attempts');
        $this->assertEquals(300, $builder->getParameter('melk_extended_login.attempts_period'), 'Attempts period');
        $this->assertEquals(1800, $builder->getParameter('melk_extended_login.captcha_period'), 'Captcha period');
    }
}
