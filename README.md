# Melhor Envio SDK - Integração com Melhor Envio

[![Latest Version on Packagist](https://img.shields.io/packagist/v/melhorenvio/shipment-sdk-php.svg?style=flat-square)](https://packagist.org/packages/melhorenvio/shipment-sdk-php)
[![Build Status](https://img.shields.io/travis/melhorenvio/shipment-sdk-php/master.svg?style=flat-square)](https://travis-ci.org/melhorenvio/shipment-sdk-php)
[![Quality Score](https://img.shields.io/scrutinizer/g/melhorenvio/shipment-sdk-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/melhorenvio/shipment-sdk-php)
[![Total Downloads](https://img.shields.io/packagist/dt/melhorenvio/shipment-sdk-php.svg?style=flat-square)](https://packagist.org/packages/melhorenvio/shipment-sdk-php)

Agora ficou mais fácil ter o serviço do Melhor Envio no seu projeto de e-commerce.

## Índice

* [Instalação](#instalacao)
* [Cofiguração Inicial](##configuração-inicial)
* [Exemplos de uso](##Criando-a-instância-do-Melhor-Envio)
* [Mais exemplos](##Mais-Exemplos)
* [Testes](##Testes)
* [Changelog](##Changelog)
* [Contribuindo](##Contribuindo)
* [Segurança](##Segurança)
* [Créditos](##Créditos)
* [Licença](##Licença)

### require 
* PHP >= 5.6
* Ext-json = *
* Guzzlehttp/guzzle >= 6.5

### require-dev
* phpunit/phpunit >= 5


## Instalação

Você pode instalar o pacote via composer, rodando o seguinte comando:

```bash
composer require melhorenvio/melhor-envio-sdk-php
```

## Configuração inicial
### Obtendo link de autorização de conta do Melhor Envio
```php
require "./vendor/autoload.php";

use MelhorEnvio\MelhorEnvioSdkPhp\Event;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\Resources\Shipment\Product;

$provider = new OAuth2(
    $appData['client_id'],
    $appData['client_secret'],
    $appData['redirect_uri']
);

$provider->setScopes('shipping-calculate');
$linkAuthorize = $provider->getAuthorizationUrl();

echo $linkAuthorize;
```
### Obtendo Access Token e Refresh Token
```php
require "./vendor/autoload.php";

use MelhorEnvio\MelhorEnvioSdkPhp\Event;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\Resources\Shipment\Product;

$provider = new OAuth2(
    $appData['client_id'],
    $appData['client_secret'],
    $appData['redirect_uri']
);

$code = $_GET['code'];

$tokens = $provider->getAccessToken($code);
var_dump($tokens);
die;
```


```php
require "./vendor/autoload.php";

use MelhorEnvio\Enums\Environment;
use MelhorEnvio\MelhorEnvioSdkPhp\Event;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\Resources\Shipment\Product;

$appData = [
    'client_id' => 2635,
    'client_secret' => 'O9WeVIi7zzCNhqveldS7oEm0YSF5lU6gCilnSkRj',
    'redirect_uri' => 'https://bridge-woocommerce.test/callback'
];

Event::listen('refresh', function ($token, $refreshToken) {
    // Put here trading rule to save accessToken e refreshToken.
});

$shipment = new Shipment(
    $accessToken,
    $refreshToken,
    Environment::SANDBOX,
    $appData['client_id'],
    $appData['client_secret'],
    $appData['redirect_uri']
);

$calculator = $shipment->calculator();

$calculator->postalCode('01010010', '20271130');

$calculator->setOwnHand();
$calculator->setReceipt(false);
$calculator->setCollect(false);

$calculator->addProducts(
    new Product(uniqid(), 40, 30, 50, 10.00, 100.0, 1),
    new Product(uniqid(), 5, 1, 10, 0.1, 50.0, 1)
);

$quotations = $calculator->calculate();

var_dump($quotations);
```

## Criando a instância do Melhor Envio


### Mais exemplos

[Aqui você pode acessar mais exemplos de implementação](/examples)

### Testes

Dentro do projeto você encontrará alguns documentos de teste baseados em testes unitários


Você pode usar na aplicação tanto o comando:
``` bash
composer test
```
Quanto o comando:
```bash
vendor/bin/phpunit tests 
```

### Changelog

Consulte [CHANGELOG](CHANGELOG.md) para mais informações de alterações recentes.

## Contribuindo

Consulte [CONTRIBUTING](CONTRIBUTING.md) para mais detalhes.

### Segurança

Se você descobrir algum problema de segurança, por favor, envie um e-mail para tecnologia@melhorenvio.com, ao invés de usar um *issue tracker*.

## Créditos

- [Vinícius Schlee Tessmann](https://github.com/viniciustessmann)

## Licença

Melhor Envio. Consulte [Arquivo de lincença](LICENSE.md) para mais informações.