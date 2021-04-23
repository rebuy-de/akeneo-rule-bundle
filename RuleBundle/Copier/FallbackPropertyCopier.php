<?php

namespace Rebuy\Bundle\RuleBundle\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyCopier;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Exception;
use LogicException;
use Rebuy\Bundle\RuleBundle\Event\RebuyCopyEvent;
use Rebuy\Bundle\RuleBundle\Exception\AttributeNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FallbackPropertyCopier extends PropertyCopier
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        CopierRegistryInterface $copierRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($repository, $copierRegistry);
        $this->eventDispatcher = $eventDispatcher;
    }

    public function copyData(
        $fromEntityWithValues,
        $toEntityWithValues,
        $fromField,
        $toField,
        array $options = []
    ) {
        if (!$fromEntityWithValues instanceof EntityWithValuesInterface ||
            !$toEntityWithValues instanceof EntityWithValuesInterface
        ) {
            throw new InvalidObjectException(
                ClassUtils::getClass($fromEntityWithValues),
                EntityWithValuesInterface::class,
                sprintf(
                    'Expects a "%s", "%s" and "%s" provided.',
                    EntityWithValuesInterface::class,
                    ClassUtils::getClass($fromEntityWithValues),
                    ClassUtils::getClass($toEntityWithValues)
                )
            );
        }

        $copier = $this->copierRegistry->getCopier($fromField, $toField);
        if (null === $copier) {
            throw new LogicException(sprintf('No copier found for fields "%s" and "%s"', $fromField, $toField));
        }

        if ($copier instanceof AttributeCopierInterface) {

            try {
                $toAttribute = $this->getAttribute($toField);

                $codes = array_merge([$fromField], $options['fallback_attributes'] ?? []);
                $fromAttribute = $this->getFromAttribute($fromEntityWithValues, $codes, $options);

                $copier->copyAttributeData(
                    $fromEntityWithValues,
                    $toEntityWithValues,
                    $fromAttribute,
                    $toAttribute,
                    $options
                );
            } catch (Exception $exception) {

                $event = new RebuyCopyEvent(
                    $fromEntityWithValues,
                    $toEntityWithValues,
                    $fromAttribute ?? null,
                    $toAttribute ?? null,
                    $options,
                    $exception
                );

                $this->eventDispatcher->dispatch($event, RebuyCopyEvent::ERROR_EVENT);

                if (!$event->isRuleBundleException()) {
                    throw $exception;
                }
            }
        } else {
            $copier->copyFieldData($fromEntityWithValues, $toEntityWithValues, $fromField, $toField, $options);
        }

        return $this;
    }

    private function getFromAttribute(
        EntityWithValuesInterface $fromEntityWithValues,
        array $fromAttributeCodes,
        array $options
    ): ?AttributeInterface {
        $fromAttribute = null;

        foreach ($fromAttributeCodes as $code) {
            $fromAttribute = $this->getAttribute($code);

            $value = $fromEntityWithValues->getValue($code, $options['from_locale'], $options['from_scope']);

            if (empty($value)) {
                continue;
            }

            if ($value->hasData()) {
                return $fromAttribute;
            }
        }

        if (null === $fromAttribute) {
            $message = sprintf(
                'No attribute found matching any of the given codes[%s]',
                implode(',', $fromAttributeCodes)
            );

            throw new AttributeNotFoundException($message);
        }

        return $fromAttribute;
    }
}
