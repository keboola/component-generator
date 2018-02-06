<?php

declare(strict_types=1);

namespace Keboola\MyComponent;

use Keboola\MyComponent\Config\ConfigDefinition;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class Config
{
    /** @var array */
    private $config;

    public function __construct(string $configFilePath)
    {
        $contents = file_get_contents($configFilePath);
        $decoder = new JsonDecode(true);
        $config = $decoder->decode($contents, JsonEncoder::FORMAT);
        $definition = new ConfigDefinition();
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($definition, [$config['parameters']]);
        $this->config = $processedConfig;
    }

    public function getData(): array
    {
        return $this->config;
    }

    public function getFoo(): string
    {
        return $this->config['foo'];
    }
}
