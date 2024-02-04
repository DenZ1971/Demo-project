<?php

namespace App\Controller;
use App\Entity\Application;
use App\Form\ApplicationType;
use App\Entity\Status;
use App\Form\ApplicationUpdateType;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\PageMessage;
use Knp\Component\Pager\PaginatorInterface;






class ApplicationController extends AbstractController
{
    #[Route('/application', name: 'app_application')]
    public function index(PaginatorInterface $paginator,
        Request $request,
        EntityManagerInterface $em,
        ApplicationRepository $applicationRepository): Response
    {   

        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $application
                ->setStatus(Status::Created)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setCreateByUser($this->getUser());

            $em->persist($application);
            $em->flush();

            return $this->redirectToRoute('app_application');
        }

        $pagination = $paginator->paginate(
            $applicationRepository->findByUserId($this->getUser()->getId()),
            $request->query->getInt('offset', 1), /*page number*/
            4 /*limit per page*/
        );
        
            return $this->render('application/index.html.twig', [
            'application_form' => $form,
            'pagination' => $pagination,
            
            ]);
        
    }

    #[Route('/application/{id}', name: 'app_application_show')]

    public function show(Application $application): Response
    {   
        
        return $this->render('application/show.html.twig', [
            'application' => $application,
           
        ]);
    }

    #[Route('/application/{id}/edit', name: 'app_application_edit')]
    public function edit(MessageBusInterface $bus,
        Request $request,
        Application $application,
        EntityManagerInterface $em,
        ApplicationRepository $applicationRepository): Response
    {
        if ($application->getStatusAsString() == 'created') {

            $form = $this->createForm(ApplicationType::class, $application);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
            $applicationRepository->save($application, true);

                return $this->redirectToRoute('app_application', [], Response::HTTP_SEE_OTHER);
            }   

            return $this->render('application/edit.html.twig', [
                'application' => $application,
                'application_form' => $form,
            ]);

        } elseif ($application->getStatusAsString() == 'done' && $application->getApproved()) {
            $form = $this->createForm(ApplicationUpdateType::class, $application);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();

                $bus->dispatch(new PageMessage($application->getId()));
                



                return $this->redirect('/application');
        }

        return $this->render('application/admin.show.html.twig', [
            'application' => $application,
            'application_form' => $form
        ]);
        } else {
            return $this->redirectToRoute('app_application', [], Response::HTTP_SEE_OTHER);
        }
    }
}
