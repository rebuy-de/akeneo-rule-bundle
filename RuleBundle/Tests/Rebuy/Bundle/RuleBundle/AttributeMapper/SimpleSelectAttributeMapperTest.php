<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Rebuy\Bundle\RuleBundle\AttributeMapper\SimpleSelectAttributeMapper;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class SimpleSelectAttributeMapperTest extends TestCase
{
    /**
     * @var SimpleSelectAttributeMapper
     */
    private $simpleSelectAttributeMapper;

    protected function setUp(): void
    {
        /**
         * a lot of test cases have been removed due to making the code public for sharing purposes
         */

        $mapping = [
            'rb_test' => [
                'single sim' => '1',
                'cast_to_string' => 18,
                'should_be_skipped' => 'skip',
            ],

        ];

        $this->simpleSelectAttributeMapper = new SimpleSelectAttributeMapper($mapping);
    }

    /**
     * @test
     */
    public function supportsMapping_supports_simpleselect()
    {
        $attribute = $this->prophesize(AttributeInterface::class);
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getCode()->willReturn('rb_test');

        $actual = $this->simpleSelectAttributeMapper->supportsMapping($attribute->reveal());

        self::assertTrue($actual);
    }

    /**
     * @test
     */
    public function map_returns_unknown_input_value_untouched()
    {
        $actual = $this->simpleSelectAttributeMapper->map('UNKOWN_ATTRIBUTE_CODE', 'Single SIM');

        self::assertEquals('Single SIM', $actual);
    }

    /**
     * @dataProvider provideCases
     * @test
     */
    public function map_returns_expected_value(string $attributeCode, $input, $expected)
    {
        $actual = $this->simpleSelectAttributeMapper->map($attributeCode, $input);

        self::assertIsString($actual);
        self::assertEquals($expected, $actual);
    }

    public function provideCases(): array

    {

        /**
         * a lot of test cases have been removed due to making the code public for sharing purposes
         */

        return [
            ['rb_test', 'Single SIM', '1'],

        ];
    }
}
