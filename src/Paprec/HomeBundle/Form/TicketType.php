<?php

namespace Paprec\HomeBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    var $options;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;

        $builder
            ->add('title', TextType::class, array(
                'required' => true
            ))
            ->add('level', ChoiceType::class, array(
                'required' => true,
                'choices' => $this->options['levels']
            ))
            ->add('status', ChoiceType::class, array(
                'required' => true,
                'choices' => $this->options['status']
            ))
            ->add('invoiceStatus', ChoiceType::class, array(
                'required' => true,
                'choices' => $this->options['invoiceStatus']
            ))
            ->add('content', CKEditorType::class, array(
                'config_name' => 'basic_config',
                'required' => true
            ))
            ->add('ticketFile', FileType::class, array(
                "required" => false,
                'data_class' => null,
                'mapped' => false,
                'multiple' => true,
            ))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\HomeBundle\Entity\Ticket',
            'status' => null,
            'invoiceStatus' => null,
            'levels' => null
        ));
    }

}
