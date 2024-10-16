<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        ->add('name', TextType::class, [
            'label' => 'Nom de la société',
        ])
        ->add('siret', TextType::class, [
            'label' => 'Numéro SIRET',
        ])
        ->add('address', TextType::class, [
            'label' => 'Adresse',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => Company::class,
            
        ]);
    }
}
