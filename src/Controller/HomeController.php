<?php

namespace App\Controller;

use App\Entity\Question;


use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(QuestionRepository $questionRepo): Response
    {
        $this->addFlash('success','Votre Question a été bien ajouté');
        // https://randomuser.me/api/portraits/men/79.jpg

        
        $questions = $questionRepo->findBy([],['createdAt' => 'DESC']);

        return $this->render('home/index.html.twig', [
            'questions' => $questions,
        ]);
    }
}
