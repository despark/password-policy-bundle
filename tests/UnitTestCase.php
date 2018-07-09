<?php


namespace Despark\PasswordPolicyBundle\Tests;


use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{

    protected function tearDown()
    {
        \Mockery::close();
    }

    protected function randomDateTime(int $startDate = 0, int $endDate = PHP_INT_MAX): \DateTime
    {
        $timestamp = rand($startDate, $endDate);

        return (new \DateTime())->setTimestamp($timestamp);
    }

}