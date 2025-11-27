<?php

namespace App\Form;

use App\Entity\PersPhys;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecapForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('npi', TextType::class)
            ->add('numCNSS', HiddenType::class)
            ->add('mat_pers', TextType::class)
            ->add('nom_pers', TextType::class)
            ->add('pnom_pers', TextType::class)
            ->add('dateNaiss', DateType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PersPhys::class,
        ]);
    }
}