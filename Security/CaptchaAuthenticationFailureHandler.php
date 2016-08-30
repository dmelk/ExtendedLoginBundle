<?php

namespace Melk\ExtendedLoginBundle\Security;

use Melk\ExtendedLoginBundle\Service\CaptchaLoginService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * CaptchaAuthenticationFailureHandler provides information about captcha if XML Http request received.
 *
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class CaptchaAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * @var CaptchaLoginService
     */
    private $captchaService;

    /**
     * Constructor.
     *
     * @param HttpKernelInterface $httpKernel
     * @param HttpUtils           $httpUtils
     * @param CaptchaLoginService $captchaService
     * @param array               $options        Options for processing a failed authentication attempt
     * @param LoggerInterface     $logger         Optional logger
     */
    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, CaptchaLoginService $captchaService, array $options = array(), LoggerInterface $logger = null)
    {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);

        $this->captchaService = $captchaService;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $result = array(
                'success' => false,
                'message' => $exception->getMessage(),
            );

            if ($this->captchaService->isCaptchaRequired($request)) {
                $result['captcha'] = $this->captchaService->getCaptchaUrl();
            }

            return new JsonResponse($result);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
