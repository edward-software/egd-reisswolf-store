<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CatalogBundle\Entity\Region;
use Paprec\CatalogBundle\Repository\RegionRepository;
use Paprec\UserBundle\Entity\User;
use Paprec\UserBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostalCodeType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, array(
                "required" => true
            ))
            ->add('city', TextType::class, array(
                "required" => true
            ))
            ->add('zone', TextType::class, array(
                "required" => true
            ))
            ->add('setUpRate', TextType::class, array(
                "required" => true
            ))
            ->add('rentalRate', TextType::class, array(
                "required" => true
            ))
            ->add('transportRate', TextType::class, array(
                "required" => true
            ))
            ->add('treatmentRate', TextType::class, array(
                "required" => true
            ))
            ->add('traceabilityRate', TextType::class, array(
                "required" => true
            ))
            ->add('region', EntityType::class, array(
                'class' => Region::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'query_builder' => function (RegionRepository $rr) {
                    return $rr
                        ->createQueryBuilder('r')
                        ->where('r.deleted IS NULL')
                        ->orderBy('r.name');
                }
            ))
            ->add('userInCharge', EntityType::class, array(
                'class' => User::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'empty_data' => null,
                'choice_label' => function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'required' => false,
                'query_builder' => function (UserRepository $ur) {
                    return $ur->createQueryBuilder('u')
                        ->where('u.deleted IS NULL')
                        ->andWhere('u.roles LIKE \'%ROLE_COMMERCIAL%\'')
                        ->andWhere('u.enabled = 1')
                        ->orderBy('u.firstName');
                }
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CatalogBundle\Entity\PostalCode',
        ));
    }
}
