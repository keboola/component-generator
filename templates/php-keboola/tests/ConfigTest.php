<?php

declare(strict_types=1);

namespace Keboola\MyComponent\Tests;

use Keboola\MyComponent\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testCreateFromFile(): void
    {
        $configFilePath = __DIR__ . '/fixtures/config.json';
        $config = new Config($configFilePath);
        self::assertSame(
            [
                'foo' => 'bar',
            ],
            $config->getData()
        );
    }

    public function testCustomGetters(): void
    {
        $configFilePath = __DIR__ . '/fixtures/config.json';
        $config = new Config($configFilePath);
        self::assertSame('bar', $config->getFoo());
    }
}
