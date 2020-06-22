<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    /**
     * permet d'afficher et de gerer le formulaire de connexion
     * @Route("/login", name="account_login")
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $userName = $utils->getLastUsername();
        

        return $this->render('account/login.html.twig', [
            'hasError' => $error !== null,
            'userName' => $userName
        ]);
    }

    /**
     * permet de se déconecter
     * @Route("/logout", name="account_logout")
     * @return void
     */
    public function logout(){
        // .. rien
    }

    /**
     * permet d'afficher le formulaire d'inscription
     * @Route("/register", name="account_register")
     * @return response
     */
    public function register(EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder){
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        // on gére la requete 
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            // on hash le mot de passe
            $hash = $encoder->encodePassword($user, $user->getHash());
            $user->setHash($hash);

            // on enregistre en base de données
            $manager->persist($user);
            $manager->flush();

            // message de success
            $this->addFlash(
                'success',
                "Votre compte a bien été creer !"
            );

            // redirection sur la route de connexion
            return $this->redirectToRoute("account_login");
        }

        return $this->render("account/registration.html.twig", [
            'form' => $form->createView()
        ]);
    }


    /**
     * permet dafficher et de traité le formulaire de modification de profil
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER")
     * @return response
     */
    public function profile(Request $request, EntityManagerInterface $manager){

        // recuperation de l'utilisateur actuellement connecté !
        $user = $this->getUser();
        // on creer le formulaire
        $form = $this->createForm(AccountType::class, $user);

        // je recupere la request
        $form->handleRequest($request);

        // si le formulaire et ok et valide
        if($form->isSubmitted() && $form->isValid()){
            // enregistre en base de données
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'les données du profil on bien été enregistrer !'
            );
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * modifier le mot de passe
     * @Route("/account/password-update", name="account_password")
     * @isGranted("ROLE_USER")
     * @return response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager){

        // on creer un nouveau mot de passe
        $passwordUpdate = new PasswordUpdate();
        // on recupere l'utilisateur connecté
        $user = $this->getUser();
        // on creer le formulaire qui vien de != l'entité PasswordUpdate
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        // je recupere la request
        $form->handleRequest($request);
        // si le formulaire et ok et valide
        if($form->isSubmitted() && $form->isValid()){
            // verifié que le old password du formulaire soi le méme que l'utilisateur connécté
            // si le mot de passe entrer dans le formuliare n'est pas le méme que le mot de passe de l'utilisateur connecté
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getHash())){
                // gére l'erreur
                // on accede au champ oldPassword
                $oldPassword = $form->get('oldPassword');
                // on set avec la class FormError un message d'erreur sur le champs selectioné !
                $oldPassword->addError(new FormError('Le mot de passe que vous avez tapé n\'est pas votre mot de passe actuel'));
            }else{
                // je recupere le nouveau mot de passe entrer dans le formulaire
                $newPassword = $passwordUpdate->getNewPassword();
                // j'encode le nouveau password dans le $hash
                $hash = $encoder->encodePassword($user, $newPassword);
                // je modifie le mot de passe de l'utilisateur
                $user->setHash($hash);
                // j'enregistre en base de données
                $manager->persist($user);
                $manager->flush();
                // message addFlash
                $this->addFlash(
                    'success',
                    'votre mot de passe a bien été modifier'
                );
                // redirection
                return $this->redirectToRoute('homepage');
            }
        }
        return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * permet dafficher le profil de l'utilisateur connecté
     * @Route("/account", name="account_index")
     * @isGranted("ROLE_USER")
     * @return response
     */
    public function myAccount(){
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}
