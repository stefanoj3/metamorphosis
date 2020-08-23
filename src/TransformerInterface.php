<?php

declare(strict_types=1);

namespace Esse\Metamorphosis;

interface TransformerInterface
{
    /**
     * @param mixed $data
     * @param callable $transformer
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function transform($data, callable $transformer);
}
