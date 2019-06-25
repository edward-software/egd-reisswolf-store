<?php

namespace Paprec\CommercialBundle\Form\Agency;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgenceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('divisions', ChoiceType::class, array(
                "choices" => $options['divisions'],
                "expanded" => true,
                "multiple" => true
            ))
            ->add('address', TextareaType::class, array(
                'attr' => array(
                    'readonly' => true,
                ),
            ))
            ->add('postalCode', TextType::class, array(
                'attr' => array(
                    'readonly' => true,
                ),
            ))
            ->add('city', TextType::class, array(
                'attr' => array(
                    'readonly' => true,
                ),
            ))
            ->add('phoneNumber')
            ->add('latitude', NumberType::class, array(
                'attr' => array(
                    'readonly' => true,
                ),
            ))
            ->add('longitude', NumberType::class, array(
                'attr' => array(
                    'readonly' => true,
                ),
            ))
            ->add('isDisplayed', ChoiceType::class, array(
                "choices" => array(
                    'Non' => 0,
                    'Oui' => 1
                ),
                "expanded" => true
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\Agency',
            'divisions' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_agence';
    }


}
