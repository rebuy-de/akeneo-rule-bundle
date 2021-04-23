<?php

namespace Rebuy\Bundle\RuleBundle\AttributeMapper;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;

class MetricAttributeMapper implements AttributeMapperInterface
{
    private const FILTER_VALUES = ['skip'];
    private const METRIC_PATTERN = '/(\d+(?:[,.]\d+)?) ?([\"A-Za-z]{1,3})/';

    /**
     * @var LegacyMeasurementProvider
     */
    protected $measurementProvider;

    /**
     * @var array
     */
    private $metricMapping;

    public function __construct(LegacyMeasurementProvider $measurementProvider, array $metricMapping)
    {
        $this->measurementProvider = $measurementProvider;
        $this->metricMapping = $metricMapping;
    }

    public function map(string $attributeCode, $input)
    {
        $attributeCode = trim($attributeCode);
        $cleanedInput = mb_strtolower(trim($input));

        if (isset($this->metricMapping[$attributeCode][$cleanedInput])) {
            $input = $this->metricMapping[$attributeCode][$cleanedInput];
        }

        if (in_array($input, self::FILTER_VALUES, true)) {
            return [];
        }

        $found = preg_match(self::METRIC_PATTERN, $input, $matches);

        if (empty($found)) {
            throw new MappingException(sprintf("Unable to parse metric amount and unit from input[%s].", $input));
        }

        [, $amount, $unit] = $matches;

        $amount = str_replace(",", ".", $amount);

        $unit = $this->findUnitSymbolInAnyFamily($unit);

        if (empty($unit)) {
            throw new MappingException(sprintf("Unable to parse unit from input[%s].", $input));
        }

        return ['amount' => $amount, 'unit' => $unit];
    }

    public function supportsMapping(AttributeInterface $toAttribute): bool
    {
        return $toAttribute->getType() == 'pim_catalog_metric';
    }

    private function findUnitSymbolInAnyFamily(string $needle): ?string
    {
        $families = $this->measurementProvider->getMeasurementFamilies();

        foreach ($families as $family) {
            foreach ($family['units'] as $unit => $meta) {
                if ($meta['symbol'] == $needle) {
                    return $unit;
                }
            }
        }

        return null;
    }
}
