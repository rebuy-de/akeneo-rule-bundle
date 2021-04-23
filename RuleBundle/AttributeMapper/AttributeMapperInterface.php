<?php

namespace Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

interface AttributeMapperInterface
{
    public function map(string $attributeCode, $input);

    public function supportsMapping(AttributeInterface $toAttribute): bool;
}
