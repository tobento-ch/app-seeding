<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Seeding\Test;

use ArrayObject;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tobento\App\Seeding\CallbackWrappingFactory;
use Tobento\App\Seeding\FactoryInterface;
use Tobento\App\Seeding\Test\Mock\RecordingFactory;

class CallbackWrappingFactoryTest extends TestCase
{
    protected function makeFactory(ArrayObject $log, null|RecordingFactory $inner = null): CallbackWrappingFactory
    {
        return new CallbackWrappingFactory(
            factory: $inner ?? new RecordingFactory($log),
            before: static function () use ($log): void {
                $log->append('before');
            },
            after: static function () use ($log): void {
                $log->append('after');
            },
        );
    }

    public function testImplementsFactoryInterface()
    {
        $this->assertInstanceOf(
            FactoryInterface::class,
            $this->makeFactory(new ArrayObject())
        );
    }

    public function testNewMethodThrowsLogicException()
    {
        $this->expectException(LogicException::class);

        CallbackWrappingFactory::new();
    }

    public function testDefinitionIsDelegatedWithoutCallbacks()
    {
        $log = new ArrayObject();

        $this->assertSame(['foo' => 'bar'], $this->makeFactory($log)->definition());
        $this->assertSame(['factory.definition'], $log->getArrayCopy());
    }

    public function testTimesReturnsTheWrapperAndForwardsTheAmount()
    {
        $log = new ArrayObject();
        $inner = new RecordingFactory($log);
        $factory = $this->makeFactory($log, $inner);

        $result = $factory->times(3);

        $this->assertSame($factory, $result);
        $this->assertSame(3, $inner->amount);
        $this->assertSame(['factory.times'], $log->getArrayCopy());
    }

    public function testTimesAmountIsUsedWhenCreating()
    {
        $factory = $this->makeFactory(new ArrayObject());

        $this->assertCount(3, $factory->times(3)->create());
    }

    public function testCreateRunsCallbacksAroundTheFactory()
    {
        $log = new ArrayObject();

        $created = $this->makeFactory($log)->create();

        $this->assertCount(1, $created);
        $this->assertSame(['before', 'factory.create', 'after'], $log->getArrayCopy());
    }

    public function testCreateOneRunsCallbacksAroundTheFactory()
    {
        $log = new ArrayObject();

        $created = $this->makeFactory($log)->createOne();

        $this->assertInstanceOf(\stdClass::class, $created);
        $this->assertSame(['before', 'factory.createOne', 'after'], $log->getArrayCopy());
    }

    public function testCreateRunsAfterCallbackIfFactoryThrows()
    {
        $log = new ArrayObject();
        $inner = new RecordingFactory($log);
        $inner->throw = new RuntimeException('Failed');

        try {
            $this->makeFactory($log, $inner)->create();
            $this->fail('Exception expected.');
        } catch (RuntimeException $e) {
            $this->assertSame('Failed', $e->getMessage());
        }

        $this->assertSame(['before', 'factory.create', 'after'], $log->getArrayCopy());
    }

    public function testCreateOneRunsAfterCallbackIfFactoryThrows()
    {
        $log = new ArrayObject();
        $inner = new RecordingFactory($log);
        $inner->throw = new RuntimeException('Failed');

        try {
            $this->makeFactory($log, $inner)->createOne();
            $this->fail('Exception expected.');
        } catch (RuntimeException $e) {
            $this->assertSame('Failed', $e->getMessage());
        }

        $this->assertSame(['before', 'factory.createOne', 'after'], $log->getArrayCopy());
    }

    public function testFactoryIsNotCalledIfBeforeCallbackThrows()
    {
        $log = new ArrayObject();

        $factory = new CallbackWrappingFactory(
            factory: new RecordingFactory($log),
            before: static function (): void {
                throw new RuntimeException('Before failed');
            },
            after: static function () use ($log): void {
                $log->append('after');
            },
        );

        try {
            $factory->create();
            $this->fail('Exception expected.');
        } catch (RuntimeException $e) {
            $this->assertSame('Before failed', $e->getMessage());
        }

        // Neither the factory nor the after callback ran:
        $this->assertSame([], $log->getArrayCopy());
    }

    public function testCallbacksRunOnEveryStoringCall()
    {
        $log = new ArrayObject();
        $factory = $this->makeFactory($log);

        $factory->create();
        $factory->createOne();
        $factory->create();

        $this->assertSame(
            [
                'before', 'factory.create', 'after',
                'before', 'factory.createOne', 'after',
                'before', 'factory.create', 'after',
            ],
            $log->getArrayCopy()
        );
    }

    public function testMakeDoesNotRunCallbacks()
    {
        $log = new ArrayObject();

        $made = $this->makeFactory($log)->make();

        $this->assertCount(1, $made);
        $this->assertSame(['factory.make'], $log->getArrayCopy());
    }

    public function testMakeOneDoesNotRunCallbacks()
    {
        $log = new ArrayObject();

        $this->assertInstanceOf(\stdClass::class, $this->makeFactory($log)->makeOne());
        $this->assertSame(['factory.makeOne'], $log->getArrayCopy());
    }

    public function testRawDoesNotRunCallbacks()
    {
        $log = new ArrayObject();

        $this->assertSame([['foo' => 'bar']], $this->makeFactory($log)->raw());
        $this->assertSame(['factory.raw'], $log->getArrayCopy());
    }

    public function testRawOneDoesNotRunCallbacks()
    {
        $log = new ArrayObject();

        $this->assertSame(['foo' => 'bar'], $this->makeFactory($log)->rawOne());
        $this->assertSame(['factory.rawOne'], $log->getArrayCopy());
    }
}