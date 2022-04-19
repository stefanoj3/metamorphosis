<?php

declare(strict_types=1);

namespace Esse\Metamorphosis;

interface TransformerBuilderInterface
{
    /**
     * This method takes in input the name of the transformer we wish to build,
     * and returns it if possible, or throws an exception.
     *
     * @param string $transformerClassName
     *
     * @return TransformerInterface
     *
     * @throws \Throwable
     */
    public function build(string $transformerClassName): TransformerInterface;
}
