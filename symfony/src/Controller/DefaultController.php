<?php

namespace App\Controller;

use App\Form\LoanType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
	
	/**
	 * @Route("/", name="home")
	 */
	public function home(Request $request): Response
	{
		$form = $this->createForm(LoanType::class);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			
			//  Process submitted data
			
			
		}
		
		return $this->render(
			'index.html.twig',
			[
				'form' => $form->createView(),
			]
		);
	}
	
	
}
