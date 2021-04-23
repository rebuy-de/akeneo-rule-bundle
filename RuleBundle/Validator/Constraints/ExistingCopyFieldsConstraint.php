<?php

namespace Rebuy\Bundle\RuleBundle\Validator\Constraints;

use Rebuy\Bundle\RuleBundle\Validator\ExistingCopyFieldsConstraintValidator;
use Symfony\Component\Validator\Constraint;

class ExistingCopyFieldsConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'You cannot copy data from "%fromField%" field to the "%toField%" field.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return ExistingCopyFieldsConstraintValidator::class;
    }
}
