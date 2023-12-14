<?php

namespace App\Controller;

use App\Form\Type\ChangePasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile'), IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    // Action pour éditer le profil de l'utilisateur.
    #[Route('/edit', methods: ['GET', 'POST'], name: 'user_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'utilisateur actuellement connecté.
        $user = $this->getUser();

        // Création du formulaire de modification du profil.
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, met à jour les informations de l'utilisateur.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('user_edit');
        }

        // Affiche le formulaire de modification du profil.
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    // Action pour permettre à l'utilisateur de changer son mot de passe.
    #[Route('/change-password', methods: ['GET', 'POST'], name: 'user_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'utilisateur actuellement connecté.
        $user = $this->getUser();

        // Création du formulaire de changement de mot de passe.
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, met à jour le mot de passe de l'utilisateur.
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('newPassword')->getData()));
            $entityManager->flush();

            // Redirige l'utilisateur vers la page de déconnexion pour qu'il puisse se reconnecter avec son nouveau mot de passe.
            return $this->redirectToRoute('security_logout');
        }

        // Affiche le formulaire de changement de mot de passe.
        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}