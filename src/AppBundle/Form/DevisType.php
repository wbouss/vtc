<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nbPersonne', "choice" ,  array("choices" => array("1" => "1","2" => "2","3" => "3","4" => "4") ,  'preferred_choices' => array(0)))
                ->add('nbBagage', "choice" ,array("choices" => array("1" => "1","2" => "2","3" => "3") ,  'preferred_choices' => array(0)))
                ->add('datePrevu' , 'text')
                ->add('depart', "text")
                ->add('arrive' , 'text')
                ->add("submit",SubmitType::class);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Devis'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_devis';
    }


}
