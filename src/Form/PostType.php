<?php


namespace App\Form;

use App\Entity\Post;
use App\Form\Type\DateTimePickerType;
use App\Form\Type\TagsInputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostType extends AbstractType
{
    public function __construct(
        private SluggerInterface $slugger
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Configuration du formulaire pour la classe Post.
        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.title', // Étiquette du champ "title".
            ])
            ->add('summary', TextareaType::class, [
                'help' => 'help.post_summary', // Message d'aide pour le champ "summary".
                'label' => 'label.summary', // Étiquette du champ "summary".
            ])
            ->add('content', null, [
                'attr' => ['rows' => 20],
                'help' => 'help.post_content', // Message d'aide pour le champ "content".
                'label' => 'label.content', // Étiquette du champ "content".
            ])
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'label.published_at', // Étiquette du champ "publishedAt".
                'help' => 'help.post_publication', // Message d'aide pour le champ "publishedAt".
            ])
            ->add('tags', TagsInputType::class, [
                'label' => 'label.tags', // Étiquette du champ "tags".
                'required' => false, // Champ facultatif.
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Post */
                $post = $event->getData();
                if (null === $post->getSlug() && null !== $post->getTitle()) {
                    // Génère un slug basé sur le titre du post si le slug est nul.
                    $post->setSlug($this->slugger->slug($post->getTitle())->lower());
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class, // Configure la classe de données associée au formulaire comme Post.
        ]);
    }
}