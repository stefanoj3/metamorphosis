<?php

declare(strict_types=1);

namespace Esse\Metamorphosis;

use InvalidArgumentException;
use Throwable;

use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function sprintf;

class RootTransformer
{
    private TransformerBuilderInterface $resolver;

    /**
     * @var string[]
     */
    private array $entityToTransformerMapping;

    /**
     * @param TransformerBuilderInterface $resolver
     * @param string[] $entityToTransformerMapping
     */
    public function __construct(TransformerBuilderInterface $resolver, array $entityToTransformerMapping)
    {
        $this->resolver = $resolver;
        $this->entityToTransformerMapping = $entityToTransformerMapping;
    }

    /**
     * @psalm-suppress DocblockTypeContradiction
     *
     * @param array|object $data
     *
     * @return mixed
     *
     * @throws Throwable
     */
    public function transform($data)
    {
        if (is_array($data)) {
            /** @var mixed[] $result */
            $result = [];

            /** @var array|object $row */
            foreach ($data as $row) {
                /** @var mixed */
                $result[] = $this->transform($row);
            }

            return $result;
        }

        if (!is_object($data)) {
            throw new InvalidArgumentException(
                sprintf('only array and object types are supported, received `%s`', gettype($data))
            );
        }

        $transformer = $this->getTransformer(get_class($data));

        return $transformer->transform($data, [$this, 'transform']);
    }

    /**
     * @param string $entityClassName
     *
     * @return TransformerInterface
     *
     * @throws InvalidArgumentException
     */
    private function getTransformer(string $entityClassName): TransformerInterface
    {
        $transformerClassName = $this->entityToTransformerMapping[$entityClassName] ?? null;

        if (!$transformerClassName) {
            throw new InvalidArgumentException(
                sprintf('unable to resolve transformer for `%s`, none specified', $entityClassName)
            );
        }

        return $this->resolver->build($transformerClassName);
    }
}
