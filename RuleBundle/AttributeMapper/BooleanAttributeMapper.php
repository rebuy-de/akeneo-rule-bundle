<?php

namespace Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class BooleanAttributeMapper implements AttributeMapperInterface
{
    /**
     * @var array
     */
    private $booleanMapping;

    public function __construct(array $booleanMapping)
    {
        $this->booleanMapping = $booleanMapping;
    }

    public function map(string $attributeCode, $input)
    {
        $normalized = mb_strtolower($input);

        if (array_key_exists($normalized, $this->booleanMapping)) {
            return $this->booleanMapping[$normalized];
        }

        return null;
    }

    public function supportsMapping(AttributeInterface $toAttribute): bool
    {
        return $toAttribute->getType() == 'pim_catalog_boolean';
    }
}
