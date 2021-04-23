<?php

namespace Rebuy\Bundle\RuleBundle\Model;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCopyActionInterface;
use Webmozart\Assert\Assert;

class RebuyCopyAction implements ProductCopyActionInterface
{
    public $fromField;

    public $fromLocale;

    public $fromScope;

    public $toField;

    public $toLocale;

    public $toScope;

    protected array $options;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->fromField = $data['from_field'] ?? null;
        $this->fromLocale = $data['from_locale'] ?? null;
        $this->fromScope = $data['from_scope'] ?? null;

        $this->toField = $data['to_field'] ?? null;
        $this->toLocale = $data['to_locale'] ?? null;
        $this->toScope = $data['to_scope'] ?? null;

        $this->options = [
            'from_locale' => $data['from_locale'] ?? null,
            'to_locale' => $data['to_locale'] ?? null,
            'from_scope' => $data['from_scope'] ?? null,
            'to_scope' => $data['to_scope'] ?? null,
            'mapping_report_field' => $data['mapping_report_field'] ?? null,
            'fallback_attributes' => $data['fallback_attributes'] ?? [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFromField()
    {
        return $this->fromField;
    }

    /**
     * {@inheritdoc}
     */
    public function getToField()
    {
        return $this->toField;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedFields()
    {
        return [$this->getToField()];
    }

    public function getType(): string
    {
        return 'rb_copy';
    }

    public function toArray(): array
    {
        Assert::stringNotEmpty($this->fromField);
        Assert::nullOrStringNotEmpty($this->fromScope);
        Assert::nullOrStringNotEmpty($this->fromLocale);
        Assert::stringNotEmpty($this->toField);
        Assert::nullOrStringNotEmpty($this->toScope);
        Assert::nullOrStringNotEmpty($this->toLocale);

        return array_filter([
            'type' => 'copy',
            'from_field' => $this->fromField,
            'from_scope' => $this->fromScope,
            'from_locale' => $this->fromLocale,
            'to_field' => $this->toField,
            'to_scope' => $this->toScope,
            'to_locale' => $this->toLocale,
        ], function ($value): bool {
            return null !== $value;
        });
    }
}
