<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CatalogBundle\Entity\Argument;
use Paprec\CatalogBundle\Repository\ArgumentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class ProductLabelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;

        $builder
            ->add('name')
            ->add('shortDescription', TextareaType::class)
            ->add('version')
            ->add('lockType')
            ->add('language', ChoiceType::class, array(
                'choices' => $options['languages'],
                'data' => $options['language']
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CatalogBundle\Entity\ProductLabel',
            'languages' => null,
            'language' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_product_label';
    }


}
