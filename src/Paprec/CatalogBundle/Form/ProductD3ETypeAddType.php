<?php
/**
 * Created by PhpStorm.
 * User: flaine
 * Date: 2019-03-06
 * Time: 16:41
 */

namespace Paprec\CatalogBundle\Form;


use Paprec\CatalogBundle\Repository\TypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductD3ETypeAddType extends AbstractType
{
    private $productId;
    private $productD3ETypeRepo;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->productId = $options['productId'];
        $this->productD3ETypeRepo = $options['productD3ETypeRepo'];

        $builder
            ->add('unitPrice', TextType::class, array(
                "required" => true
            ))
            ->add('type', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:Type',
                'query_builder' => function (TypeRepository $er) {
                    $subQueryBuilder = $this->productD3ETypeRepo->createQueryBuilder('pc');
                    $subQuery = $subQueryBuilder->select(array('IDENTITY(pc.type)'))
                        ->innerJoin('PaprecCatalogBundle:ProductD3E', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pc.productD3E')
                        ->andWhere('p.id = :productId');

                    $queryBuilder = $er->createQueryBuilder('t');
                    $queryBuilder
                        ->where($queryBuilder->expr()->notIn('t.id', $subQuery->getDQL()))
                        ->andWhere('t.deleted is NULL')
                        ->distinct()
                        ->orderBy('t.name', 'ASC')
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
            'data_class' => 'Paprec\CatalogBundle\Entity\ProductD3EType',
            'productId' => null,
            'productD3ETypeRepo' => 'Paprec\CatalogBundle\Repository\ProductD3ETypeRepository'

        ));
    }
}
