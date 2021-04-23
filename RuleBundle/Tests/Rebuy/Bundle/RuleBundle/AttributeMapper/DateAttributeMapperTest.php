<?php

namespace Rebuy\Bundle\RuleBundle\Tests\Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Rebuy\Bundle\RuleBundle\AttributeMapper\DateAttributeMapper;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class DateAttributeMapperTest extends TestCase
{
    /**
     * @var DateAttributeMapper
     */
    private $dateAttributeMapper;

    protected function setUp(): void
    {
        $this->dateAttributeMapper = new DateAttributeMapper();
    }

    /**
     * @test
     */
    public function supportsMapping_supports_date()
    {
        $attribute = $this->prophesize(AttributeInterface::class);
        $attribute->getType()->willReturn('pim_catalog_date');

        $actual = $this->dateAttributeMapper->supportsMapping($attribute->reveal());

        self::assertTrue($actual);
    }

    /**
     * @test
     */
    public function map_throws_exception_on_unexpected_format()
    {
        $this->expectException(MappingException::class);
        $actual = $this->dateAttributeMapper->map('rb_attribute', 'bogus date 2020');
    }

    /**
     * @dataProvider provideCases
     * @test
     */
    public function map_converts_values_to_iso_format(string $input, string $expected)
    {
        $actual = $this->dateAttributeMapper->map('rb_attribute', $input);

        self::assertEquals($expected, $actual);
    }

    public function provideCases(): array
    {
        return [
            ['2020-12-01', '2020-12-01T00:00:00+0000'],
            ['Tue Oct 27 15:35:08 CDT 2009', '2009-10-27T15:35:08-0500'],
            ['1.12.2020 13:15:35', '2020-12-01T13:15:35+0000'],
        ];
    }
}
