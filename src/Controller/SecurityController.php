<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SecurityController extends AbstractController
{
    use TargetPathTrait;

    // Action pour afficher le formulaire de connexion.
    #[Route('/login', name: 'security_login')]
    public function login(Request $request, AuthenticationUtils $helper): Response
    {
        // Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil.
        if ($this->getUser()) {
            return $this->redirectToRoute('blog_index');
        }

        // Sauvegarde de la page de destination pour la redirection après la connexion.
        $this->saveTargetPath($request->getSession(), 'main', $this->generateUrl('admin_index'));

        // Rendu du formulaire de connexion.
        return $this->render('security/login.html.twig', [
            'last_username' => $helper->getLastUsername(),
            'error' => $helper->getLastAuthenticationError(),
        ]);
    }

    // Action pour gérer la déconnexion de l'utilisateur.
    #[Route('/logout', name: 'security_logout')]
    public function logout(): void
    {
        // Cette méthode ne doit jamais être atteinte car la déconnexion est gérée automatiquement par Symfony.
        throw new \Exception('This should never be reached!');
    }
}