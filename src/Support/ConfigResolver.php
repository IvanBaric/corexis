<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Exceptions\InvalidConfiguration;
use ReflectionClass;

/**
 * Resolves infrastructure values from package configuration.
 */
final readonly class ConfigResolver
{
    public function __construct(
        private Repository $config,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $default
     * @param  class-string<TModel>  $expectedType
     * @return class-string<TModel>
     */
    public function model(
        string $key,
        string $default,
        string $expectedType = Model::class,
    ): string {
        return $this->implementation(
            key: $key,
            default: $default,
            expectedType: $expectedType,
        );
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $default
     * @param  class-string<T>  $expectedType
     * @return class-string<T>
     */
    public function implementation(
        string $key,
        string $default,
        string $expectedType,
    ): string {
        $configured = $this->config->get($key);
        $class = $configured === null ? $default : $configured;

        if (
            ! is_string($class)
            || ! class_exists($class)
            || ! is_a($class, $expectedType, true)
            || ! (new ReflectionClass($class))->isInstantiable()
        ) {
            throw InvalidConfiguration::invalidClass(
                key: $key,
                value: $configured,
                expectedType: $expectedType,
            );
        }

        return $class;
    }

    public function table(string $key, string $default): string
    {
        $configured = $this->config->get($key);
        $table = $configured === null ? $default : $configured;

        if (
            ! is_string($table)
            || trim($table) === ''
            || preg_match(
                '/\A[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)?\z/',
                $table,
            ) !== 1
        ) {
            throw InvalidConfiguration::invalidTable(
                key: $key,
                value: $configured,
            );
        }

        return $table;
    }
}
