<?php


namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Configuration du formulaire pour la classe Comment.
        $builder
            ->add('content', TextareaType::class, [
                'help' => 'help.comment_content', // Affiche un message d'aide pour le champ content.
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class, // Configure la classe de données associée au formulaire comme Comment.
        ]);
    }
}
