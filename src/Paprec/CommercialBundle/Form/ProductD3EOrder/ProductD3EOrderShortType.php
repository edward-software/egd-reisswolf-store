<?php

namespace Paprec\CommercialBundle\Form\ProductD3EOrder;

use Paprec\CommercialBundle\Entity\BusinessLine;
use Paprec\CommercialBundle\Repository\BusinessLineRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class ProductD3EOrderShortType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('businessName')
            ->add('businessLine', EntityType::class, array(
                'class' => BusinessLine::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'Commercial.ProductD3EQuote.BusinessLinePlaceholder',
                'empty_data' => null,
                'choice_label' => 'name',
                'query_builder' => function (BusinessLineRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->where('b.deleted IS NULL')
                        ->andWhere('b.division = \'D3E\'');
                }
            ))
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
            ->add('firstName', TextType::class)
            ->add('email', TextType::class)
            ->add('phone', TextType::class)
            ->add('function', TextType::class, array(
                'required' => false
            ))
            ->add('siret', TextType::class)
            ->add('tvaStatus', ChoiceType::class, array(
                'choices' => array(
                    'Numéro de TVA intracommunautaire' => 'intracom',
                    'Franchise en base de TVA - Article 293 B' => 'franchise',
                ),
                'choice_attr' => function () {
                    return ['class' => 'input__radio input__radio--short'];
                },
                'expanded' => true
            ))
            ->add('tvaNumber', TextType::class)
            ->add('address', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('city', TextType::class)
            ->add('invoicingAddress', TextType::class, array(
                'required' => false
            ))
            ->add('invoicingPostalCode', TextType::class, array(
                'required' => false
            ))
            ->add('invoicingCity', TextType::class, array(
                'required' => false
            ))
            ->add('headofficeAddress', TextType::class, array(
                'required' => false
            ))
            ->add('headofficePostalCode', TextType::class, array(
                'required' => false
            ))
            ->add('headofficeCity', TextType::class, array(
                'required' => false
            ))
            ->add('preferredContact', ChoiceType::class, array(
                'choices' => array(
                    'Téléphone' => 'phone',
                    'e-mail' => 'email',
                ),
                'choice_attr' => function () {
                    return ['class' => 'input__radio input__radio--short'];
                },
                'expanded' => true
            ));

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductD3EOrder'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_productd3eordershort';
    }


}
