<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RefusForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('motif', TextareaType::class, [
            'label' => 'Motif du rejet',
            'required' => true,
            'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Exemple : Informations manquantes ou incohérentes...',
                    'rows' => 6,        
                    'style' => 'resize: vertical;' 
                ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
