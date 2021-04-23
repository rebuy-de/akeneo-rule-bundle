<?php

namespace Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class SimpleSelectAttributeMapper implements AttributeMapperInterface
{
    private const FILTER_VALUES = ['skip'];

    /**
     * @var array
     */
    private $selectValueMapping;

    public function __construct(array $selectValueMapping)
    {
        $this->selectValueMapping = $selectValueMapping;
    }

    public function map(string $attributeCode, $input)
    {
        $attributeCode = trim($attributeCode);
        $cleanedInput = mb_strtolower(trim($input));

        if (!isset($this->selectValueMapping[$attributeCode][$cleanedInput])) {
            return (string) $input;
        }

        $value = $this->selectValueMapping[$attributeCode][$cleanedInput];

        if (in_array($value, self::FILTER_VALUES, true)) {
            return '';
        }

        return (string) $value;
    }

    public function supportsMapping(AttributeInterface $toAttribute): bool
    {
        return $toAttribute->getType() === 'pim_catalog_simpleselect';
    }
}
