<?php

namespace Melk\ExtendedLoginBundle\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * WrongCaptchaException thrown when received wrong captcha value.
 *
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class WrongCaptchaException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Wrong captcha value.';
    }
}
