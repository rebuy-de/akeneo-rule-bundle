<?php

namespace Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class NumberAttributeMapper implements AttributeMapperInterface
{
    private const GENERIC_REPLACEMENTS = [
        ',' => '.',
    ];
    private const ATTRIBUTE_SPECIFIC_REPLACEMENTS = [
        'gr_pages' => [
            'seiten' => '',
        ],
        'gr_sim_card_slots' => [
            'single sim' => 1,
            'dual sim' => 2,
        ],
    ];

    public function map(string $attributeCode, $input)
    {
        $cleanedInput = mb_strtolower(trim($input));
        $replacements = array_merge(self::GENERIC_REPLACEMENTS, self::ATTRIBUTE_SPECIFIC_REPLACEMENTS[$attributeCode] ?? []);

        $output = trim(str_replace(array_keys($replacements), array_values($replacements), $cleanedInput));

        if ('gr_quantity' === $attributeCode) {
            $tokens = explode('$$$', $output);

            $tokens = array_map(function (string $token) {
                return $this->format($token);
            }, $tokens);

            return array_sum($tokens);
        }

        return $this->format($output);
    }

    public function supportsMapping(AttributeInterface $toAttribute): bool
    {
        return $toAttribute->getType() == 'pim_catalog_number';
    }

    private function format($input)
    {
        $input = trim($input);

        if (!is_numeric($input) && !is_float($input)) {
            throw new MappingException(sprintf("Given input[%s] is not a number nor a float", $input));
        }

        return $input + 0;
    }
}
