<?php 

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Article;
use App\Form\RegisterFormType;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{

       /**
        * @Route("/inscription", name= "user_register", methods={"GET|POST"})
        */
        public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{     
        #1 instanciation de class
       $user = new User();

    #2 creation du form
    $form = $this->createForm(RegisterFormType::class, $user )
    ->handleRequest($request);


    #4 si le form est soumis et valide 
    if($form->isSubmitted() && $form->isValid()) {
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new DateTime());
        $user->setUpdatedAt(new DateTime());


        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', "vous vous êtes inscrit avec succès !");
        return $this->redirectToRoute('app_login');
       
    }

    #3 on retourne la vue du formulaire

    return $this->render("user/register.html.twig", [
        'form' =>$form->createView()
    ]);
    
   }

     /**
      * @Route("/profile/mon-espace-perso", name="show_profile", methods={"GET"})
      */
    public function showProfile(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findBy(['author' => $this->getUser()]);

        return $this->render("user/show_profile.html.twig", [
            'articles' => $articles
        ]);

    }


    /**
     * @Route("/profile/changer-mon-mot-de-passe", name="change_password", methods={"GET|POST"})
     */
    public function changePassword(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Request $request): Response
    {
          $form = $this->createForm(ChangePasswordFormType::class)
                ->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

             
    /** @var User $user  */
     $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $this->getUser()]);
   
        $user->setUpdatedAt(new Datetime());

        $user->setPassword($passwordHasher->hashPassword(
            $user, $form->get('plainPassword')->getData()
        )
            
        );

        $entityManager->persist($user);
        $entityManager->flush();

        $this ->addFlash('success', "Votre mot de passe a bien été changé");
        return $this->redirectToRoute('show_profile');
    }

          return $this->render('user/change_password.html.twig', [
            'form' => $form->createView()
          ]);

    }

}
