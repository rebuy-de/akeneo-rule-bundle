<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rebuy\Bundle\RuleBundle\Copier\FallbackPropertyCopier;
use Rebuy\Bundle\RuleBundle\Event\RebuyCopyEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FallbackPropertyCopierTest extends TestCase
{
    const FROM_FIELD = 'from_field';
    const TO_FIELD = 'to_field';
    const SECOND_FROM_FIELD = 'second_from_field';
    const OPTIONS = [
        'from_locale' => null,
        'from_scope' => null,
        'fallback_attributes' => [self::SECOND_FROM_FIELD],
    ];

    /**
     * @var IdentifiableObjectRepositoryInterface|ObjectProphecy
     */
    private $objectRepository;

    /**
     * @var CopierRegistryInterface|ObjectProphecy
     */
    private $copierRegistry;

    /**
     * @var FallbackPropertyCopier
     */
    private $fallbackPropertyCopier;

    /**
     * @var ObjectProphecy|EventDispatcherInterface
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->objectRepository = $this->prophesize(IdentifiableObjectRepositoryInterface::class);
        $this->copierRegistry = $this->prophesize(CopierRegistryInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->fallbackPropertyCopier = new FallbackPropertyCopier(
            $this->objectRepository->reveal(),
            $this->copierRegistry->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    /**
     * @test
     */
    public function copyData_returns_first_from_attribute_with_value()
    {
        // valid to attribute and entity
        [$toAttribute, $toEntityWithValues] = $this->prophesizeToAttributeAndEntity();

        // valid first from attribute and entity
        $fromAttribute = $this->prophesizeFindOneByIdentifier(self::FROM_FIELD, true);
        $fromValue = $this->prophesizeValue(true);
        $fromEntityWithValues = $this->prophesizeGetValue(self::FROM_FIELD, $fromValue);

        $copier = $this->prophesizeGetCopier();

        $this->fallbackPropertyCopier->copyData(
            $fromEntityWithValues->reveal(),
            $toEntityWithValues->reveal(),
            self::FROM_FIELD,
            self::TO_FIELD,
            self::OPTIONS
        );

        $copier->copyAttributeData(
            $fromEntityWithValues,
            Argument::type(EntityWithValuesInterface::class),
            $fromAttribute,
            $toAttribute,
            self::OPTIONS
        )
            ->shouldBeCalledOnce();
    }

    /**
     * @test
     */
    public function copyData_skips_first_empty_from_attribute()
    {
        // valid to attribute and entity
        [$toAttribute, $toEntityWithValues] = $this->prophesizeToAttributeAndEntity();

        // invalid first from attribute without any value
        $this->prophesizeFindOneByIdentifier(self::FROM_FIELD, true);
        $fromEntityWithValues = $this->prophesizeGetValue(self::FROM_FIELD, null);

        // valid second from attribute with a value
        $fromValue = $this->prophesizeValue(true);
        $fromAttribute = $this->prophesizeFindOneByIdentifier(self::SECOND_FROM_FIELD, true);
        $fromEntityWithValues->getValue(self::SECOND_FROM_FIELD, self::OPTIONS['from_locale'], self::OPTIONS['from_scope'])
            ->shouldBeCalledOnce()
            ->willReturn($fromValue->reveal());

        $copier = $this->prophesizeGetCopier();

        $this->fallbackPropertyCopier->copyData(
            $fromEntityWithValues->reveal(),
            $toEntityWithValues->reveal(),
            self::FROM_FIELD,
            self::TO_FIELD,
            self::OPTIONS
        );

        $copier->copyAttributeData(
            $fromEntityWithValues,
            Argument::type(EntityWithValuesInterface::class),
            $fromAttribute,
            $toAttribute,
            self::OPTIONS
        )
            ->shouldBeCalledOnce();
    }

    /**
     * @test
     */
    public function copyData_returns_last_found_from_attribute_without_value()
    {
        // valid to attribute and entity
        [$toAttribute, $toEntityWithValues] = $this->prophesizeToAttributeAndEntity();

        // first from attribute without any value
        $this->prophesizeFindOneByIdentifier(self::FROM_FIELD, false);
        $fromEntityWithValues = $this->prophesizeGetValue(self::FROM_FIELD, null);

        // second from attribute without any value, but the one we expect to use
        $fromAttribute = $this->prophesizeFindOneByIdentifier(self::SECOND_FROM_FIELD, true);
        $fromEntityWithValues->getValue(self::SECOND_FROM_FIELD, self::OPTIONS['from_locale'], self::OPTIONS['from_scope'])
            ->shouldBeCalledOnce()
            ->willReturn(null);

        $copier = $this->prophesizeGetCopier();

        $this->fallbackPropertyCopier->copyData(
            $fromEntityWithValues->reveal(),
            $toEntityWithValues->reveal(),
            self::FROM_FIELD,
            self::TO_FIELD,
            self::OPTIONS
        );

        $copier->copyAttributeData(
            $fromEntityWithValues,
            Argument::type(EntityWithValuesInterface::class),
            $fromAttribute,
            $toAttribute,
            self::OPTIONS
        )
            ->shouldBeCalledOnce();
    }

    /**
     * @test
     */
    public function copyData_dispatches_error_event_and_rethrows_exception()
    {
        $this->objectRepository
            ->findOneByIdentifier(Argument::any())
            ->willThrow(new Exception());

        $entity = $this->prophesize(EntityWithValuesInterface::class);

        $this->expectException(Exception::class);

        $this->prophesizeGetCopier();

        $this->fallbackPropertyCopier->copyData(
            $entity->reveal(),
            $entity->reveal(),
            self::FROM_FIELD,
            self::TO_FIELD,
            self::OPTIONS
        );

        $this->eventDispatcher
            ->dispatch(Argument::type(RebuyCopyEvent::class), RebuyCopyEvent::ERROR_EVENT)
            ->shouldBeCalledOnce();
    }

    /**
     * @test
     */
    public function copyData_logs_error_when_no_from_attribute_exists()
    {
        // valid to attribute and entity
        [$toAttribute, $toEntityWithValues] = $this->prophesizeToAttributeAndEntity();

        // invalid first from attribute that does not exist
        $this->prophesizeFindOneByIdentifier(self::FROM_FIELD, false);
        $fromEntityWithValues = $this->prophesizeGetValue(self::FROM_FIELD, null);

        // invalid second from attribute that does not exist
        $fromAttribute = $this->prophesizeFindOneByIdentifier(self::SECOND_FROM_FIELD, false);
        $fromEntityWithValues->getValue(self::SECOND_FROM_FIELD, self::OPTIONS['from_locale'], self::OPTIONS['from_scope'])
            ->shouldBeCalledOnce()
            ->willReturn(null);

        $copier = $this->prophesizeGetCopier();

        $this->fallbackPropertyCopier->copyData(
            $fromEntityWithValues->reveal(),
            $toEntityWithValues->reveal(),
            self::FROM_FIELD,
            self::TO_FIELD,
            self::OPTIONS
        );

        $copier->copyAttributeData(
            $fromEntityWithValues,
            Argument::type(EntityWithValuesInterface::class),
            $fromAttribute,
            $toAttribute,
            self::OPTIONS
        )
            ->shouldNotBeCalled();
    }

    /**
     * @return null|AttributeInterface|ObjectProphecy $toAttribute
     */
    private function prophesizeFindOneByIdentifier(string $field, bool $returnAttribute)
    {
        $attribute = $returnAttribute ? $this->prophesize(AttributeInterface::class) : null;

        $this->objectRepository
            ->findOneByIdentifier($field)
            ->willReturn($returnAttribute ? $attribute->reveal() : $attribute);

        return $attribute;
    }

    /**
     * @return ValueInterface|ObjectProphecy $toAttribute
     */
    private function prophesizeValue(bool $hasData)
    {
        $fromValue = $this->prophesize(ValueInterface::class);
        $fromValue->hasData()
            ->shouldBeCalledOnce()
            ->willReturn($hasData);

        return $fromValue;
    }

    /**
     * @return EntityWithValuesInterface|ObjectProphecy
     */
    private function prophesizeGetValue(string $fromField, ?ObjectProphecy $fromValue)
    {
        $fromValue = (null !== $fromValue) ? $fromValue->reveal() : null;

        $entity = $this->prophesize(EntityWithValuesInterface::class);
        $entity->getValue($fromField, self::OPTIONS['from_locale'], self::OPTIONS['from_scope'])
            ->shouldBeCalledOnce()
            ->willReturn($fromValue);

        return $entity;
    }

    /**
     * @return AttributeCopierInterface|ObjectProphecy
     */
    private function prophesizeGetCopier()
    {
        $copier = $this->prophesize(AttributeCopierInterface::class);

        $this->copierRegistry
            ->getCopier(Argument::any(), Argument::any())
            ->willReturn($copier->reveal());

        return $copier;
    }

    /**
     * @return array
     */
    private function prophesizeToAttributeAndEntity(): array
    {
        $toAttribute = $this->prophesizeFindOneByIdentifier(self::TO_FIELD, true);
        $toEntityWithValues = $this->prophesize(EntityWithValuesInterface::class);

        return [$toAttribute, $toEntityWithValues];
    }
}
