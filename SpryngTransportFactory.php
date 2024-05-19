<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Spryng;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;

/**
 * @author Paul Rijke
 */
final class SpryngTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): SpryngTransport
    {
        $scheme = $dsn->getScheme();

        if ('spryng' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'spryng', $this->getSupportedSchemes());
        }

        $apiKey = $this->getUser($dsn);
        $sender = $dsn->getRequiredOption('sender');
        $route = $dsn->getOption('route') ?? 'business';
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new SpryngTransport($apiKey, $sender, $route, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['spryng'];
    }
}
