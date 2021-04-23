<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;
use Rebuy\Bundle\RuleBundle\AttributeMapper\MetricAttributeMapper;
use PHPUnit\Framework\TestCase;

class MetricAttributeMapperTest extends TestCase
{
    /**
     * @var MetricAttributeMapper
     */
    private $metricAttributeMapper;

    protected function setUp(): void
    {
        $measures = [
            'Length' => [
                'standard' => 'METER',
                'units' => [
                    'CENTIMETER' => [
                        'symbol' => 'cm',
                    ],
                    'METER' => [
                        'symbol' => 'm',
                    ],
                ],
            ],
            'Weight' => [
                'standard' => 'GRAM',
                'units' => [
                    'MILLIGRAM' => [
                        'symbol' => 'mg',
                    ],
                    'GRAM' => [
                        'symbol' => 'g',
                    ],
                    'KILOGRAM' => [
                        'symbol' => 'kg',
                    ],
                ],
            ],
        ];

        $mapping = [
            'rb_attribute' => [
                'zwanzig' => '20 cm',
                'should_be_skipped' => 'skip',
            ],
        ];

        $measurementProvider = $this->prophesize(LegacyMeasurementProvider::class);
        $measurementProvider->getMeasurementFamilies()
            ->willReturn($measures);

        $this->metricAttributeMapper = new MetricAttributeMapper($measurementProvider->reveal(), $mapping);
    }

    /**
     * @test
     */
    public function supportsMapping_supports_metric()
    {
        $attribute = $this->prophesize(AttributeInterface::class);
        $attribute->getType()->willReturn('pim_catalog_metric');

        $actual = $this->metricAttributeMapper->supportsMapping($attribute->reveal());

        self::assertTrue($actual);
    }

    /**
     * @test
     */
    public function map_throws_exception_if_unable_to_parse()
    {
        $this->expectException(MappingException::class);

        $this->metricAttributeMapper->map('rb_attribute', '20');
    }

    /**
     * @test
     */
    public function map_throws_exception_on_unknown_amount()
    {
        $this->expectException(MappingException::class);

        $actual = $this->metricAttributeMapper->map('rb_attribute', 'no_amount gigawatts');
    }

    /**
     * @test
     */
    public function map_throws_exception_on_unknown_unit()
    {
        $this->expectException(MappingException::class);

        $actual = $this->metricAttributeMapper->map('rb_attribute', '2.2 gigawatts');
    }

    /**
     * @dataProvider provideCases
     * @test
     */
    public function map_converts_values(string $input, array $expected)
    {
        $actual = $this->metricAttributeMapper->map('rb_attribute', $input);

        self::assertEquals($expected, $actual);
    }

    public function provideCases(): array
    {
        return [
            ["20 cm", ['amount' => 20, 'unit' => 'CENTIMETER']],
            ["20cm", ['amount' => 20, 'unit' => 'CENTIMETER']],
            ["84,80 m", ['amount' => 84.80, 'unit' => 'METER']],
            ["1.2 m", ['amount' => 1.2, 'unit' => 'METER']],
            ["1.2m", ['amount' => 1.2, 'unit' => 'METER']],
            ["12 g", ['amount' => 12, 'unit' => 'GRAM']],
            ["13,5 kg", ['amount' => 13.5, 'unit' => 'KILOGRAM']],
            ["13,5kg", ['amount' => 13.5, 'unit' => 'KILOGRAM']],
            // apply mapping beforehand
            ["zwanzig", ['amount' => 20, 'unit' => 'CENTIMETER']],
            // skip
            ["should_be_skipped", []],
        ];
    }
}
