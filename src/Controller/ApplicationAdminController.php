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
use Knp\Component\Pager\PaginatorInterface;



class ApplicationAdminController extends AbstractController
{
    #[Route('/admin/application', name: 'app_admin_application')]
    public function index(
        PaginatorInterface $paginator,
        Request $request,
        ApplicationRepository $applicationRepository): Response
    {
            
        $pagination = $paginator->paginate(
            $applicationRepository->findAll(),
            $request->query->getInt('offset', 1), /*page number*/
            8 /*limit per page*/
        );
        
            
            return $this->render('application/admin.index.html.twig', [
                'pagination' => $pagination,
                
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
