<?php

namespace Paprec\CommercialBundle\Form\CallBack;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CallBackShortType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('businessName')
            ->add('civility', ChoiceType::class, array(
                'choices' => array(
                    'Monsieur' => 'M',
                    'Madame' => 'Mme',
                ),
                'choice_attr' => function () {
                    return ['class' => 'input__radio'];
                },
                'expanded' => true
            ))
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class, array(
                'required' => false
            ))
            ->add('email', TextType::class)
            ->add('phone', TextType::class)
            ->add('function', TextType::class)
            ->add('dateCallBack', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
            ))
            ->add('timeCallBack', TimeType::class, array(
                'widget' => 'single_text',
                'input' => 'string',
                'html5' => false
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\CallBack'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_callBack';
    }


}
