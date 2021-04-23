<?php

namespace Rebuy\Bundle\RuleBundle\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\CopierActionApplier;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Rebuy\Bundle\RuleBundle\Model\RebuyCopyAction;

class RebuyCopyActionApplier extends CopierActionApplier
{
    public function supports(ActionInterface $action)
    {
        return $action instanceof RebuyCopyAction;
    }
}
