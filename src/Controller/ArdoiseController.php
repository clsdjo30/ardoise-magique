<?php

namespace App\Controller;

use App\Entity\Ardoise;
use App\Repository\ArdoiseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArdoiseController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArdoiseRepository $ardoiseRepository): Response
    {
        $ardoises = $ardoiseRepository->findBy(
            ['isActive' => true],
            ['dateCreation' => 'DESC']
        );

        return $this->render('ardoise/index.html.twig', [
            'ardoises' => $ardoises,
        ]);
    }

    #[Route('/ardoise/{id}', name: 'app_ardoise_show')]
    public function show(Ardoise $ardoise): Response
    {
        // Si l'ardoise n'est pas active, on retourne une 404
        if (!$ardoise->isActive()) {
            throw $this->createNotFoundException('Cette ardoise n\'est pas disponible.');
        }

        return $this->render('ardoise/show.html.twig', [
            'ardoise' => $ardoise,
        ]);
    }
}
