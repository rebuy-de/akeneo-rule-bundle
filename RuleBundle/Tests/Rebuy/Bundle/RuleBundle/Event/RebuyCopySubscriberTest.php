<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\Event;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rebuy\Bundle\RuleBundle\Event\RebuyCopyEvent;
use Rebuy\Bundle\RuleBundle\Event\RebuyCopySubscriber;
use Rebuy\Bundle\RuleBundle\Exception\AttributeNotFoundException;

class RebuyCopySubscriberTest extends TestCase
{
    /**
     * @var RebuyCopySubscriber
     */
    private $mappingErrorSubscriber;

    /**
     * @var EntityWithValuesBuilderInterface|ObjectProphecy
     */
    private $entityWithValuesBuilder;

    /**
     * @var IdentifiableObjectRepositoryInterface|ObjectProphecy
     */
    private $repository;

    protected function setUp(): void
    {
        $this->entityWithValuesBuilder = $this->prophesize(EntityWithValuesBuilderInterface::class);
        $this->repository = $this->prophesize(IdentifiableObjectRepositoryInterface::class);

        $this->mappingErrorSubscriber = new RebuyCopySubscriber(
            $this->entityWithValuesBuilder->reveal(),
            $this->repository->reveal()
        );
    }

    /**
     * @test
     */
    public function getSubscribedEvents_subscribes_to_the_desired_events()
    {
        $actual = RebuyCopySubscriber::getSubscribedEvents();

        self::assertArrayHasKey(RebuyCopyEvent::ERROR_EVENT, $actual);
    }

    /**
     * @test
     */
    public function onMappableCopierEvent_logs_exceptions()
    {
        $event = $this->setupProphecies();

        $event->isRuleBundleException()->willReturn(true);

        $this->mappingErrorSubscriber->onCopyError($event->reveal());

        $this->repository->findOneByIdentifier(Argument::any())->shouldHaveBeenCalledOnce();
        $this->entityWithValuesBuilder->addOrReplaceValue(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function onMappableCopierEvent_throws_exception_when_mapping_attribute_does_not_exist()
    {
        $event = $this->setupProphecies(false);

        $event->isRuleBundleException()->willReturn(true);

        $this->expectException(AttributeNotFoundException::class);

        $this->mappingErrorSubscriber->onCopyError($event->reveal());

        $this->entityWithValuesBuilder->addOrReplaceValue(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->shouldHaveBeenCalledOnce();
    }

    /**
     * @return ObjectProphecy|RebuyCopyEvent
     */
    private function setupProphecies($mappingAttributeExists = true)
    {
        $event = $this->prophesizeMappableCopierEvent();
        $attribute = $this->prophesize(AttributeInterface::class);
        $this->repository
            ->findOneByIdentifier('')
            ->willReturn($mappingAttributeExists ? $attribute->reveal() : null);

        $toValue = $this->prophesize(ValueInterface::class);
        $toValue->__toString()->willReturn('');

        $toEntity = $this->prophesize(EntityWithValuesInterface::class);
        $event->getToEntity()->willReturn($toEntity);

        return $event;
    }

    /**
     * @return ObjectProphecy|RebuyCopyEvent
     */
    private function prophesizeMappableCopierEvent()
    {
        $event = $this->prophesize(RebuyCopyEvent::class);
        $event->hasException()->willReturn(true);

        $fromValue = $this->prophesize(ValueInterface::class);
        $fromValue->__toString()->willReturn('');
        $event->getFromValue()->willReturn($fromValue);

        $event->getFromCode()->willReturn('');
        $event->fromLocale()->willReturn('');
        $event->fromScope()->willReturn('');
        $event->getToCode()->willReturn('');
        $event->toLocale()->willReturn('');
        $event->toScope()->willReturn('');
        $event->getException()->willReturn(new Exception());
        $event->getMappingReportAttribute()->willReturn('');

        return $event;
    }
}
