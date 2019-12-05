<?php

namespace Paprec\CommercialBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Paprec\CatalogBundle\Entity\PostalCode;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PostalCodeToStringTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (postalCode) to a string .
     *
     * @param PostalCode|null $issue
     * @return string
     */
    public function transform($postalCode)
    {
        if (null === $postalCode) {
            return '';
        }

        return $postalCode->getCode();
    }

    /**
     * Transforms a string to an object (postalCode).
     *
     * @param string $postalCodeCode
     * @return PostalCode|null
     * @throws TransformationFailedException if object (postalCode) is not found.
     */
    public function reverseTransform($postalCodeCode)
    {
        // no issue number? It's optional, so that's ok
        if (!$postalCodeCode) {
            return;
        }

        $postalCode = $this->entityManager
            ->getRepository(PostalCode::class)->findOneBy(array(
                'code' => $postalCodeCode,
                'deleted' => null
            ));

        if (null === $postalCode) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An postal code with code "%s" does not exist!',
                $postalCodeCode
            ));
        }

        return $postalCode;
    }
}
