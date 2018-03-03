<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom' , null , array( "attr" => array( "placeholder" => "Nom")))
/*                ->add('prenom', null , array( "attr" => array( "placeholder" => "Prénom")))*/
                ->add('mail', EmailType::class , array( "attr" => array( "placeholder" => "Email")))
/*                ->add('numero', null , array( "attr" => array( "placeholder" => "Numéro de Téléphone(Optionnel)")))*/
                ->add('message', "textarea" , array( "attr" => array( "placeholder" => "Ecrivez votre message")))
                ->add('submit', "submit", [
                    'label' => 'Envoyer',

         ]);;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Contact'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_contact';
    }


}
