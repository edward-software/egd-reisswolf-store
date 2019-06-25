<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CatalogBundle\Entity\Argument;
use Paprec\CatalogBundle\Entity\PriceListD3E;
use Paprec\CatalogBundle\Repository\ArgumentRepository;
use Paprec\CatalogBundle\Repository\PriceListD3ERepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductD3EPackageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('subName')
            ->add('description', TextareaType::class)
            ->add('reference')
            ->add('dimensions', TextareaType::class)
            ->add('position')
            ->add('availablePostalCodes', TextareaType::class)
            ->add('isDisplayed', ChoiceType::class, array(
                "choices" => array(
                    'Non' => 0,
                    'Oui' => 1
                ),
                "expanded" => true
            ))
            ->add('arguments', EntityType::class, array(
                'class' => Argument::class,
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (ArgumentRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.deleted IS NULL');
                }
            ))
            ->add('packageUnitPrice', TextType::class, array(
                "required" => true
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CatalogBundle\Entity\ProductD3E',
            'validation_groups' => ['package'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_productd3e_package';
    }


}
