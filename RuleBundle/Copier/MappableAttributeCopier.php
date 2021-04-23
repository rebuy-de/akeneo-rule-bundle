<?php

namespace Rebuy\Bundle\RuleBundle\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AbstractAttributeCopier;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Rebuy\Bundle\RuleBundle\AttributeMapper\AttributeMapperInterface;
use Rebuy\Bundle\RuleBundle\Exception\MappingException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MappableAttributeCopier extends AbstractAttributeCopier
{
    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * @var AttributeMapperInterface[]
     */
    private $attributeMappers;

    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        NormalizerInterface $normalizer,
        array $supportedFromTypes,
        array $supportedToTypes,
        iterable $attributeMappers
    ) {
        parent::__construct($entityWithValuesBuilder, $attrValidatorHelper);

        $this->normalizer = $normalizer;
        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;

        $this->attributeMappers = iterator_to_array($attributeMappers);
        $this->resolver->setRequired(['mapping_report_field']);
        $this->resolver->setDefault('fallback_attributes', []);
    }

    public function copyAttributeData(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ) {

        $options = $this->resolver->resolve($options);

        $this->checkLocaleAndScope($fromAttribute, $options['from_locale'], $options['from_scope']);
        $this->checkLocaleAndScope($toAttribute, $options['to_locale'], $options['to_scope']);

        $this->copySingleValue(
            $fromEntityWithValues,
            $toEntityWithValues,
            $fromAttribute,
            $toAttribute,
            $options['from_locale'],
            $options['to_locale'],
            $options['from_scope'],
            $options['to_scope']
        );
    }

    protected function copySingleValue(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        $fromValue = $fromEntityWithValues->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        $standardData = $this->normalizer->normalize($fromValue, 'standard');

        if (null == $standardData) {
            $standardData['data'] = null;
        } else if ($fromAttribute->getType() != $toAttribute->getType()) {
            $standardData['data'] = $this->mapData($toAttribute, $standardData['data']);
        }

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $toEntityWithValues,
            $toAttribute,
            $toLocale,
            $toScope,
            $standardData['data']
        );
    }

    /**
     * This overload lacks the equal check on the attribute types, so we are able to copy between different types.
     */
    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        $supportsFrom = in_array($fromAttribute->getType(), $this->supportedFromTypes);
        $supportsTo = in_array($toAttribute->getType(), $this->supportedToTypes);

        return $supportsFrom && $supportsTo;
    }

    private function mapData(AttributeInterface $attribute, $data)
    {
        foreach ($this->attributeMappers as $attributeMapper) {
            if ($attributeMapper->supportsMapping($attribute)) {
                return $attributeMapper->map($attribute->getCode(), $data);
            }
        }

        return $data;
    }
}
