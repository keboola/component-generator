<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor\Tests\Config;

use Keboola\HttpExtractor\Config\ConfigDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigDefinitionTest extends TestCase
{
    /**
     * @dataProvider provideValidConfigs
     */
    public function testValidConfigDefinition(array $inputConfig, array $expectedConfig): void
    {
        $definition = new ConfigDefinition();
        $processor = new Processor();

        $processedConfig = $processor->processConfiguration($definition, [$inputConfig]);

        $this->assertSame($expectedConfig, $processedConfig);
    }

    /**
     * @return mixed[][]
     */
    public function provideValidConfigs(): array
    {
        return [
            'minimal config' => [
                [
                    'baseUrl' => 'http://www.google.com',
                    'path' => 'path',
                ],
                [
                    'baseUrl' => 'http://www.google.com',
                    'path' => 'path',
                    'saveAs' => null,
                ],
            ],
            'minimal config with saveAs' => [
                [
                    'baseUrl' => 'http://www.google.com',
                    'path' => 'path',
                    'saveAs' => 'newFilename',
                ],
                [
                    'baseUrl' => 'http://www.google.com',
                    'path' => 'path',
                    'saveAs' => 'newFilename',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideInvalidConfigs
     */
    public function testInvalidConfigDefinition(
        array $inputConfig,
        string $expectedExceptionClass,
        string $expectedExceptionMessage
    ): void {
        $definition = new ConfigDefinition();
        $processor = new Processor();

        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $processor->processConfiguration($definition, [$inputConfig]);
    }

    /**
     * @return mixed[][]
     */
    public function provideInvalidConfigs(): array
    {
        return [
            'empty parameters' => [
                [],
                InvalidConfigurationException::class,
                'The child node "baseUrl" at path "parameters" must be configured.',
            ],
            'missing url base' => [
                [
                    'path' => 'path',
                ],
                InvalidConfigurationException::class,
                'The child node "baseUrl" at path "parameters" must be configured.',
            ],
            'missing url path' => [
                [
                    'baseUrl' => 'path',
                ],
                InvalidConfigurationException::class,
                'The child node "path" at path "parameters" must be configured.',
            ],
            'unknown option' => [
                [
                    'baseUrl' => 'http://www.google.com',
                    'path' => 'path',
                    'other' => false,
                ],
                InvalidConfigurationException::class,
                'Unrecognized option "other" under "parameters"',
            ],
        ];
    }
}
