<?php

namespace Apero\EventBundle\Form;

use Symfony\Component\Form\AbstractType;
use Apero\EventBundle\Entity\Event;
use Apero\EventBundle\Entity\Bar;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('date', 'datetime')
            ->add('bar',  'entity', array(
                'class' => 'AperoEventBundle:Bar',
                'property' => 'nom',
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Apero\EventBundle\Entity\Event'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'apero_eventbundle_event';
    }
}
