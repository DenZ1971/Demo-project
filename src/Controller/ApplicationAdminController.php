<?php

namespace App\Controller;
use App\Entity\Application;
use App\Form\ApplicationUpdateType;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;




class ApplicationAdminController extends AbstractController
{
    #[Route('/admin/application', name: 'app_admin_application')]
    public function index(Request $request, ApplicationRepository $applicationRepository): Response
    {
            $offset = max(0, $request->query->getInt('offset',0));
            $applications = $applicationRepository->getApplicationsPaginator($offset);
            // $applications = $applicationRepository->findAll();
            return $this->render('application/admin.index.html.twig', [
                'applications' => $applications,
                'previous' => $offset - ApplicationRepository::APPLICATIONS_PER_PAGE,
                'next' => min(count($applications), $offset + ApplicationRepository::APPLICATIONS_PER_PAGE),
            ]);
    }


    #[Route('/admin/application/{id}', name: 'app_admin_application_show')]

    public function show(Application $application, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ApplicationUpdateType::class, $application);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirect('/admin/application');
        }

        return $this->render('application/admin.show.html.twig', [
            'application' => $application,
            'application_form' => $form,
            ]);
    }
}
