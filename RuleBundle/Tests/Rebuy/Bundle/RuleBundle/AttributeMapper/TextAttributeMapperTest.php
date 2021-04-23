<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Rebuy\Bundle\RuleBundle\AttributeMapper\DateAttributeMapper;
use Rebuy\Bundle\RuleBundle\AttributeMapper\NumberAttributeMapper;
use Rebuy\Bundle\RuleBundle\AttributeMapper\TextAttributeMapper;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class TextAttributeMapperTest extends TestCase
{
    /**
     * @var TextAttributeMapper
     */
    private $textAttributeMapper;

    protected function setUp(): void
    {
        $replacements = [
            'pixeles' => 'Pixel',
        ];

        /**
         * a lot of test cases have been removed due to making the code public for sharing purposes
         */


        $mappings = [

        ];

        $this->textAttributeMapper = new TextAttributeMapper($replacements, $mappings);
    }

    /**
     * @test
     */
    public function supportsMapping_supports_number()
    {
        $attribute = $this->prophesize(AttributeInterface::class);
        $attribute->getType()->willReturn('pim_catalog_text');

        $actual = $this->textAttributeMapper->supportsMapping($attribute->reveal());

        self::assertTrue($actual);
    }

    /**
     * @dataProvider provideCases
     * @test
     */
    public function map_converts_values_to_valid_number(string $attributeCode, $input, $expected)
    {
        $actual = $this->textAttributeMapper->map($attributeCode, $input);

        self::assertEquals($expected, $actual);
    }

    public function provideCases(): array
    {
        /**
         * a lot of test cases have been removed due to making the code public for sharing purposes
         */

        return [

        ];
    }
}
