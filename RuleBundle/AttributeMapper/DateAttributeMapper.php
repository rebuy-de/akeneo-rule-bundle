<?php

namespace Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use DateTime;
use DateTimeInterface;
use Exception;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class DateAttributeMapper implements AttributeMapperInterface
{
    public function map(string $attributeCode, $input)
    {
        try {
            $dateTime = new DateTime($input);
        } catch (Exception $e) {
            throw new MappingException(sprintf("Unable to parse DateTime from input '%s'", $input));
        }

        return $dateTime->format(DateTimeInterface::ISO8601);
    }

    public function supportsMapping(AttributeInterface $toAttribute): bool
    {
        return $toAttribute->getType() == 'pim_catalog_date';
    }
}
