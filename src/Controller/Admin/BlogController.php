<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Security\PostVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/post')]
#[IsGranted('ROLE_ADMIN')]
class BlogController extends AbstractController
{

    #[Route('/', methods: ['GET'], name: 'admin_index')]
    #[Route('/', methods: ['GET'], name: 'admin_post_index')]
    public function index(PostRepository $posts): Response
    {
        // Récupère les articles de l'auteur actuellement connecté, triés par date de publication décroissante.
        $authorPosts = $posts->findBy(['author' => $this->getUser()], ['publishedAt' => 'DESC']);

        return $this->render('admin/blog/index.html.twig', ['posts' => $authorPosts]);
    }


    #[Route('/new', methods: ['GET', 'POST'], name: 'admin_post_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée un nouvel article avec l'auteur actuellement connecté.
        $post = new Post();
        $post->setAuthor($this->getUser());

        // Crée un formulaire pour l'article.
        $form = $this->createForm(PostType::class, $post)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistre l'article dans la base de données.
            $entityManager->persist($post);
            $entityManager->flush();

            // Affiche un message flash pour indiquer que l'article a été créé avec succès.
            $this->addFlash('success', 'post.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_post_new');
            }

            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('admin/blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id<\d+>}', methods: ['GET'], name: 'admin_post_show')]
    public function show(Post $post): Response
    {
        // Vérifie si l'utilisateur actuel a le droit de voir l'article en utilisant PostVoter.
        $this->denyAccessUnlessGranted(PostVoter::SHOW, $post, 'Les articles ne peuvent être affichés qu\'à leurs auteurs.');

        return $this->render('admin/blog/show.html.twig', [
            'post' => $post,
        ]);
    }


    #[Route('/{id<\d+>}/edit', methods: ['GET', 'POST'], name: 'admin_post_edit')]
    #[IsGranted('edit', subject: 'post', message: 'Les articles ne peuvent être modifiés que par leurs auteurs.')]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Crée un formulaire pour la modification de l'article.
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistre les modifications de l'article dans la base de données.
            $entityManager->flush();

            // Affiche un message flash pour indiquer que l'article a été mis à jour avec succès.
            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute('admin_post_edit', ['id' => $post->getId()]);
        }

        return $this->render('admin/blog/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/delete', methods: ['POST'], name: 'admin_post_delete')]
    #[IsGranted('delete', subject: 'post')]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_post_index');
        }

        // Supprime les tags associés à l'article et ensuite supprime l'article lui-même de la base de données.
        $post->getTags()->clear();
        $entityManager->remove($post);
        $entityManager->flush();

        // Affiche un message flash pour indiquer que l'article a été supprimé avec succès.
        $this->addFlash('success', 'post.deleted_successfully');

        return $this->redirectToRoute('admin_post_index');
    }
}