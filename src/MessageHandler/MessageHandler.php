<?php

namespace App\MessageHandler;

use App\Message\PageMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Repository\ApplicationRepository;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;


use Mpdf\Mpdf;


#[AsMessageHandler]
class MessageHandler
{
    public function __construct(MailerInterface $mailer, ApplicationRepository $applicationRepository)
    {
        
    }
    
    public function __invoke(PageMessage $message)
    {
        $pageId = $message->getPageId();

        $page = $this->applicationRepository->find($pageId);
        dump($page);

        $mpdf = new Mpdf();
        $content = '<h1>Hello world</h1>'; //$page;
        $mpdf->writeHTML($content);
        $applicationPdf = $mpdf->output('', 'S');
        
        
        $email = (new Email())
            ->from('d.zapekin@gmail.com')
            ->to('d.zapekin@gmail.com') //($page->getCreateByUser()->getEmail())
            ->subject('Application confirmation')
            ->text("Here is your application confirmation {$pageId}");
            // ->attach($applicationPdf, 'application.pdf');

        $this->mailer->send($email);
        
    }
} 