<?php

declare(strict_types=1);

namespace Esse\Metamorphosis;

use Psr\Container\ContainerInterface;

use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function sprintf;

class RootTransformer
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var string[]
     */
    private array $entityToTransformerMapping;

    /**
     * @param ContainerInterface $container
     * @param string[] $entityToTransformerMapping
     */
    public function __construct(ContainerInterface $container, array $entityToTransformerMapping)
    {
        $this->container = $container;
        $this->entityToTransformerMapping = $entityToTransformerMapping;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function transform($data)
    {
        if (is_array($data)) {
            /** @var mixed[] $result */
            $result = [];

            /** @var mixed $row */
            foreach ($data as $row) {
                /** @var mixed */
                $result[] = $this->transform($row);
            }

            return $result;
        }

        if (!is_object($data)) {
            throw new \InvalidArgumentException(
                sprintf('only array and object types are supported, received `%s`', gettype($data))
            );
        }

        $transformer = $this->resolveTransformer($data);

        return $transformer->transform($data, [$this, 'transform']);
    }

    private function resolveTransformer(object $data): TransformerInterface
    {
        $dataClassName = get_class($data);

        $transformerClassName = $this->entityToTransformerMapping[$dataClassName] ?? null;

        if (!$transformerClassName) {
            throw new \InvalidArgumentException(
                sprintf('unable to resolve transformer for `%s`, none specified', $dataClassName)
            );
        }

        if (!$this->container->has($transformerClassName)) {
            throw new \InvalidArgumentException(
                sprintf('`%s` is not available in the container', $transformerClassName)
            );
        }

        /** @var object $transformer */
        $transformer = $this->container->get($transformerClassName);

        if (!$transformer instanceof TransformerInterface) {
            throw new \InvalidArgumentException(
                sprintf('`%s` does not implement `%s`', $transformerClassName, TransformerInterface::class)
            );
        }

        return $transformer;
    }
}
