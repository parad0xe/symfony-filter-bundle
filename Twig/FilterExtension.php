<?php


namespace Parad0xe\Bundle\FilterBundle\Twig;


use Parad0xe\Bundle\FilterBundle\Twig\Parsers\FilterContextParser;
use Twig\Extension\AbstractExtension;

class FilterExtension extends AbstractExtension
{
    public function getTokenParsers()
    {
        return [
            new FilterContextParser
        ];
    }
}
