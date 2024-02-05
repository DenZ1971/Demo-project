<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class DefaultController extends AbstractController
{

    #[Route('/')]
    public function default(): Response
    {
        return $this->redirectToRoute('app_login');
    }
}






