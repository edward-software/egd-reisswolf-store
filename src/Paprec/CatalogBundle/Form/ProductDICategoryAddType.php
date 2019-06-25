<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CatalogBundle\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDICategoryAddType extends AbstractType
{
    private $productId;
    private $productDICategoryRepo;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->productId = $options['productId'];
        $this->productDICategoryRepo = $options['productDICategoryRepo'];

        $builder
            ->add('unitPrice', TextType::class, array(
                "required" => true
            ))
            ->add('category', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:Category',
                'query_builder' => function (CategoryRepository $er) {
                    $subQueryBuilder = $this->productDICategoryRepo->createQueryBuilder('pc');
                    $subQuery = $subQueryBuilder->select(array('IDENTITY(pc.category)'))
                        ->innerJoin('PaprecCatalogBundle:ProductDI', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pc.productDI')
                        ->andWhere('p.id = :productId');

                    $queryBuilder = $er->createQueryBuilder('c');
                    $queryBuilder
                        ->where($queryBuilder->expr()->notIn('c.id', $subQuery->getDQL()))
                        ->andWhere('c.division = \'DI\'')
                        ->andWhere('c.deleted is NULL')
                        ->distinct()
                        ->orderBy('c.name', 'ASC')
                        ->setParameter('productId', $this->productId);
                    return $queryBuilder;
                },
                'placeholder' => '',
                'empty_data' => null
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CatalogBundle\Entity\ProductDICategory',
            'productId' => null,
            'productDICategoryRepo' => 'Paprec\CatalogBundle\Repository\ProductDICategoryRepository'

        ));
    }
}
