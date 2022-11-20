<?php

namespace App\Controller;

use App\Form\QuestionType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function index(Request $request): Response
    {
        $formquestion = $this->createForm(QuestionType::class);

        $formquestion->handleRequest($request);
         if($formquestion->isSubmitted() && $formquestion->isValid()){
            
         }

        return $this->render('question/index.html.twig', [
            'form' => $formquestion->createView(),
        ]);
    }


    #[Route('/question/{id}', name: 'question_show')]
    public function show(Request $request, string $id): Response
    {
       $question =  [
        'id'=>3,
        'title'=>'WONDER You Can\'t Afford ',
        'content'=>'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Alias harum atque ducimus ullam temporibus, sequi rerum natus consequatur modi libero!',
        'rating'=>0,
        'auteur'=>[
            'nom'=>'Wyatt Quiron',
            'avatar'=>'https://randomuser.me/api/portraits/men/11.jpg'
        ],
        'nombreOfResponse'=>25
    ];

        return $this->render('question/show.html.twig', [
            'question'=>$question,
        ]);
    }
}
