<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Rebuy\Bundle\RuleBundle\AttributeMapper\DateAttributeMapper;
use Rebuy\Bundle\RuleBundle\AttributeMapper\NumberAttributeMapper;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class NumberAttributeMapperTest extends TestCase
{
    /**
     * @var NumberAttributeMapper
     */
    private $numberAttributeMapper;

    protected function setUp(): void
    {
        $this->numberAttributeMapper = new NumberAttributeMapper();
    }

    /**
     * @test
     */
    public function supportsMapping_supports_number()
    {
        $attribute = $this->prophesize(AttributeInterface::class);
        $attribute->getType()->willReturn('pim_catalog_number');

        $actual = $this->numberAttributeMapper->supportsMapping($attribute->reveal());

        self::assertTrue($actual);
    }

    /**
     * @test
     */
    public function map_throws_exception_if_input_is_not_numeric()
    {
        $this->expectException(MappingException::class);

        $this->numberAttributeMapper->map('rb_attribute', 'not a number');
    }

    /**
     * @dataProvider provideCases
     * @test
     */
    public function map_converts_values_to_valid_number(string $attributeCode, $input, $expected)
    {
        $actual = $this->numberAttributeMapper->map($attributeCode, $input);

        self::assertEquals($expected, $actual);
    }

    public function provideCases(): array
    {
        return [
            ['rb_test', '3', 3],
            ['rb_test', '6,1', 6.1],
            ['rb_test', '5.6', 5.6],
            ['gr_pages', '502 Seiten', 502],
            ['gr_pages', '5,5 Seiten', 5.5],
            ['gr_quantity', '3
            $$$
            1', 4],
        ];
    }
}
