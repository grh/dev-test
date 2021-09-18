<?php

namespace App\Controller;

use App\Form\LoanType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\LoanParameter;
use App\Service\LoanCalculator;
use Knp\Snappy\Pdf;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
	/**
	 * @Route("/", name="home")
	 */
	public function home(Request $request, LoanParameter $loanParameterService, LoanCalculator $loanCalculatorService): Response
	{
		$form = $this->createForm(LoanType::class);
		
		$formErrors = [];
		$loanData = [];
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			
			//  Process submitted data
			try {
				//  Check for requesting too large of an amount
				if ($data['amount'] > $loanParameterService->getMaxAmount($data['creditScore'])) {
					$formErrors[] = 'The maximum loan amount for your credit score is: $'.$loanParameterService->getMaxAmount($data['creditScore']);
				}
			} catch (\Exception $e) {
				$formErrors = ['Please check your inputs and resubmit the form'];
			}
			
			
            if (empty($formErrors)) 
            {
                try 
                {
					$interestRate = $loanParameterService->getInterestRate($data['term'], $data['creditScore']);
					
					$fee = $loanParameterService->getOriginationFee($data['amount']);
					
					$payment = $loanCalculatorService->getMonthlyPayment($data['amount'] + $fee, $interestRate, $data['term']);

                    $amortization = $loanCalculatorService->getAmortization($data['amount'], $interestRate, $payment, $data['term']);

                    // Check if payment exceeds 15% of monthly income
                    if ($payment > $data['monthlyGrossIncome'] * 0.15)
                    {
                        $formErrors[] = "Monthly loan payment of \$$payment exceeds 15% of your monthly income";
                    }
					
                    $loanData['amount'] = $data['amount'];
                    $loanData['term'] = $data['term'];
                    $loanData['monthlyGrossIncome'] = $data['monthlyGrossIncome'];
                    $loanData['creditScore'] = $data['creditScore'];
					$loanData['interestRate'] = $interestRate;
					$loanData['fee'] = $fee;
					$loanData['payment'] = $payment;
                    $loanData['amortization'] = $amortization;
                } 
                catch (\Exception $e) 
                {
					$formErrors = ['Please check your inputs and resubmit the form'];
				}
			}
		}

        $_SESSION['loanData'] = $loanData;

		return $this->render(
			'index.html.twig',
			[
				'form'       => $form->createView(),
				'formErrors' => $formErrors,
				'loanData'   => $loanData,
			]
		);
	}
	
	/**
	 * @Route("/pdf", name="pdf")
	 */
	public function pdf(Request $request, Pdf $knpSnappyPdf, MailerInterface $mailer): RedirectResponse
    {
        $pdfPath = sys_get_temp_dir() . '/results.pdf';
        $to = $request->request->get('email');
        $from = 'no-reply@smartpayrentals.com';
        $subject = 'Your SmartPay Loan Estimate!';
        $text = 'Attached are the results of your loan estimate.';

        $knpSnappyPdf->generateFromHtml(
            $this->render(
                'pdf.html.twig',
                [
                    'loanData' => $_SESSION['loanData']
                ]
            ),
            $pdfPath,
            array(),
            True
        );

        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->text($text)
            ->attachFromPath($pdfPath);

        $mailer->send($email);

        return $this->redirectToRoute('home');
    }
}
