<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 13/11/2018
 * Time: 12:27
 */

namespace Paprec\CatalogBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use Exception;
use Paprec\CatalogBundle\Entity\Category;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CategoryManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($category)
    {
        $id = $category;
        if ($category instanceof Category) {
            $id = $category->getId();
        }
        try {

            $category = $this->em->getRepository('PaprecCatalogBundle:Category')->find($id);

            if ($category === null || $this->isDeleted($category)) {
                throw new EntityNotFoundException('categoryNotFound');
            }

            return $category;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la catégorie n'est pas supprimée
     *
     * @param Category $category
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(Category $category, $throwException = false)
    {
        $now = new \DateTime();

        if ($category->getDeleted() !== null && $category->getDeleted() instanceof \DateTime && $category->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('categoryNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * Jointure sur ProductDICategory comme ça on est sur de renvoyer uniquement les catégories qui ont des produits
     * @return mixed
     */
    public function getCategoriesDI()
    {
        try {
            $query = $this->em
                ->getRepository(Category::class)
                ->createQueryBuilder('c')
                ->innerJoin('PaprecCatalogBundle:ProductDICategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.id = pc.category')
                ->where('c.division = \'DI\'')
                ->distinct()
                ->orderBy('c.position', 'ASC');

            return $query->getQuery()->getResult();


        } catch (ORMException $e) {
            throw new Exception('unableToGetCategoriesDI', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Jointure sur ProductChantierCategory comme ça on est sur de renvoyer uniquement les catégories qui ont des produits
     * @return mixed
     */
    public function getCategoriesChantier($type)
    {
        try {

            if ($type == 'order') {
                $query = $this->em
                    ->getRepository(Category::class)
                    ->createQueryBuilder('c')
                    ->innerJoin('PaprecCatalogBundle:ProductChantierCategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.id = pc.category')
                    ->innerJoin('PaprecCatalogBundle:ProductChantier', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pc.productChantier')
                    ->where('c.division = \'Chantier\'')
                    ->distinct()
                    ->orderBy('c.position', 'ASC');

            } else {
                $query = $this->em
                    ->getRepository(Category::class)
                    ->createQueryBuilder('c')
                    ->innerJoin('PaprecCatalogBundle:ProductChantierCategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.id = pc.category')
                    ->where('c.division = \'Chantier\'')
                    ->distinct()
                    ->orderBy('c.position', 'ASC');
            }

            return $query->getQuery()->getResult();


        } catch (ORMException $e) {
            throw new Exception('unableToGetCategoriesChantier', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
