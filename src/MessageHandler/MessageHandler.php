<?php

namespace App\MessageHandler;

use App\Message\PageMessage;
use Mpdf\MpdfException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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


    /**
     * @throws MpdfException
     * @throws TransportExceptionInterface
     */
    public function __invoke(PageMessage $message): void
    {
        $pageId = $message->getPage();

        $page = $this->applicationRepository->find($pageId);
        $page_title = $this->applicationRepository->find($pageId)->getTitle();
        $page_description = $this->applicationRepository->find($pageId)->getDescription();
        $page_status = $this->applicationRepository->find($pageId)->getStatusAsString();
        $page_category = $this->applicationRepository->find($pageId)->getCategory()->getCategory();


        $mpdf = new Mpdf();
        $content = (sprintf("<h1>Application confirmation</h1><br>
        <h2>Title : %s</h2><br>
        <h3>Category : %s</h3><br>
        <h4>Description : %s</h4><br>
        <h3>Status : %s</h3>", $page_title, $page_category, $page_description, $page_status));
        $mpdf->writeHTML($content);
        $applicationPdf = $mpdf->output('', 'S');


        $email = (new Email())
            ->from('d.zapekin@gmail.com')
            ->to($page->getCreateByUser()->getEmail())
            ->subject('Application confirmation')
            ->text("Here is your application confirmation $pageId")
            ->attach($applicationPdf, 'application.pdf');

        $this->mailer->send($email);
    }
}

