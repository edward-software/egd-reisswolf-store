<?php

namespace Paprec\CommercialBundle\Form;

use Paprec\CommercialBundle\Entity\QuoteRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteRequestPublicType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('canton')
            ->add('businessName')
            ->add('civility', ChoiceType::class, array(
                'choices' => array(
                    'M' => 'M',
                    'Mme' => 'Mme',
                ),
                'expanded' => true
            ))
            ->add('access', ChoiceType::class, array(
                "choices" => $options['access'],
                "choice_label" => function ($choiceValue, $key, $value) {
                    return 'Commercial.AccessList.' . $choiceValue;
                },
                'empty_data' => null
            ))
            ->add('staff', ChoiceType::class, array(
                "choices" => $options['staff'],
                "choice_label" => function ($choiceValue, $key, $value) {
                    return 'Commercial.StaffList.' . $choiceValue;
                },
                'empty_data' => null
            ))
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('email', TextType::class)
            ->add('phone', TextType::class)
            ->add('isMultisite', ChoiceType::class, array(
                "choices" => array(0, 1),
                "choice_label" => function ($choiceValue, $key, $value) {
                    return 'General.' . $choiceValue;
                },
                "expanded" => true,
            ))
            ->add('address', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('city', TextType::class)
            ->add('comment', TextareaType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\QuoteRequest',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                if ($data->getIsMultisite() === 1) {
                    return ['public'];
                }
                return ['public', 'public_multisite'];
            },
            'access' => null,
            'staff' => null,
            'locale' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_quote_request_public';
    }


}
