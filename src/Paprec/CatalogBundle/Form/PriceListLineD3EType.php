<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CommercialBundle\Entity\Agency;
use Paprec\CommercialBundle\Repository\AgencyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceListLineD3EType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('postalCodes', TextareaType::class)
            ->add('agency', EntityType::class, array(
                'class' => Agency::class,
                'multiple' => false,
                'expanded' => false,
                'choice_label' => function ($category) {
                    return $category->getName() . ' - ' . $category->getCity();
                },
                'query_builder' => function (AgencyRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.deleted IS NULL')
                        ->andWhere('a.divisions LIKE \'%D3E%\'');
                }
            ))
            ->add('minQuantity')
            ->add('maxQuantity', IntegerType::class, array(
                'required' => false
            ))
            ->add('price', TextType::class, array(
                'required' => true
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CatalogBundle\Entity\PriceListLineD3E'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_pricelistlined3e';
    }


}
