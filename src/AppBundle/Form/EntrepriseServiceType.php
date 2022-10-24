<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EntrepriseServiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//                ->add('droitinout',  ChoiceType::class,array(
//                        'choices'=>array(   'Client'=>'Client',
//                                            'Prestataire'=>'Prestataire',
//                                         ), 'expanded' => true,
//                                            'multiple' => false,))
//                ->add('droitguichet',  ChoiceType::class,array(
//                        'choices'=>array(   'Lister'=>'Lister',
//                                            'Ouvrir'=>'Ouvrir',
//                                         ), 'expanded' => true,
//                                            'multiple' => false,))
//                
                 ->add('stockage',  ChoiceType::class,array(
                        'choices'=>array(   '3 mois'=>'3 mois',
                                            '1 an'=>'1 an',
                                         ), 'expanded' => true,
                                            'multiple' => false,))
               ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\EntrepriseService'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_entrepriseservice';
    }


}
