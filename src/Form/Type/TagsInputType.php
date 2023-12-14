<?php

namespace App\Form\Type;

use App\Form\DataTransformer\TagArrayToStringTransformer;
use App\Repository\TagRepository;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TagsInputType extends AbstractType
{
    public function __construct(
        private TagRepository $tags
    ) {
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new CollectionToArrayTransformer(), true) // Transforme la collection en tableau.
            ->addModelTransformer(new TagArrayToStringTransformer($this->tags), true) // Transforme le tableau de tags en une chaîne de caractères.
        ;
    }


    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['tags'] = $this->tags->findAll(); // Récupère la liste de tous les tags pour la vue.
    }


    public function getParent(): ?string
    {
        return TextType::class; // Hérite du type TextType de base pour l'entrée de texte.
    }
}





