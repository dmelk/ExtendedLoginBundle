# MelkExtendedLoginBundle

## About

This bundle allows you to use extended login form with captcha when several 
sequenced failed login attempts received from one IP address.
 
## Installation

### Step 1

Simply install it via composer:

`composer require melk/extended-login-bundle`

### Step 2

Enable bundle and it's dependencies in the `AppKernel`:

```
new Snc\RedisBundle\SncRedisBundle(),
new Gregwar\CaptchaBundle\GregwarCaptchaBundle(),
new Melk\ExtendedLoginBundle\MelkExtendedLoginBundle(),
```

## Configuration

### Gregwar captcha

Gregwar captcha bundle configuration can be found [here](https://github.com/Gregwar/CaptchaBundle)

### Snc redis

General configuration info for snc redis bundle can be found [here](https://github.com/snc/SncRedisBundle)

Extened login bundle requires snc client configuration, for example:

```
#app/config/config.yml
snc_redis:
    clients:
        captcha_login:
            type: phpredis
            alias: captcha_login
            dsn: redis://redis/4
```

### Extended login bundle

You can use it without any additional configuration. List of all options with 
default values:

```
#app/config/config.yml
melk_extended_login:
    client: captcha_login
    max_attempts: 5
    attempts_period: 300 (5 minutes in seconds)
    captcha_period: 1800 (30 minutes in seconds)
```

## Usage

### Enabling form

Simply change your firewall settings changing form_login to melk_form_login. No 
additional configuration required:

```
#app/config/security.yml
secured_area:
    melk_form_login:
        captcha_parameter: _captcha
```

All other options inherited from the basic Symfony form_login

### Accessing captcha info

Bundle provides you `melk_extended_login.service.captcha_login_service`
which allows to check if captcha required and get captcha image url:

```
$captchaLoginService = $container->get('melk_extended_login.service.captcha_login_service');

$captchaLoginService->isCaptchaRequired($request),
$captchaLoginService->getCaptchaUrl()
```

### Authentication Failure Handler

Bundle provides own authentication failure handler, which will return `JsonResponse` with next
keys: `success`, `message` and optional `captcha` (captcha url if captcha required) in case
when request sent via AJAX (it is  `XmlHttpRequest`). In all other cases this handler acts as
default form login authentication failure handler.