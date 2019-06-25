<?php

namespace Paprec\CommercialBundle\Form\ProductChantierOrder;

use Paprec\CommercialBundle\Entity\BusinessLine;
use Paprec\CommercialBundle\Repository\BusinessLineRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChantierOrderType extends AbstractType
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
                'choice_label' => 'name',
                'query_builder' => function (BusinessLineRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->where('b.deleted IS NULL')
                        ->andWhere('b.division = \'CHANTIER\'');
                }
            ))
            ->add('civility', ChoiceType::class, array(
                'choices' => array(
                    'M' => 'M',
                    'Mme' => 'Mme',
                ),
                'expanded' => true
            ))
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('function', TextType::class, array(
                'required' => false
            ))
            ->add('email', TextType::class)
            ->add('address', TextareaType::class)
            ->add('postalCode', TextType::class)
            ->add('city', TextType::class)
            ->add('headofficeAddress', TextType::class, array(
                'required' => false
            ))
            ->add('headofficePostalCode', TextType::class, array(
                'required' => false
            ))
            ->add('headofficeCity', TextType::class, array(
                'required' => false
            ))
            ->add('invoicingAddress', TextType::class, array(
                'required' => false
            ))
            ->add('invoicingPostalCode', TextType::class, array(
                'required' => false
            ))
            ->add('invoicingCity', TextType::class, array(
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
            ->add('phone', TextType::class)
            ->add('orderStatus', ChoiceType::class, array(
                "choices" => $options['status'],
                "choice_label" => function ($choiceValue, $key, $value) {
                    return 'Commercial.OrderStatusList.' . $choiceValue;
                }
            ))
            ->add('associatedInvoice', FileType::class, array(
                'multiple' => false,
                'data_class' => null
            ))
            ->add('paymentMethod', ChoiceType::class, array(
                "choices" => $options['paymentMethods']
            ))
            ->add('installationDate', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('removalDate', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('domainType', ChoiceType::class, array(
                'choices' => array(
                    'Matériel présent sur le domaine privé' => 'private',
                    'Matériel présent sur le domain public' => 'public'
                )
            ))
            ->add('accessConditions', TextareaType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductChantierOrder',
            'status' => null,
            'paymentMethods' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_productchantierorder';
    }


}
