<?php

namespace Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class MultiSelectAttributeMapper implements AttributeMapperInterface
{
    private const FILTER_VALUES = ['skip'];
    private const TOKENIZING_PATTERNS = [null, '/\${3}/', '/[,|;\/]/'];

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
        if (false === array_key_exists($attributeCode, $this->selectValueMapping)) {
            return [$input];
        }

        $matches = [];
        $tokenMap = [];

        $input = [$input => $input];
        foreach (self::TOKENIZING_PATTERNS as $pattern) {
            $tokenMap = $this->createTokenMap($input, $pattern);
            $intersect = array_intersect_key($this->selectValueMapping[$attributeCode], $tokenMap);
            $matches += $intersect;

            $input = array_diff_key($tokenMap, $intersect);
            if (empty($input)) {
                break;
            }
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveArrayIterator(
                $matches + $tokenMap
            )
        );

        $values = iterator_to_array($it, false);

        return $this->clean($values);
    }

    public function supportsMapping(AttributeInterface $toAttribute): bool
    {
        return $toAttribute->getType() === 'pim_catalog_multiselect';
    }

    private function createTokenMap(array $inputs, ?string $pattern): array
    {
        if ($pattern !== null) {
            $list = [];

            foreach ($inputs as $input) {
                $list += preg_split($pattern, $input);
            }
        } else {
            $list = $inputs;
        }

        $formatted = array_map(function ($token) {
            return mb_strtolower(trim($token));
        }, $list);

        return array_combine($formatted, $formatted);
    }

    private function clean(array $values): array
    {
        $validValues = array_filter($values, function ($value, $key) {
            if (is_null($value) || $value === '') {
                return false;
            }

            return !in_array($value, self::FILTER_VALUES, true);
        }, ARRAY_FILTER_USE_BOTH);

        $valuesAsString = array_map(function ($input) {
            return (string) $input;
        }, $validValues);

        sort($valuesAsString);

        return $valuesAsString;
    }
}
