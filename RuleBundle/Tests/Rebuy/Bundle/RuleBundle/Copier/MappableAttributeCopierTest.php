<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rebuy\Bundle\RuleBundle\AttributeMapper\AttributeMapperInterface;
use Rebuy\Bundle\RuleBundle\Copier\MappableAttributeCopier;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MappableAttributeCopierTest extends TestCase
{
    /**
     * @var ObjectProphecy|EntityWithValuesBuilderInterface
     */
    private $entityWithValuesBuilder;

    /**
     * @var ObjectProphecy|AttributeValidatorHelper
     */
    private $attributeValidatorHelper;

    /**
     * @var ObjectProphecy|NormalizerInterface
     */
    private $normalizer;

    /**
     * @var MappableAttributeCopier
     */
    private $mappableAttributeCopier;

    /**
     * @var ObjectProphecy|AttributeMapperInterface
     */
    private $attributeMapper;

    protected function setUp(): void
    {
        $this->entityWithValuesBuilder = $this->prophesize(EntityWithValuesBuilderInterface::class);
        $this->attributeValidatorHelper = $this->prophesize(AttributeValidatorHelper::class);
        $this->normalizer = $this->prophesize(NormalizerInterface::class);
        $this->attributeMapper = $this->prophesize(AttributeMapperInterface::class);

        $this->mappableAttributeCopier = new MappableAttributeCopier(
            $this->entityWithValuesBuilder->reveal(),
            $this->attributeValidatorHelper->reveal(),
            $this->normalizer->reveal(),
            ['text'],
            ['magic'],
            new ArrayIterator([$this->attributeMapper->reveal()])
        );
    }

    /**
     * @test
     */
    public function copyAttributeData_maps_data_if_applicable()
    {
        $this->attributeMapper
            ->supportsMapping(Argument::any())
            ->willReturn(true);

        $this->normalizer
            ->normalize(Argument::any(), 'standard')
            ->willReturn(['data' => 'fonky chunk']);

        [$fromEntityWithValues, $toEntityWithValues, $fromAttribute, $toAttribute, $options] = $this->prophesizeInputParameters();

        $this->attributeMapper
            ->map(Argument::any(), Argument::any())
            ->shouldBeCalled();

        $this->mappableAttributeCopier->copyAttributeData(
            $fromEntityWithValues,
            $toEntityWithValues,
            $fromAttribute,
            $toAttribute,
            $options
        );
    }

    /**
     * @test
     */
    public function copyAttributeData_only_maps_different_types()
    {
        $this->attributeMapper
            ->supportsMapping(Argument::any())
            ->willReturn(true);

        $this->normalizer
            ->normalize(Argument::any(), 'standard')
            ->willReturn(['data' => 'fonky chunk']);

        [$fromEntityWithValues, $toEntityWithValues, $fromAttribute, $unused, $options] = $this->prophesizeInputParameters();

        $toAttribute = $this->prophesize(AttributeInterface::class);
        $toAttribute->getType()
            ->willReturn('pim_catalog_textarea');

        $this->attributeMapper
            ->map(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->mappableAttributeCopier->copyAttributeData(
            $fromEntityWithValues,
            $toEntityWithValues,
            $fromAttribute,
            $toAttribute->reveal(),
            $options
        );
    }

    /**
     * @test
     */
    public function copyAttributeData_removes_target_value()
    {
        $this->attributeMapper
            ->supportsMapping(Argument::any())
            ->willReturn(true);

        $this->normalizer
            ->normalize(Argument::any(), 'standard')
            ->willReturn(null);

        [$fromEntityWithValues, $toEntityWithValues, $fromAttribute, $toAttribute, $options] = $this->prophesizeInputParameters();

        $this->attributeMapper
            ->map(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->entityWithValuesBuilder
            ->addOrReplaceValue(
                $toEntityWithValues,
                $toAttribute,
                Argument::any(),
                Argument::any(),
                null
            )
            ->shouldBeCalledOnce();

        $this->mappableAttributeCopier->copyAttributeData(
            $fromEntityWithValues,
            $toEntityWithValues,
            $fromAttribute,
            $toAttribute,
            $options
        );
    }

    private function prophesizeInputParameters(): array
    {
        $fromEntityWithValues = $this->prophesize(EntityWithValuesInterface::class);
        $toEntityWithValues = $this->prophesize(EntityWithValuesInterface::class);

        $fromAttribute = $this->prophesize(AttributeInterface::class);
        $fromAttribute->getCode()
            ->willReturn('from_code');

        $fromAttribute->getType()
            ->willReturn('pim_catalog_textarea');

        $toAttribute = $this->prophesize(AttributeInterface::class);
        $toAttribute->getType()
            ->willReturn('pim_catalog_boolean');

        $toAttribute->getCode()
            ->willReturn('to_code');

        $options = ['mapping_report_field' => 'big_bad_field'];

        return [
            $fromEntityWithValues->reveal(),
            $toEntityWithValues->reveal(),
            $fromAttribute->reveal(),
            $toAttribute->reveal(),
            $options,
        ];
    }
}
