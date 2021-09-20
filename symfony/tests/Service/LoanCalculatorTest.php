<?php

namespace App\Tests\Service;

use PHPUnit\Framework\AssertionFailedError;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\LoanCalculator;
use Exception;

class LoanCalculatorTest extends KernelTestCase
{
    public function testMonthlyPayment(): void
    {
	    $kernel = self::bootKernel();
	    $container = static::getContainer();
	
	    /** @var LoanCalculator $loanCalculatorService */
	    $loanCalculatorService = $container->get(LoanCalculator::class);
    	
	    $this->assertEquals( 100, $loanCalculatorService->getMonthlyPayment( 1000, 0, 10 ) );
		$this->assertEquals( 13.22, $loanCalculatorService->getMonthlyPayment( 1000, 10, 120 ) );
	    
	    $this->assertEquals( 1000, $loanCalculatorService->getMonthlyPayment( 1000, 10, 0 ) );
	    $this->assertEquals( 1000, $loanCalculatorService->getMonthlyPayment( 1000, 0, 0 ) );
		
	    $this->assertEquals( 212.47, $loanCalculatorService->getMonthlyPayment( 10000, 10, 60 ) );
		
	    try {
		    $loanCalculatorService->getMonthlyPayment(-5000, 5, 10 );
		    $this->fail('Should have thrown an exception for invalid principal amount');
	    } catch (AssertionFailedError $e) {
		    //  Pass Through failure
		    throw $e;
	    } catch (Exception $e) {
		    $this->assertEquals('invalid principal amount', $e->getMessage());
	    }
	
	    try {
		    $loanCalculatorService->getMonthlyPayment(5000, -5, 10 );
		    $this->fail('Should have thrown an exception for invalid Interest rate');
	    } catch (AssertionFailedError $e) {
		    //  Pass Through failure
		    throw $e;
	    } catch (Exception $e) {
		    $this->assertEquals('invalid Interest rate', $e->getMessage());
	    }
	
	    try {
		    $loanCalculatorService->getMonthlyPayment(5000, 5, -10 );
		    $this->fail('Should have thrown an exception for invalid term');
	    } catch (AssertionFailedError $e) {
		    //  Pass Through failure
		    throw $e;
	    } catch (Exception $e) {
		    $this->assertEquals('invalid term', $e->getMessage());
	    }
	    
    }

    public function testAmortization(): void
    {
	    $kernel = self::bootKernel();
	    $container = static::getContainer();
	
	    /** @var LoanCalculator $loanCalculatorService */
	    $loanCalculatorService = $container->get(LoanCalculator::class);

        /* 
         * sample amortization table:
         * principal = $1050
         * interest = 6.99%
         * monthly payment = $90.85
         * term = 12 months
         */
        $amortization = [
            array("principal" => 1050.0, "interestPayment" => 6.12, "principalPayment" => 84.73, "totalPayment" => 90.85),
            array("principal" => 965.27, "interestPayment" => 5.62, "principalPayment" => 85.23, "totalPayment" => 90.85),
            array("principal" => 880.04, "interestPayment" => 5.13, "principalPayment" => 85.72, "totalPayment" => 90.85),
            array("principal" => 794.32, "interestPayment" => 4.63, "principalPayment" => 86.22, "totalPayment" => 90.85),
            array("principal" => 708.09, "interestPayment" => 4.12, "principalPayment" => 86.73, "totalPayment" => 90.85),
            array("principal" => 621.37, "interestPayment" => 3.62, "principalPayment" => 87.23, "totalPayment" => 90.85),
            array("principal" => 534.14, "interestPayment" => 3.11, "principalPayment" => 87.74, "totalPayment" => 90.85),
            array("principal" => 446.40, "interestPayment" => 2.60, "principalPayment" => 88.25, "totalPayment" => 90.85),
            array("principal" => 358.15, "interestPayment" => 2.09, "principalPayment" => 88.76, "totalPayment" => 90.85),
            array("principal" => 269.38, "interestPayment" => 1.57, "principalPayment" => 89.28, "totalPayment" => 90.85),
            array("principal" => 180.10, "interestPayment" => 1.05, "principalPayment" => 89.80, "totalPayment" => 90.85),
            array("principal" => 090.30, "interestPayment" => 0.53, "principalPayment" => 90.30, "totalPayment" => 90.83)
        ];

        $this->assertEquals( 
            $amortization, 
            array_map( function($a) {
                return array_map( function ($v) {
                    return round($v, 2);
                }, $a);
            }, $loanCalculatorService->getAmortization(1050, 6.99, 90.85, 12))
        );
    }
}
