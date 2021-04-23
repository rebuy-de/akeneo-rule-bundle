<?php

namespace Rebuy\Bundle\RuleBundle\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistry as AkeneoCopierRegistry;

class CopierRegistry extends AkeneoCopierRegistry
{
    /**
     * @param CopierInterface[] $copiers
     */
    public function registerCollection(iterable $copiers)
    {
        foreach ($copiers as $copier) {
            $this->register($copier);
        }
    }
}
