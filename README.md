# Melhor Envio SDK - Integração com Melhor Envio

[![Latest Version on Packagist](https://img.shields.io/packagist/v/melhorenvio/melhor-envio-sdk-php.svg?style=flat-square)](https://packagist.org/packages/melhorenvio/melhor-envio-sdk-php)
[![Build Status](https://img.shields.io/travis/melhorenvio/melhor-envio-sdk-php/master.svg?style=flat-square)](https://travis-ci.org/melhorenvio/melhor-envio-sdk-php)
[![Quality Score](https://img.shields.io/scrutinizer/g/melhorenvio/melhor-envio-sdk-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/melhorenvio/melhor-envio-sdk-php)
[![Total Downloads](https://img.shields.io/packagist/dt/melhorenvio/melhor-envio-sdk-php.svg?style=flat-square)](https://packagist.org/packages/melhorenvio/melhor-envio-sdk-php)

Agora ficou mais fácil ter o serviço do Melhor Envio no seu projeto de e-commerce.

## Indice

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
* PHP >= 7.4
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
Para maiores informações sobre autenticação, acessar a documentação do Auth SDK:
https://packagist.org/packages/melhorenvio/auth-sdk-php
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

### Realizando cotações
Para maiores informações sobre cotações, acessar a documentação do Shipment SDK:
https://packagist.org/packages/melhorenvio/shipment-sdk-php

```php
require "./vendor/autoload.php";

use MelhorEnvio\Enums\Environment;
use MelhorEnvio\MelhorEnvioSdkPhp\Event;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\Resources\Shipment\Product;

Event::listen('refresh', function ($token, $refreshToken) {
    // Put here trading rule to save accessToken e refreshToken.
});

$oAuth2 = new OAuth2(
    CLIENT_ID,
    TEST_CLIENT_SECRET,
    TEST_REDIRECT_URI
);

$this->shipment = new Shipment(
    $oAuth2,
    ACCESS_TOKEN,
    REFRESH_TOKEN
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

### Recebendo Access Tokens e Refresh Tokens atualizados
O Access Token gerado pelo Melhor Envio tem a validade de 1(um) mês, após esse período é possível atualizar o token de forma automatiza com o refresh token, por isso é necessário sempre manter atulizado os access tokens e refresh tokens, visando isso, o Melhor Envio SDK possui um evento de listerner de receber com os dados de tokens atualizados.  
Você deverá implementar a lógica para persistir esses dados na sua plataforma, veja um exemplo abaixo:
```php
Event::listen('refresh', function ($token, $refreshToken) {
    // Aqui deve ser inserido a sua lógica de persitir as informações na sua plataforma, o código abaixo é apenas um exemplo, o mesmo deve ser subistituido para a sua realidade.
    Credentials::update([
       'access_token' => $token,
       'refresh_token' => $refreshToken 
   ]) 
});
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