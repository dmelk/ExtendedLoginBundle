<?php

namespace Melk\ExtendedLoginBundle\Tests\Service;

use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;
use Melk\ExtendedLoginBundle\Service\CaptchaLoginService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class CaptchaLoginServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CaptchaLoginService
     */
    private $captchaService;

    /**
     * @var int
     */
    private $maxAttempts = 1;

    /**
     * @var int
     */
    private $attemptsPeriod = 30;

    /**
     * @var int
     */
    private $captchaPeriod = 60;

    /**
     * @var CaptchaGenerator
     */
    private $captchaGenerator;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var \Redis
     */
    private $redis;

    protected function setUp()
    {
        $this->captchaGenerator = \Phake::mock(CaptchaGenerator::class);

        $this->session = \Phake::mock(SessionInterface::class);

        $this->redis = \Phake::mock(\Redis::class);

        $this->captchaService = new CaptchaLoginService(
            $this->maxAttempts,
            $this->attemptsPeriod,
            $this->captchaPeriod,
            [],
            $this->captchaGenerator,
            $this->session,
            $this->redis
        );
    }

    public function testIsCaptchaRequiredNotRequired()
    {
        $ip = '123';
        $request = \Phake::mock(Request::class);
        \Phake::when($request)->getClientIp()->thenReturn($ip);

        \Phake::when($this->redis)->hGetAll($ip)->thenReturn([]);

        $this->assertFalse($this->captchaService->isCaptchaRequired($request));
    }

    public function testIsCaptchaRequiredNotRequiredByCaptchaKey()
    {
        $ip = '123';
        $request = \Phake::mock(Request::class);
        \Phake::when($request)->getClientIp()->thenReturn($ip);

        $time = time() - $this->captchaPeriod - 100;

        \Phake::when($this->redis)->hGet($ip, CaptchaLoginService::CAPTCHA_KEY)->thenReturn($time);
        \Phake::when($this->redis)->hGetAll($ip)->thenReturn([]);

        $this->assertFalse($this->captchaService->isCaptchaRequired($request));
    }

    public function testIsCaptchaRequiredNotRequiredByAttempts()
    {
        $ip = '123';
        $request = \Phake::mock(Request::class);
        \Phake::when($request)->getClientIp()->thenReturn($ip);

        $time = time() - $this->attemptsPeriod - 100;

        \Phake::when($this->redis)->hGetAll($ip)->thenReturn(
            array(
                $time => $time
            )
        );

        $this->assertFalse($this->captchaService->isCaptchaRequired($request));
    }

    public function testIsCaptchaRequiredByCaptchaKey()
    {
        $ip = '123';
        $request = \Phake::mock(Request::class);
        \Phake::when($request)->getClientIp()->thenReturn($ip);

        $time = time() - $this->captchaPeriod + 100;

        \Phake::when($this->redis)->hGet($ip, CaptchaLoginService::CAPTCHA_KEY)->thenReturn($time);

        $this->assertTrue($this->captchaService->isCaptchaRequired($request));
    }

    public function testIsCaptchaRequiredByAttempts()
    {
        $ip = '123';
        $request = \Phake::mock(Request::class);
        \Phake::when($request)->getClientIp()->thenReturn($ip);

        $time = time() - $this->attemptsPeriod + 100;

        \Phake::when($this->redis)->hGetAll($ip)->thenReturn(
            array(
                $time => $time
            )
        );

        $this->assertTrue($this->captchaService->isCaptchaRequired($request));
    }

}