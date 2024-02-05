<?php

namespace App\MessageHandler;

use App\Message\PageMessage;
use Mpdf\MpdfException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Repository\ApplicationRepository;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;


use Mpdf\Mpdf;


#[AsMessageHandler]
class MessageHandler
{
    private $applicationRepository;
    private $mailer;

    public function __construct(MailerInterface $mailer, ApplicationRepository $applicationRepository)
    {
        $this->mailer = $mailer;
        $this->applicationRepository = $applicationRepository;
    }


    public function __invoke(PageMessage $message): void
    {
        $pageId = $message->getPage();

        $page = $this->applicationRepository->find($pageId);


//        $mpdf = new Mpdf();
//        $content = '<h1>Hello world</h1>'; //$page;
//        $mpdf->writeHTML($content);
//        $applicationPdf = $mpdf->output('', 'S');
        
        
        $email = (new Email())
            ->from('d.zapekin@gmail.com')
            ->to('d.zapekin@gmail.com') //($page->getCreateByUser()->getEmail())
            ->subject('Application confirmation')
            ->text("Here is your application confirmation $pageId");
            // ->attach($applicationPdf, 'application.pdf');

        $this->mailer->send($email);
        
    }
} 