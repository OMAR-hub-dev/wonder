<?php

namespace App\Controller;

use App\Entity\Vote;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Entity\Question;
use App\Form\CommentType;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function index(Request $request,EntityManagerInterface $em): Response
    {
        $user= $this->getUser();
        $question = new Question();
        $formquestion = $this->createForm(QuestionType::class, $question);

        $formquestion->handleRequest($request);
         if($formquestion->isSubmitted() && $formquestion->isValid()){
            $question->setNombreOfResonse(0);
            $question->setRating(0);
            $question->setAuthor($user);
            $question->setCreatedAt(new DateTimeImmutable());

            $em->persist($question);
            $em->flush();
            $this->addFlash('success', 'Votre question a été ajoutée');
            return  $this->redirectToRoute('home');
         }

        return $this->render('question/index.html.twig', [
            'form' => $formquestion->createView(),

        ]);
    }


    #[Route('/question/{id}', name: 'question_show')]
    public function show(Request $request, QuestionRepository $questionrepo,int $id,  EntityManagerInterface $em): Response
    {
      $question = $questionrepo->getQuestionsWithCommentsAndAuthors($id); 
        $options = [
            'question' => $question
        ];
        $user = $this->getUser();

        if ($user){
            $comment= new Comment(); 
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);

            if ($commentForm->isSubmitted() && $commentForm->isValid()){
                    $comment->setCreatedAt(new \DateTimeImmutable());
                    $comment->setRating(0);
                    $comment->setQuestion($question);
                    $comment->setAuthor($user);
                    $question->setNombreOfResonse($question->getNombreOfResonse() + 1);
                    $em->persist($comment);
                    $em->flush();
                    $this->addFlash('success', 'Votre reponse a bien été ajouter');
                    return  $this->redirect($request->getUri());
            }
            $options['form'] = $commentForm->createView();

        }
            
                return $this->render('question/show.html.twig',$options);
    }


    #[Route("/question/rating/{id}/{score}",name:"question_rating")]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function ratingQuestion(Request $request, Question $question,int $score, EntityManagerInterface $em, VoteRepository $voteRepo)
    {

        $user = $this->getUser();
        if ($user !== $question->getAuthor()) {
          $vote = $voteRepo->findOneBy([
            'author' => $user,
            'question' => $question
          ]);
          if ($vote) {
                    if (($vote->getIsLiked() && $score > 0) || (!$vote->getIsLiked() && $score < 0)) {
                      $em->remove($vote);
                      $question->setRating($question->getRating() + ($score > 0 ? -1 : 1));
                    } else {
                      $vote->setIsLiked(!$vote->getIsLiked());
                      $question->setRating($question->getRating() + ($score > 0 ? 2 : -2));
                    }
              } else {
                      $vote = new Vote();
                      $vote->setAuthor($user);
                      $vote->setQuestion($question);
                      $vote->setIsLiked($score > 0 ? true : false);
                      $question->setRating($question->getRating() + $score);
                      $em->persist($vote);
                }
          $em->flush();
        }


      
        $referer = $request->server->get('HTTP_REFERER');

        return $referer? $this->redirect($referer): $this->redirectToRoute('home');
    }

    #[Route("/comment/rating/{id}/{score}",name:"comment_rating")]
  #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function ratingComment(Request $request, Comment $comment,VoteRepository $voteRepo,int $score, EntityManagerInterface $em){

      $user = $this->getUser();
      if ($user !== $comment->getAuthor()) {
        $vote = $voteRepo->findOneBy([
          'author' => $user,
          'comment' => $comment
        ]);
        if ($vote) {
                  if (($vote->getIsLiked() && $score > 0) || (!$vote->getIsLiked() && $score < 0)) {
                    $em->remove($vote);
                    $comment->setRating($comment->getRating() + ($score > 0 ? -1 : 1));
                  } else {
                    $vote->setIsLiked(!$vote->getIsLiked());
                    $comment->setRating($comment->getRating() + ($score > 0 ? 2 : -2));
                  }
            } else {
                    $vote = new Vote();
                    $vote->setAuthor($user);
                    $vote->setComment($comment);
                    $vote->setIsLiked($score > 0 ? true : false);
                    $comment->setRating($comment->getRating() + $score);
                    $em->persist($vote);
              }
        $em->flush();
      }
      
        $comment ->setRating($comment->getRating() + $score);
        $em->flush();
        $referer = $request->server->get('HTTP_REFERER');

        return $referer? $this->redirect($referer): $this->redirectToRoute('home');
    }


}



//    $question =  [
    //     'id'=>3,
    //     'title'=>'WONDER You Can\'t Afford ',
    //     'content'=>'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Alias harum atque ducimus ullam temporibus, sequi rerum natus consequatur modi libero!',
    //     'rating'=>0,
    //     'auteur'=>[
    //         'nom'=>'Wyatt Quiron',
    //         'avatar'=>'https://randomuser.me/api/portraits/men/11.jpg'
    //     ],
    //     'nombreOfResponse'=>25
    // ];