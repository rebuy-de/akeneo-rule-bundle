<?php

namespace Rebuy\Bundle\RuleBundle\Event;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use DateTime;
use Rebuy\Bundle\RuleBundle\Exception\AttributeNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RebuyCopySubscriber implements EventSubscriberInterface
{
    /**
     * @var IdentifiableObjectRepositoryInterface
     */
    private $repository;

    /**
     * @var EntityWithValuesBuilderInterface
     */
    private $entityWithValuesBuilder;

    /**
     * @var array
     */
    private $messageLog = [];

    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->repository = $attributeRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RebuyCopyEvent::ERROR_EVENT => 'onCopyError',
        ];
    }

    public function onCopyError(RebuyCopyEvent $event)
    {
        $message = $this->createMessage($event);

        $this->log($message, $event);
    }

    private function createMessage(RebuyCopyEvent $errorEvent): string
    {
        return sprintf(
            "• Unable to copy value[%s] from source attribute[%s], locale[%s] and scope[%s] to target attribute[%s], locale[%s] and scope[%s]: %s",
            $errorEvent->getFromValue(),
            $errorEvent->getFromCode(),
            $errorEvent->fromLocale() ?? 'NULL',
            $errorEvent->fromScope() ?? 'NULL',
            $errorEvent->getToCode(),
            $errorEvent->toLocale() ?? 'NULL',
            $errorEvent->toScope() ?? 'NULL',
            $errorEvent->getException()->getMessage()
        );
    }

    private function log(string $message, RebuyCopyEvent $event)
    {
        $attribute = $this->findMappingReportAttribute($event);

        $identifier = spl_object_hash($event->getToEntity());

        if (!isset($this->messageLog[$identifier])) {
            $this->messageLog[$identifier] = [];
        }

        array_push($this->messageLog[$identifier], $message);
        $message = implode("\n", $this->messageLog[$identifier]);

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $event->getToEntity(),
            $attribute,
            null,
            null,
            $message
        );
    }

    private function findMappingReportAttribute(RebuyCopyEvent $event): AttributeInterface
    {
        $attribute = $this->repository->findOneByIdentifier($event->getMappingReportAttribute());

        if (empty($attribute)) {
            $message = sprintf('Unable to find mapping report attribute[%s]', $event->getMappingReportAttribute());
            throw new AttributeNotFoundException($message);
        }

        return $attribute;
    }
}
