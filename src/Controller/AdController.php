<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Entity\Image;
use App\Repository\AdRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo)
    {
        // $repo = $this->getDoctrine()->getRepository(Ad::class);
        $ads = $repo->findAll();

        return $this->render('ad/index.html.twig', [
            'controller_name' => 'AdController',
            'ads' => $ads
        ]);
    }

    /**
     * permet de creer une annonce
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     * @Route("/ads/new", name="ads_create")
     */
    public function create(Request $request, EntityManagerInterface $manager){

        // on creer une nouvelle annonce
        $ad = new Ad();
        // on creer le formulaire avec le formbuilder
        $form = $this->createForm(AdType::class, $ad);
        // on recuperer la request du formulaire
        $form->handleRequest($request);

        // si le formulaire est soumis et valide
        if($form->isSubmitted() && $form->isValid()){

            // boucle enregistrement en base de données des images suplementaire
            foreach($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }

            // on set l'utilisateur dans l'annonce
            $ad->setAuthor($this->getUser());

            // on demande au manager d'entité de persister et d'enregistrer en base de données
            $manager->persist($ad);
            $manager->flush();

            // on creer un message flash de success
            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !"
            );

            // on retourne une redirection
            return $this->redirectToRoute('ads_show', [
                // on passe le slug dans la redirection ce qui permet d'aller directement sur l'annonce creer
                'slug' => $ad->getSlug()
            ]);
        }
        
        // on retourn le rendu de la route
        return $this->render('ad/new.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * permet d'afficher le formulaire d'edition
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="cette annonce ne vous appartient pas vous ne pouvez pas la modifier")
     * @return void
     */
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager){
        
        // on creer le formulaire avec le formbuilder
        $form = $this->createForm(AdType::class, $ad);
        // on recuperer la request du formulaire
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            // boucle enregistrement en base de données des images suplementaire
            foreach($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }

            // on demande au manager d'entité de persister et d'enregistrer en base de données
            $manager->persist($ad);
            $manager->flush();

            // on creer un message flash de success
            $this->addFlash(
                'success',
                "Les modifications de l'annonce on bien etait modifier !"
            );

            // on retourne une redirection
            return $this->redirectToRoute('ads_show', [
                // on passe le slug dans la redirection ce qui permet d'aller directement sur l'annonce creer
                'slug' => $ad->getSlug()
            ]);
        }
        

        return $this->render("ad/edit.html.twig",[
            'form' => $form->createView(),
            'ad' => $ad
        ]);
    }




    /**
     * permet d'afficher une seul annonce
     *
     * @Route("/ads/{slug}", name="ads_show")
     * @return Response
     */
    public function show($slug, Ad $ad){
        // je recupere l'annonce qui corespond au slug 
        // $ad = $repo->findOneBySlug($slug);

        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }

    /**
     * permet de supprimer une annonce
     * @Route("ads/{slug}/delete", name="ads_delete")
     * @security("is_granted('ROLE_USER') and user == ad.getAuthor()", message="Vous n'avez pas le droit d'acceder a cette resource")
     * @return response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager ){
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
            'success',
            "l'annonce {$ad->getTitle()} a bien été supprimé !"
        );

        return $this->redirectToRoute("ads_index");
    }

}
