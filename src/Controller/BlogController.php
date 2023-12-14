<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blog')]
class BlogController extends AbstractController
{
    // Action pour afficher la liste des articles du blog.
    #[Route('/', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'], name: 'blog_index')]
    #[Route('/rss.xml', defaults: ['page' => '1', '_format' => 'xml'], methods: ['GET'], name: 'blog_rss')]
    #[Route('/page/{page<[1-9]\d*>}', defaults: ['_format' => 'html'], methods: ['GET'], name: 'blog_index_paginated')]
    #[Cache(smaxage: 10)]
    public function index(Request $request, int $page, string $_format, PostRepository $posts, TagRepository $tags): Response
    {
        // Récupération facultative du tag depuis la requête.
        $tag = null;
        if ($request->query->has('tag')) {
            $tag = $tags->findOneBy(['name' => $request->query->get('tag')]);
        }

        // Récupération des derniers articles paginés.
        $latestPosts = $posts->findLatest($page, $tag);

        // Rendu de la vue avec les articles et le tag en option.
        return $this->render('blog/index.' . $_format . '.twig', [
            'paginator' => $latestPosts,
            'tagName' => $tag?->getName(),
        ]);
    }

    // Action pour afficher un article du blog.
    #[Route('/posts/{slug}', methods: ['GET'], name: 'blog_post')]
    public function postShow(Post $post): Response
    {
        // Rendu de la vue de l'article.
        return $this->render('blog/post_show.html.twig', ['post' => $post]);
    }

    // Action pour créer un nouveau commentaire.
    #[Route('/comment/{postSlug}/new', methods: ['POST'], name: 'comment_new')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[ParamConverter('post', options: ['mapping' => ['postSlug' => 'slug']])]
    public function commentNew(Request $request, Post $post, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager): Response
    {
        // Création d'une instance de Comment.
        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $post->addComment($comment);

        // Création du formulaire de commentaire.
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        // Validation et enregistrement du commentaire s'il est valide.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            // Déclenchement d'un événement pour le nouveau commentaire.
            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

            // Redirection vers la page de l'article.
            return $this->redirectToRoute('blog_post', ['slug' => $post->getSlug()]);
        }

        // Rendu d'une vue en cas d'erreur de formulaire.
        return $this->render('blog/comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    // Action pour afficher le formulaire de commentaire.
    public function commentForm(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('blog/_comment_form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    // Action pour effectuer une recherche d'articles.
    #[Route('/search', methods: ['GET'], name: 'blog_search')]
    public function search(Request $request, PostRepository $posts): Response
    {
        $query = $request->query->get('q', '');
        $limit = $request->query->get('l', 10);

        // Vérifie si la requête n'est pas une requête Ajax.
        if (!$request->isXmlHttpRequest()) {
            return $this->render('blog/search.html.twig', ['query' => $query]);
        }

        // Recherche d'articles correspondant à la requête.
        $foundPosts = $posts->findBySearchQuery($query, $limit);

        // Formatage des résultats en JSON.
        $results = [];
        foreach ($foundPosts as $post) {
            $results[] = [
                'title' => htmlspecialchars($post->getTitle(), \ENT_COMPAT | \ENT_HTML5),
                'date' => $post->getPublishedAt()->format('M d, Y'),
                'author' => htmlspecialchars($post->getAuthor()->getFullName(), \ENT_COMPAT | \ENT_HTML5),
                'summary' => htmlspecialchars($post->getSummary(), \ENT_COMPAT | \ENT_HTML5),
                'url' => $this->generateUrl('blog_post', ['slug' => $post->getSlug()]),
            ];
        }

        // Renvoi des résultats au format JSON.
        return $this->json($results);
    }
}