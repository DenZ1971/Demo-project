<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class MailerController extends AbstractController
{

    #[Route('/mail')]
    public function sendMail(MailerInterface $mailer): Response
    {
    
        $email = (new Email())
            ->from('d.zapekin@gmail.com')
            ->to('d.zapekin@gmail.com') //($page->getCreateByUser()->getEmail())
            ->subject('Application confirmation')
            ->text("Here is your application confirmation");

        $mailer->send($email);
    }
}






