<?php

declare(strict_types=1);

namespace Esse\Metamorphosis;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

use function get_class;

class RootTransformerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function shouldTransform()
    {
        $myEntity = new class () {
            public string $name = 'myname';
        };

        $myEntityClassName = get_class($myEntity);

        $data = [
            $myEntity,
            $myEntity,
            $myEntity,
        ];

        $transformer = new class () implements TransformerInterface {

            public function transform($data, callable $transformer)
            {
                return ['name' => $data->name];
            }
        };

        $transformerClassName = get_class($transformer);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get($transformerClassName)->willReturn($transformer);
        $container->has($transformerClassName)->willReturn(true);

        $rootTransformer = new RootTransformer(
            $container->reveal(),
            [
                $myEntityClassName => $transformerClassName,
            ]
        );

        $result = $rootTransformer->transform($data);

        $expectedResult = [
            [
                'name' => 'myname',
            ],
            [
                'name' => 'myname',
            ],
            [
                'name' => 'myname',
            ],
        ];

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function shouldThrowWhenNoTransformerIsSpecifiedForThegivenClass()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $rootTransformer = new RootTransformer($container->reveal(), []);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/unable to resolve transformer for/');
        $rootTransformer->transform(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldThrowWhenTheTransformerIsNotAvailableInTheContainer()
    {
        $myTransformerClassName = 'bla';

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($myTransformerClassName)->willReturn(false);

        $rootTransformer = new RootTransformer($container->reveal(), [\stdClass::class => $myTransformerClassName]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is not available in the container/');
        $rootTransformer->transform(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldThrowWhenTheTransformerIsNotImplementingTheTransformerInterface()
    {
        $invalidTransformer = new class () {

        };

        $myTransformerClassName = get_class($invalidTransformer);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($myTransformerClassName)->willReturn(true);
        $container->get($myTransformerClassName)->willReturn($invalidTransformer);

        $rootTransformer = new RootTransformer($container->reveal(), [\stdClass::class => $myTransformerClassName]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/does not implement/');
        $rootTransformer->transform(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldThrowWhenDataIsNotArrayOrObject()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $rootTransformer = new RootTransformer($container->reveal(), []);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/only array and object types are supported/');
        $rootTransformer->transform('invalid data - the transformer accepts only arrays and objects');
    }
}
