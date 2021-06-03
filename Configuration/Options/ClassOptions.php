<?php


namespace Parad0xe\Bundle\FilterBundle\Configuration\Options;


class ClassOptions
{
    /**
     * @var string
     */
    private $available_methods_prefix;

    public function __construct($options)
    {
        $this->available_methods_prefix = $options->available_methods_prefix;
    }

    /**
     * @return string
     */
    public function getAvailableMethodsPrefix(): string
    {
        return $this->available_methods_prefix;
    }
}
