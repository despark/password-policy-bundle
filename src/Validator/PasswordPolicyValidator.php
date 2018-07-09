<?php


namespace Despark\PasswordPolicyBundle\Validator;


use Carbon\Carbon;
use Despark\PasswordPolicyBundle\Exceptions\ValidationException;
use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Service\PasswordPolicyServiceInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordPolicyValidator extends ConstraintValidator
{

    /**
     * @var PasswordPolicyServiceInterface
     */
    private $passwordPolicyService;
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(PasswordPolicyServiceInterface $passwordPolicyService, TranslatorInterface $translator)
    {
        $this->passwordPolicyService = $passwordPolicyService;
        $this->translator = $translator;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     * @return bool
     * @throws \Despark\PasswordPolicyBundle\Exceptions\ValidationException
     */
    public function validate($value, Constraint $constraint)
    {
        if (is_null($value)) {
            return true;
        }

        $entity = $this->context->getObject();

        if (!$entity instanceof HasPasswordPolicyInterface) {
            throw new ValidationException(sprintf('Expected validation entity to implements %s',
                HasPasswordPolicyInterface::class));
        }

        Carbon::setLocale($this->translator->getLocale());

        if ($history = $this->passwordPolicyService->getHistoryByPassword($value, $entity)) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ days }}', Carbon::instance($history->getCreatedAt())->diffForHumans())
                          ->setCode(PasswordPolicy::PASSWORD_IN_HISTORY)
                          ->addViolation();

            return false;
        }

        return true;
    }
}