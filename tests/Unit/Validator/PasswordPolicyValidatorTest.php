<?php


namespace Despark\PasswordPolicyBundle\Tests\Unit\Validator;


use Despark\PasswordPolicyBundle\Exceptions\ValidationException;
use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Despark\PasswordPolicyBundle\Service\PasswordPolicyServiceInterface;
use Despark\PasswordPolicyBundle\Tests\UnitTestCase;
use Despark\PasswordPolicyBundle\Validator\PasswordPolicy;
use Despark\PasswordPolicyBundle\Validator\PasswordPolicyValidator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class PasswordPolicyValidatorTest extends UnitTestCase
{
    /**
     * @var \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface|\Mockery\Mock
     */
    private $entityMock;
    /**
     * @var \Symfony\Component\Validator\Context\ExecutionContextInterface|\Mockery\Mock
     */
    private $contextMock;
    /**
     * @var \Despark\PasswordPolicyBundle\Validator\PasswordPolicyValidator|\Mockery\Mock
     */
    private $validator;
    /**
     * @var PasswordPolicyServiceInterface|\Mockery\Mock
     */
    private $passwordPolicyServiceMock;
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface|\Mockery\Mock
     */
    private $translatorMock;

    /**
     * Setup.
     */
    protected function setUp()
    {
        $this->translatorMock = \Mockery::mock(TranslatorInterface::class);
        $this->translatorMock->shouldReceive('getLocale')
                             ->andReturn('en');

        $this->passwordPolicyServiceMock = \Mockery::mock(PasswordPolicyServiceInterface::class);
        $this->validator = \Mockery::mock(PasswordPolicyValidator::class, [
            $this->passwordPolicyServiceMock,
            $this->translatorMock,
        ])->makePartial();
        $this->contextMock = \Mockery::mock(ExecutionContextInterface::class);
        $this->entityMock = \Mockery::mock(HasPasswordPolicyInterface::class);
    }

    public function testValidatePass()
    {
        $this->contextMock->shouldReceive('getObject')
                          ->once()
                          ->andReturn($this->entityMock);

        $constraint = new PasswordPolicy();

        $this->passwordPolicyServiceMock->shouldReceive('getHistoryByPassword')
                                        ->withArgs(['pwd', $this->entityMock])
                                        ->andReturn(null);

        $this->validator->initialize($this->contextMock);
        $this->assertTrue($this->validator->validate('pwd', $constraint));
    }

    public function testValidateFail()
    {
        $this->contextMock->shouldReceive('getObject')
                          ->once()
                          ->andReturn($this->entityMock);

        $constraintBuilderMock = \Mockery::mock(ConstraintViolationBuilderInterface::class);

        $constraintBuilderMock->shouldReceive('setParameter')
                              ->once()
                              ->andReturnSelf();

        $constraintBuilderMock->shouldReceive('setCode')
                              ->once()
                              ->andReturnSelf();

        $constraintBuilderMock->shouldReceive('addViolation')
                              ->once();

        $this->contextMock->shouldReceive('buildViolation')
                          ->once()
                          ->andReturn($constraintBuilderMock);

        $constraint = new PasswordPolicy();

        $historyMock = \Mockery::mock(PasswordHistoryInterface::class);
        $historyMock->shouldReceive('getCreatedAt')
                    ->andReturn(new \DateTime('-2 days'));

        $this->passwordPolicyServiceMock->shouldReceive('getHistoryByPassword')
                                        ->withArgs(['pwd', $this->entityMock])
                                        ->andReturn($historyMock);

        $this->validator->initialize($this->contextMock);
        $this->assertFalse($this->validator->validate('pwd', $constraint));
    }

    public function testValidateNullValue()
    {
        $this->assertTrue($this->validator->validate(null, new PasswordPolicy()));
    }

    public function testValidateBadEntity()
    {
        $this->contextMock->shouldReceive('getObject')
                          ->once()
                          ->andReturn(new PasswordPolicyValidatorTest());

        $constraint = new PasswordPolicy();

        $this->validator->initialize($this->contextMock);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Expected validation entity to implements '.HasPasswordPolicyInterface::class);
        $this->assertTrue($this->validator->validate('pwd', $constraint));
    }

}