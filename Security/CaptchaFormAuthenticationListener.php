<?php

namespace Melk\ExtendedLoginBundle\Security;

use Melk\ExtendedLoginBundle\Service\CaptchaLoginService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class CaptchaFormAuthenticationListener extends UsernamePasswordFormAuthenticationListener
{
    /**
     * @var CaptchaLoginService
     */
    private $captchaLoginService;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        HttpUtils $httpUtils,
        $providerKey,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $options = array(),
        LoggerInterface $logger = null,
        EventDispatcherInterface $dispatcher = null,
        CaptchaLoginService $captchaLoginService,
        $csrfTokenManager = null
    ) {
        parent::__construct(
            $tokenStorage,
            $authenticationManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            $options,
            $logger,
            $dispatcher,
            $csrfTokenManager
        );

        $this->captchaLoginService = $captchaLoginService;
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        if ($this->captchaLoginService->isCaptchaRequired($request)) {
            // check if captcha is valid
            if ($this->options['post_only']) {
                $code = ParameterBagUtils::getParameterBagValue($request->request, $this->options['captcha_parameter']);
            } else {
                $code = ParameterBagUtils::getRequestParameterValue($request, $this->options['captcha_parameter']);
            }
            if (!$this->captchaLoginService->validateCaptchaCode($code)) {
                throw new WrongCaptchaException('Wrong captcha');
            }
        }

        try {
            // try to login using simple form
            return parent::attemptAuthentication($request);
        } catch (AuthenticationException $authenticationException) {
            // if login failed update failed attempts and throw exception
            $this->captchaLoginService->onFailedRequest($request);
            throw $authenticationException;
        }
    }
}
