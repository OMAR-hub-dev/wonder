<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {


        $questions =[
            [
                'id'=>1,
                'title'=>'Essential WONDER',
                'content'=>'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Alias harum atque ducimus ullam temporibus, sequi rerum natus consequatur modi libero!',
                'rating'=>20,
                'auteur'=>[
                    'nom'=>'Jon J. Martinez',
                    'avatar'=>'https://randomuser.me/api/portraits/men/79.jpg'
                ],
                'nombreOfResponse'=>15
            ],
            [
                'id'=>2,
                'title'=>'Works Only Under These Conditions',
                'content'=>'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Alias harum atque ducimus ullam temporibus, sequi rerum natus consequatur modi libero!',
                'rating'=>-5,
                'auteur'=>[
                    'nom'=>'Perrin Dupont',
                    'avatar'=>'https://randomuser.me/api/portraits/men/24.jpg'
                ],
                'nombreOfResponse'=>20
            ],
            [
                'id'=>3,
                'title'=>'WONDER You Can\'t Afford ',
                'content'=>'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Alias harum atque ducimus ullam temporibus, sequi rerum natus consequatur modi libero!',
                'rating'=>0,
                'auteur'=>[
                    'nom'=>'Wyatt Quiron',
                    'avatar'=>'https://randomuser.me/api/portraits/men/11.jpg'
                ],
                'nombreOfResponse'=>25
            ],
            [
                'id'=>4,
                'title'=>'How To Start WONDER With Less Than $100',
                'content'=>'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Alias harum atque ducimus ullam temporibus, sequi rerum natus consequatur modi libero!',
                'rating'=>35,
                'auteur'=>[
                    'nom'=>'Ferrau Fontaine',
                    'avatar'=>'https://randomuser.me/api/portraits/men/28.jpg'
                ],
                'nombreOfResponse'=>30
            ],
          

        ];


        return $this->render('home/index.html.twig', [
            'questions' => $questions,
        ]);
    }
}
