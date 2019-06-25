<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 04/12/2018
 * Time: 17:15
 */

namespace Paprec\CatalogBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NumberManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * @param $amount , montant en centime
     * @param $currency , si null on n'affiche pas le sympbole, sinon on affiche le symbole, si currency = PERCENTAGE on affiche %
     * @param $locale
     * @return string
     */
    public function formatAmount($amount, $currency, $locale)
    {
        if ($currency) {
            if ($currency == 'PERCENTAGE') {
                $fmt = numfmt_create($locale, \NumberFormatter::PERCENT);
                $fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);

                return numfmt_format($fmt, $amount / 100 / 100);
            }

            return twig_localized_currency_filter($amount / 100, $currency, $locale);
        }

        $fmt = numfmt_create($locale, \NumberFormatter::DECIMAL);
        $fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);

        return numfmt_format($fmt, $this->denormalize($amount));
    }

    /**
     * Prend un number en paramètre et retourne sa valeur divisée par 100
     * Pour récupérer les nombres stockés en base et les afficher
     *
     * @param $value
     */
    public function denormalize($value)
    {
        if ($value == null || $value == '') {
            return null;
        }
        return $value / 100;
    }


    /**
     * Prend un string en paramètre, le transforme en nombre et le multplie par 100
     * Pour stocker les nombres en base
     *
     * @param $value
     */
    public function normalize($value)
    {
        if ($value == null || $value == '') {
            return null;
        }
        $value = str_replace(',', '.', $value);
        return round($value * 100);
    }

    public function formatId($id, $padlength, $padstring = 0, $pad_type = STR_PAD_LEFT)
    {
        return str_pad($id, $padlength, $padstring, $pad_type);
    }

}
