<?php

namespace Rebuy\Bundle\RuleBundle\Event;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Exception;
use Rebuy\Bundle\RuleBundle\Exception\MissingMappingAttributeException;
use Rebuy\Bundle\RuleBundle\Exception\RuleBundleThrowable;
use Symfony\Contracts\EventDispatcher\Event;

class RebuyCopyEvent extends Event
{
    public const ERROR_EVENT = 'rebuy.rule.rebuy.copy.error.event';

    /**
     * @var AttributeInterface
     */
    private $fromAttribute;

    /**
     * @var AttributeInterface
     */
    private $toAttribute;

    /**
     * @var array
     */
    private $options;

    /**
     * @var Exception
     */
    private $exception;

    /**
     * @var EntityWithValuesInterface
     */
    private $fromEntity;

    /**
     * @var EntityWithValuesInterface
     */
    private $toEntity;

    public function __construct(
        EntityWithValuesInterface $fromEntity,
        EntityWithValuesInterface $toEntity,
        ?AttributeInterface $fromAttribute,
        ?AttributeInterface $toAttribute,
        array $options,
        Exception $exception
    ) {
        $this->fromEntity = $fromEntity;
        $this->toEntity = $toEntity;
        $this->fromAttribute = $fromAttribute;
        $this->toAttribute = $toAttribute;
        $this->options = $options;
        $this->exception = $exception;
    }

    public function getException(): Exception
    {
        return $this->exception;
    }

    public function hasException(): bool
    {
        return !empty($this->exception);
    }

    public function isRuleBundleException(): bool
    {
        return $this->exception instanceof RuleBundleThrowable;
    }

    public function getFromAttribute(): ?AttributeInterface
    {
        return $this->fromAttribute;
    }

    public function getFromCode(): string
    {
        if (empty($this->fromAttribute)) {
            return 'NULL';
        }

        return $this->fromAttribute->getCode();
    }

    public function getToAttribute(): ?AttributeInterface
    {
        return $this->toAttribute;
    }

    public function getToCode(): string
    {
        if (empty($this->toAttribute)) {
            return 'NULL';
        }

        return $this->toAttribute->getCode();
    }

    public function fromLocale(): ?string
    {
        return $this->options['from_locale'];
    }

    public function toLocale(): ?string
    {
        return $this->options['to_locale'];
    }

    public function fromScope(): ?string
    {
        return $this->options['from_scope'];
    }

    public function toScope(): ?string
    {
        return $this->options['to_scope'];
    }

    public function getFromEntity(): EntityWithValuesInterface
    {
        return $this->fromEntity;
    }

    public function getFromValue(): ?ValueInterface
    {
        return $this->fromEntity->getValue($this->getFromCode(), $this->fromLocale(), $this->fromScope());
    }

    public function getToEntity(): EntityWithValuesInterface
    {
        return $this->toEntity;
    }

    public function getMappingReportAttribute(): string
    {
        if (empty($this->options['mapping_report_field'])) {
            throw new MissingMappingAttributeException('Unable to write error report, as no mapping report field has been provided.');
        }

        return $this->options['mapping_report_field'];
    }
}
