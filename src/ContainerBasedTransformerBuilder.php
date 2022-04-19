<?php

declare(strict_types=1);

namespace Esse\Metamorphosis;

use Psr\Container\ContainerInterface;

use function sprintf;

/**
 * This class is an implementation of TransformerBuilderInterface.
 * It relies on a psr compliant container to build the transformers.
 */
class ContainerBasedTransformerBuilder implements TransformerBuilderInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function build(string $transformerClassName): TransformerInterface
    {
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
