<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Spryng\Tests;

use Symfony\Component\Notifier\Bridge\Spryng\SpryngTransportFactory;
use Symfony\Component\Notifier\Test\TransportFactoryTestCase;

final class SpryngTransportFactoryTest extends TransportFactoryTestCase
{
    public function createFactory(): SpryngTransportFactory
    {
        return new SpryngTransportFactory();
    }

    public static function createProvider(): iterable
    {
        yield [
            'spryng://host.test?sender=0611223344&route=1234',
            'spryng://apiKey@host.test?sender=0611223344&route=1234',
        ];
    }

    public static function supportsProvider(): iterable
    {
        yield [true, 'spryng://apiKey@default?sender=0611223344'];
        yield [false, 'somethingElse://apiKey@default?sender=0611223344'];
    }

    public static function incompleteDsnProvider(): iterable
    {
        yield 'missing api_key' => ['spryng://default?sender=0611223344'];
    }

    public static function missingRequiredOptionProvider(): iterable
    {
        yield 'missing option: sender' => ['spryng://apiKey@host.test'];
    }

    public static function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://apiKey@default?sender=0611223344'];
        yield ['somethingElse://apiKey@host']; // missing "sender" option
    }
}
