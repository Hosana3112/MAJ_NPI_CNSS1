<?php

namespace App\Form;

use App\Entity\PersPhys;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersPhysForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('npi')
            ->add('numCNSS')
            ->add('mat_pers')
            ->add('nom_pers')
            ->add('pnom_pers')
            ->add('dateNaiss')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PersPhys::class,
        ]);
    }
}
