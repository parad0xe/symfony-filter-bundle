<?php

namespace Parad0xe\Bundle\FilterBundle;

use Doctrine\ORM\QueryBuilder;
use Exception;
use http\Exception\RuntimeException;
use Parad0xe\Bundle\FilterBundle\Configuration\FilterConfiguration;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class AbstractFilter implements FilterInterface
{
    use FilterHelperTrait {
        FilterHelperTrait::__initTrait as protected __initFilterHelperTrait;
    }

    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @var FilterViewBuilder
     */
    protected static $view_builder;

    /**
     * @var QueryBuilder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var FilterConfiguration
     */
    protected $configuration;

    /**
     * @var FilterParameters
     */
    protected $parameters;

    /**
     * - use {id} : replace with entity hash (Optional) (use if many filter type in same page)
     * - use {method} : replace with filter method to use (Required)
     *
     * @return string
     * @example return "filter-{id}-{method}
     *
     */
    public function getPattern(): string
    {
        return $this->configuration->getViewOptions()->getDefaultRenderPattern();
    }

    /**
     * @return FilterViewBuilder
     * @throws Exception
     */
    public static function view(): FilterViewBuilder
    {
        return static::$view_builder;
    }

    /**
     * @param QueryBuilder $builder
     * @param ContainerInterface $container
     * @return QueryBuilder
     * @throws Exception
     */
    public static function filter(QueryBuilder $builder, ContainerInterface $container): QueryBuilder
    {
        return (new static($builder, $container))->getQueryBuilder();
    }

    /**
     * @return FilterConfiguration
     */
    public function getConfiguration(): FilterConfiguration {
        return $this->configuration;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->builder;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request {
        return $this->request;
    }

    /**
     * @return Session
     */
    public function getSession(): Session {
        return $this->session;
    }

    /**
     * @return FilterParameters
     */
    public function getParameters(): FilterParameters {
        return $this->parameters;
    }

    /**
     * AbstractFilter constructor.
     * @param QueryBuilder $builder
     * @param ContainerInterface $container
     * @throws ReflectionException
     * @throws Exception
     */
    public function __construct(QueryBuilder $builder, ContainerInterface $container)
    {
        try {
            $this->request = $container->get("request_stack")->getCurrentRequest();
            $this->session = $container->get("session");
        } catch (Exception $e) {
            throw new RuntimeException("An error is occured");
        }

        $this->__initFilterHelperTrait($this);

        $this->configuration = new FilterConfiguration($container);
        $this->parameters = new FilterParameters($this);

        self::$container = $container;
        self::$view_builder = new FilterViewBuilder($this);

        $this->builder = $builder;
        $this->alias = $this->builder->getAllAliases()[0];

        $this->_handleCleanningFilter();

        if(!$this->configuration->getStoreOptions()->withCache()) {
            $this->parameters->storeParameters([]);
        }

        $parameters = $this->parameters->getRequestedParameters();

        foreach ($parameters as $parameter_key => $transact_key) {
            $filter_method = $this->_extractRealMethodFromParameterKey($parameter_key);
            $filter_value = $parameters[$parameter_key];

            if(
                !method_exists($this, $filter_method)
                || !$this->_isPublicFilterMethod($filter_method)
            ) {
                continue;
            }

            $this->_callFilterMethod($filter_method, $filter_value, $transact_key);
        }

        $this->parameters->storeParameters($parameters);

        return $this->builder;
    }

    /**
     * @param string $parameter_key
     * @return string
     */
    private function _extractRealMethodFromParameterKey(string $parameter_key): string {
        $pattern = $this->parsePattern($this->getDefaultMethodNamePattern());
        $pattern = "/$pattern/";

        $matches = [];
        preg_match($pattern, $parameter_key, $matches);

        if(count($matches) >= 2) {
            return $this->configuration->getClassOptions()->getAvailableMethodsPrefix() . $this->toCamelCase($matches[1]);
        }

        return "";
    }

    private function _handleCleanningFilter(): void {
        if($this->parameters->getParameterBag()->has($this->configuration->getStoreOptions()->getCleanerkey())) {
            $this->parameters->clean();
        }
    }

    /**
     * @param $filter_method
     * @param $filter_value
     * @param null $transact_key
     * @throws ReflectionException
     */
    private function _callFilterMethod($filter_method, $filter_value, $transact_key = null): void {
        $fn_reflection = new ReflectionMethod($this, $filter_method);

        if(!empty($filter_value) || $filter_value === "0") {
            if(!is_array($filter_value)) {
                $filter_value = trim($filter_value);
            }

            if(is_array($filter_value) && $fn_reflection->getParameters()[0]->getType() == "string"){
                $filter_value = $filter_value[0];
            } elseif(!is_array($filter_value) && $fn_reflection->getParameters()[0]->getType() == "array") {
                $filter_value = [$filter_value];
            }

            if(!is_null($transact_key)) {
                if($fn_reflection->getNumberOfParameters() == 2) {
                    $this->{$filter_method}($filter_value, $transact_key);
                    return;
                }
            }

            $this->{$filter_method}($filter_value);
        }
    }

    /**
     * @param string $filter_method
     * @return bool
     */
    private function _isPublicFilterMethod(string $filter_method): bool {
        return str_starts_with($filter_method, $this->configuration->getClassOptions()->getAvailableMethodsPrefix());
    }

    public function __toString(): string
    {
        return get_class($this);
    }
}
