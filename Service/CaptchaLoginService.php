<?php

namespace Melk\ExtendedLoginBundle\Service;

use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * CaptchaLoginService provides logic for adding captcha to the login form, storing failed login attempts.
 *
 * @author    Michael Potienko <potienko.m@gmail.com>
 */
class CaptchaLoginService
{
    /**
     * Redis hash key for captcha show time.
     */
    const CAPTCHA_KEY = 'captcha';

    /**
     * Captcha session key.
     */
    const SESSION_KEY = 'gcb_login_captcha';

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * Maximum failure attempts before captcha will be used.
     *
     * @var int
     */
    private $maxAttempts;

    /**
     * Time period during which attempts will be counted. In seconds.
     *
     * @var int
     */
    private $attemptsPeriod;

    /**
     * Time during which captcha should be used after failed attempts.
     *
     * @var int
     */
    private $captchaPeriod;

    /**
     * Captcha configuration.
     *
     * @var array
     */
    private $captchaConfig;

    /**
     * @var CaptchaGenerator
     */
    private $captchaGenerator;

    /**
     * CaptchaLoginService constructor.
     *
     * @param                    $maxAttempts
     * @param                    $attemptsPeriod
     * @param                    $captchaPeriod
     * @param                    $captchaConfig
     * @param CaptchaGenerator   $captchaGenerator
     * @param SessionInterface   $session
     * @param                    $redis
     * @param ContainerInterface $container
     */
    public function __construct(
        $maxAttempts,
        $attemptsPeriod,
        $captchaPeriod,
        $captchaConfig,
        CaptchaGenerator $captchaGenerator,
        SessionInterface $session,
        $redis,
        ContainerInterface $container)
    {
        $this->maxAttempts = $maxAttempts;
        $this->attemptsPeriod = $attemptsPeriod;
        $this->captchaPeriod = $captchaPeriod;
        $this->captchaConfig = $captchaConfig;
        $this->captchaGenerator = $captchaGenerator;
        $this->session = $session;
        $this->redis = $container->get('snc_redis.'.$redis);
    }

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * Check if captcha required for current login request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isCaptchaRequired(Request $request)
    {
        $ip = $request->getClientIp();

        $currentTimestamp = time();

        $captchaRequired = $this->redis->hGet($ip, self::CAPTCHA_KEY);
        if ($captchaRequired) {
            if ($currentTimestamp - $captchaRequired <= $this->captchaPeriod) {
                return true;
            }
            // captcha show time is over
            $this->redis->hDel($ip, self::CAPTCHA_KEY);
        }

        if ($this->getActualAttemptsAmount($ip) < $this->maxAttempts) {
            return false;
        }

        // set the captcha here
        $this->redis->hSet($ip, self::CAPTCHA_KEY, $currentTimestamp);

        return true;
    }

    /**
     * Add failed request time to the redis.
     *
     * @param Request $request
     */
    public function onFailedRequest(Request $request)
    {
        $ip = $request->getClientIp();
        $currentTimestamp = time();
        $this->redis->hSet($ip, $currentTimestamp, $currentTimestamp);
    }

    /**
     * Generate captcha and return it's url.
     *
     * @return string
     */
    public function getCaptchaUrl()
    {
        $options = array_merge(
            $this->captchaConfig,
            array(
                'as_url' => true,
            )
        );

        $keys = $this->session->get($this->captchaConfig['whitelist_key'], array());
        if (!in_array(self::SESSION_KEY, $keys)) {
            $keys[] = self::SESSION_KEY;
        }
        $this->session->set($this->captchaConfig['whitelist_key'], $keys);
        $options['session_key'] = self::SESSION_KEY;

        return $this->captchaGenerator->getCaptchaCode($options);
    }

    /**
     * @param $code
     *
     * @return bool
     */
    public function validateCaptchaCode($code)
    {
        $options = $this->session->get(self::SESSION_KEY);

        $expectedCode = (is_array($options) && isset($options['phrase'])) ? $options['phrase'] : null;

        $this->session->remove(self::SESSION_KEY);

        if ($this->session->has(self::SESSION_KEY.'_fingerprint')) {
            $this->session->remove(self::SESSION_KEY.'_fingerprint');
        }

        return $code !== null && is_string($code) && $expectedCode !== null && is_string($expectedCode) && $this->niceize($code) == $this->niceize($expectedCode);
    }

    /**
     * Process the codes.
     *
     * @param $code
     *
     * @return string
     */
    protected function niceize($code)
    {
        return strtr(strtolower($code), 'oil', '01l');
    }

    /**
     * Get amount of all actual failed attempts.
     *
     * @param $ip
     *
     * @return int
     */
    protected function getActualAttemptsAmount($ip)
    {
        $currentTimestamp = time();
        // need to check if we had expired attempts
        $allAttempts = $this->redis->hGetAll($ip);
        $actualAttempts = 0;
        foreach ($allAttempts as $key => $value) {
            if ($key == self::CAPTCHA_KEY) {
                continue;
            }
            if ($currentTimestamp - $key > $this->attemptsPeriod) {
                // this attempt is not actual no more
                $this->redis->hDel($ip, $key);
                continue;
            }
            ++$actualAttempts;
        }

        return $actualAttempts;
    }
}
