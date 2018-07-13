<?php


namespace Despark\PasswordPolicyBundle\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * Class PasswordPolicy.
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class PasswordPolicy extends Constraint
{
    const PASSWORD_IN_HISTORY = '72a1be03-a5d1-4b23-bd70-a6841992a03c';

    public $message = 'Cannot change your password to an old one. You used this password {{ days }}';
}