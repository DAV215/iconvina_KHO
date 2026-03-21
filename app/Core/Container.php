<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use InvalidArgumentException;
use ReflectionClass;

final class Container
{
    private array $bindings = [];

    private array $instances = [];

    public function singleton(string $id, Closure $resolver): void
    {
        $this->bindings[$id] = $resolver;
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (array_key_exists($id, $this->bindings)) {
            return $this->instances[$id] = $this->bindings[$id]($this);
        }

        if (!class_exists($id)) {
            throw new InvalidArgumentException("Container cannot resolve [$id].");
        }

        $reflection = new ReflectionClass($id);
        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $id();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type === null || $type->isBuiltin()) {
                throw new InvalidArgumentException("Unresolvable dependency [{$parameter->getName()}] in [$id].");
            }

            $arguments[] = $this->get($type->getName());
        }

        return $reflection->newInstanceArgs($arguments);
    }
}
