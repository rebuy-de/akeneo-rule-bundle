<?php

namespace Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class TextAttributeMapper implements AttributeMapperInterface
{
    private const FILTER_VALUES = ['skip'];

    /**
     * @var array
     */
    private $replacements;

    /**
     * @var array
     */
    private $textMappings;

    public function __construct(array $replacements, array $mappings)
    {
        $this->replacements = $replacements;
        $this->textMappings = $mappings;
    }

    public function map(string $attributeCode, $input)
    {
        $cleanedInput = trim($input);
        $value = (string) trim(str_ireplace(array_keys($this->replacements), array_values($this->replacements), $cleanedInput));

        $searchValue = mb_strtolower($value);
        if (!isset($this->textMappings[$attributeCode][$searchValue])) {
            return $value;
        }

        $value = $this->textMappings[$attributeCode][$searchValue];

        if (in_array($value, self::FILTER_VALUES, true)) {
            return '';
        }

        return $value;
    }

    public function supportsMapping(AttributeInterface $toAttribute): bool
    {
        return $toAttribute->getType() == 'pim_catalog_text';
    }
}
