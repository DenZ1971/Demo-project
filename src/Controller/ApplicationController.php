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





class ApplicationController extends AbstractController
{
    #[Route('/application', name: 'app_application')]
    public function index(
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
        $filteredApplications = $applicationRepository->findByUserId($this->getUser()->getId());
        if ($filteredApplications == null)
        {
            $offset = max(0, $request->query->getInt('offset', 0));
            $applications = null;
            return $this->render('application/index.html.twig', [
            'application_form' => $form,
            'applications' => $applications,
            'previous' => $offset - ApplicationRepository::APPLICATIONS_PER_PAGE,
            'next' => null,
            ]);
            
        } else {

            // $offset = max(0, $request->query->getInt('offset', 0));
            $applications = $filteredApplications;   //->getApplicationsPaginator($offset);
            return $this->render('application/index.html.twig', [
            'application_form' => $form,
            'applications' => $applications,
            // 'previous' => $offset - ApplicationRepository::APPLICATIONS_PER_PAGE,
            // 'next' => min(count($applications), $offset + ApplicationRepository::APPLICATIONS_PER_PAGE),
            ]);
        }


    }

    #[Route('/application/{id}', name: 'app_application_show')]

    public function show(Application $application): Response
    {   
        
        return $this->render('application/show.html.twig', [
            'application' => $application,
           
        ]);
    }

    #[Route('/application/{id}/edit', name: 'app_application_edit')]
    public function edit(
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
