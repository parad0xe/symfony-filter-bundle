<?php


namespace Parad0xe\Bundle\FilterBundle\Configuration;


use Parad0xe\Bundle\FilterBundle\Configuration\Options\ClassOptions;
use Parad0xe\Bundle\FilterBundle\Configuration\Options\StoreOptions;
use Parad0xe\Bundle\FilterBundle\Configuration\Options\ViewOptions;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterConfiguration
{
    /**
     * @var StoreOptions
     */
    private $store_options;

    /**
     * @var ClassOptions
     */
    private $class_options;

    /**
     * @var ViewOptions
     */
    private $view_options;

    public function __construct(ContainerInterface $container)
    {
        $config = json_decode(json_encode($container->getParameter("filter.config")), false);

        $this->store_options = new StoreOptions($config->store_options);
        $this->class_options = new ClassOptions($config->class_options);
        $this->view_options = new ViewOptions($config->view_options);
    }

    /**
     * @return StoreOptions
     */
    public function getStoreOptions(): StoreOptions
    {
        return $this->store_options;
    }

    /**
     * @return ClassOptions
     */
    public function getClassOptions(): ClassOptions
    {
        return $this->class_options;
    }

    /**
     * @return ViewOptions
     */
    public function getViewOptions(): ViewOptions
    {
        return $this->view_options;
    }
}
