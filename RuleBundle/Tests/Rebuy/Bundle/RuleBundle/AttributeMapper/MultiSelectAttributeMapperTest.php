<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Rebuy\Bundle\RuleBundle\AttributeMapper\MultiSelectAttributeMapper;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class MultiSelectAttributeMapperTest extends TestCase
{
    /**
     * @var MultiSelectAttributeMapper
     */
    private $multiSelectAttributeMapper;

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

        $this->multiSelectAttributeMapper = new MultiSelectAttributeMapper($mapping);
    }

    /**
     * @test
     */
    public function supportsMapping_supports_multiselect()
    {
        $attribute = $this->prophesize(AttributeInterface::class);
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getCode()->willReturn('rb_test');

        $actual = $this->multiSelectAttributeMapper->supportsMapping($attribute->reveal());

        self::assertTrue($actual);
    }

    /**
     * @test
     */
    public function map_returns_unknown_input_value_as_array()
    {
        $actual = $this->multiSelectAttributeMapper->map('UNKOWN_ATTRIBUTE_CODE', 'Single SIM');

        self::assertEquals(['Single SIM'], $actual);
    }

    /**
     * @dataProvider provideCases
     * @test
     */
    public function map_returns_expected_value(string $attributeCode, $input, $expected)
    {
        $actual = $this->multiSelectAttributeMapper->map($attributeCode, $input);

        self::assertEqualsCanonicalizing($expected, $actual);
    }

    public function provideCases(): array

    {

        /**
         * a lot of test cases have been removed due to making the code public for sharing purposes
         */

        return [
            ['rb_test', 'one two', ['one two']],
            ['rb_test', 'Single SIM', ['1']],

        ];
    }
}
