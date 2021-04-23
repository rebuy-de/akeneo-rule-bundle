<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Rebuy\Bundle\RuleBundle\AttributeMapper\BooleanAttributeMapper;
use PHPUnit\Framework\TestCase;

class BooleanAttributeMapperTest extends TestCase
{
    /**
     * @var BooleanAttributeMapper
     */
    private $booleanAttributeMapper;

    protected function setUp(): void
    {
        $mapping = [
            'ja' => true,
            'y' => true,
            'oui' => true,
            'yes' => true,
            'nein' => false,
            'nicht' => false,
            '' => false,
            '-' => false,
            'n' => false,
        ];


        $this->booleanAttributeMapper = new BooleanAttributeMapper($mapping);
    }

    /**
     * @test
     */
    public function supportsMapping_supports_bool()
    {
        $attribute = $this->prophesize(AttributeInterface::class);
        $attribute->getType()->willReturn('pim_catalog_boolean');

        $actual = $this->booleanAttributeMapper->supportsMapping($attribute->reveal());

        self::assertTrue($actual);
    }

    /**
     * @dataProvider provideTrueCases
     * @test
     */
    public function map_converts_values_to_true(string $input)
    {
        $actual = $this->booleanAttributeMapper->map('rb_attribute', $input);

        self::assertTrue($actual);
    }

    /**
     * @dataProvider provideFalseCases
     * @test
     */
    public function map_converts_values_to_false(string $input)
    {
        $actual = $this->booleanAttributeMapper->map('rb_attribute', $input);

        self::assertFalse($actual);
    }

    /**
     * @test
     */
    public function map_converts_unknown_values_to_null()
    {
        $actual = $this->booleanAttributeMapper->map('rb_attribute', 'unkown value');

        self::assertNull($actual);
    }

    public function provideTrueCases(): array
    {
        return [
            ['ja'],
            ['Ja'],
            ['y'],
            ['Y'],
            ['oui'],
            ['OUI'],
            ['yes'],
            ['Yes'],
        ];
    }

    public function provideFalseCases(): array
    {
        return [
            ['nein'],
            ['Nein'],
            ['nicht'],
            ['Nicht'],
            [''],
            ['-'],
            ['n'],
            ['N'],
        ];
    }
}
