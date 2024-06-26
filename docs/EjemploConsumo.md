# Ejemplo de consumo

Este ejemplo está documentado con las siguientes consideraciones:

- El RFC está en el entorno en la variable `SAT_AUTH_RFC`
- La clave CIEC está en el entorno en la variable `SAT_AUTH_CIEC`
- Desde donde se está llamando al código existen las carpetas `build/cookies/` y `build/cfdis/`
- Se está usando el objeto `ConsoleCaptchaResolver`, así que se espera que si se le solicita un captcha lo
  resuelva y escriba su contenido. El captcha está en el archivo `captcha.png`.

Y se espera que:

- Se pueda reutilizar la `cookie` si no ha expirado y así no tener que volver a resolver un captcha.
- Se carge una lista de CFDI recibidos y vigentes entre 2019-12-01 y 2019-12-31.
- Ocurra la descarga de los XML correspondientes a dichos registros.

La rutina de descarga intentará hasta que haya descargado todos los archivos.

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\Resolvers\ConsoleResolver;

$rfc = strval($_SERVER['SAT_AUTH_RFC'] ?? '');
$claveCiec = strval($_SERVER['SAT_AUTH_CIEC'] ?? '');
$cookieJarPath = sprintf('%s/build/cookies/%s.json', getcwd(), $rfc);
$downloadsPath = sprintf('%s/build/cfdis/%s', getcwd(), $rfc);

$gateway = new SatHttpGateway(new Client(), new FileCookieJar($cookieJarPath, true));
$captchaResolver = new ConsoleResolver();

$ciecSessionManager = CiecSessionManager::create($rfc, $claveCiec, $captchaResolver);

$satScraper = new SatScraper($ciecSessionManager, $gateway);

$resourceDownloader = $satScraper->resourceDownloader(ResourceType::xml())
    ->setConcurrency(20);

$query = new QueryByFilters(new DateTimeImmutable('2019-12-01'), new DateTimeImmutable('2019-12-31'));
$query->setDownloadType(DownloadType::recibidos()) // default: emitidos
    ->setStateVoucher(StatesVoucherOption::vigentes());   // default: todos

$list = $satScraper->listByPeriod($query);
printf("\nSe encontraron %d registros", $list->count());

$list = $list->filterWithResourceLink(ResourceType::xml());
printf("\nPero solamente %d registros contienen archivos XML", $list->count());

while ($list->count() > 0) {
    // perform download
    printf("\nIntentando descargar %d archivos: ", $list->count());
    $downloadedUuids = $resourceDownloader->setMetadataList($list)
        ->saveTo($downloadsPath, true);
    printf('%d descargados.', count($downloadedUuids));

    // check that at least one uuid were downloaded
    if ([] === $downloadedUuids) {
        printf("\nNo se pudieron descargar %d registros", $list->count());
        break; // exit loop since no records were downloaded
    }
    
    // reduce list
    $list = $list->filterWithOutUuids($downloadedUuids);
}
```
