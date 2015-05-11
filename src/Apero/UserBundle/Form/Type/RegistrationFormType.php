<?php

namespace Apero\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('gender', 'choice', array(
            'label' => 'Genre',
            'choices' => array(1 => 'Homme', 0 => 'Femme'),
            'expanded' => true,
            'mapped' => true,
        ));
    }

    public function getName()
    {
        return 'apero_user_registration';
    }
}