<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TarifType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('service',  ChoiceType::class,array(
                        'choices'=>array(   'Chosir un Document...'=>'0',
                                            'Avis de Banque'=>'Avis de Banque',
                                            'Contrats'=>'Contrats',
                                            'Documents Salarié'=>'Documents Salarié',
                                            'Factures'=>'Factures',
                                            'Relevés de Compte'=>'Relevés de Compte',
                                         )))
                ->add('prixunitaire');
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Tarif'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_tarif';
    }


}
