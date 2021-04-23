<?php

namespace Rebuy\Bundle\RuleBundle\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingCopyFieldsValidator as AkeneoExistingCopyFieldsValidator;
use Rebuy\Bundle\RuleBundle\Model\RebuyCopyAction;
use Rebuy\Bundle\RuleBundle\Validator\Constraints\ExistingCopyFieldsConstraint;
use Symfony\Component\Validator\Constraint;
use Webmozart\Assert\Assert;

class ExistingCopyFieldsConstraintValidator extends AkeneoExistingCopyFieldsValidator
{
    public function validate($action, Constraint $constraint)
    {
        Assert::isInstanceOf($action, RebuyCopyAction::class);
        Assert::isInstanceOf($constraint, ExistingCopyFieldsConstraint::class);

        if (!is_string($action->fromField) || !is_string($action->toField)) {
            return;
        }

        $copier = $this->copierRegistry->getCopier($action->fromField, $action->toField);
        if (null === $copier) {
            $this->context->buildViolation(
                $constraint->message,
                ['{{ from_field }}' => $action->fromField, '{{ to_field }}' => $action->toField]
            )->addViolation();
        }
    }
}
