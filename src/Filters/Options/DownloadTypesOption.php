<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use Eclipxe\Enum\Enum;
use LogicException;
use PhpCfdi\CfdiSatScraper\URLS;

/**
 * @method static self recibidos()
 * @method static self emitidos()
 *
 * @method bool isEmitidos()
 * @method bool isRecibidos()
 */
class DownloadTypesOption extends Enum
{
    private const URLS = [
        'recibidos' => URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR,
        'emitidos' => URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR,
    ];

    public function url(): string
    {
        $url = self::URLS[$this->value()] ?? '';
        if ('' === $url) {
            throw new LogicException(sprintf('Class %s does not have the url for "%s"', static::class, $this->value()));
        }
        return $url;
    }
}
