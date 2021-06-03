<?php


namespace Parad0xe\Bundle\FilterBundle;


use Carbon\Carbon;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class FilterParameters
{
    /**
     * @var array
     */
    private $request_parameters;

    /**
     * @var array
     */
    private $session_parameters;

    /**
     * @var FilterInterface|AbstractFilter
     */
    private $filter;

    /**
     * @var InputBag|ParameterBag
     */
    private $paramaterBag;

    /**
     * FilterParameters constructor.
     * @param FilterInterface|AbstractFilter $filter
     */
    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;

        if($this->filter->getConfiguration()->getStoreOptions()->isGETMethod()) {
            $this->paramaterBag = $filter->getRequest()->query;
        } else {
            $this->paramaterBag = $filter->getRequest()->request;
        }

        $this->session_parameters = $this->retrieveStoredParameters();
        $this->request_parameters = $this->retrieveRequestParameters();

        foreach ($this->session_parameters as $session_key => $session_value) {
            if(
                is_array($session_value)
                && !empty($this->request_parameters)
                && !array_key_exists($session_key, $this->request_parameters)
            ) {
                unset($this->session_parameters[$session_key]);
            }
        }

        $this->handleAutoCleanTimeout();
    }

    /**
     * @return InputBag|ParameterBag
     */
    public function getParameterBag() {
        return $this->paramaterBag;
    }

    public function getRequestedParameters(): array {
        return array_merge(
            $this->session_parameters,
            $this->request_parameters
        );
    }

    public function storeParameters(array $parameters) {
        if($this->filter->getConfiguration()->getStoreOptions()->withCache() || empty($parameters)) {
            list($store_parameters, $scope, $scope_key) = $this->_getStore();

            $store_parameters[$scope_key] = $parameters;

            $this->filter->getSession()->set($this->filter->getConfiguration()->getStoreOptions()->getSessionkey(), $store_parameters);
        }
    }

    public function clean() {
        $this->paramaterBag->remove($this->filter->getConfiguration()->getStoreOptions()->getCleanerkey());

        $this->request_parameters = [];
        $this->paramaterBag->remove($this->filter->getConfiguration()->getStoreOptions()->getRequestkey());

        $this->session_parameters = [];
        $this->storeParameters([]);
    }

    private function retrieveRequestParameters(): array {
        if($this->filter->getConfiguration()->getStoreOptions()->isGETMethod()) {
            $parameters = $this->paramaterBag->all();
        } else {
            $parameters = $this->paramaterBag->get($this->filter->getConfiguration()->getStoreOptions()->getRequestkey(), []);
        }

        return array_intersect_key($parameters, array_flip(array_filter(array_keys($parameters), function ($key) {
            $pattern = $this->filter->parsePattern($this->filter->getDefaultMethodNamePattern());
            $pattern = "/$pattern/";

            return preg_match($pattern, $this->filter->toCamelCase($key));
        })));
    }

    private function retrieveStoredParameters(): array {
        list($store_parameters, $scope, $scope_key) = $this->_getStore();
        return $scope;
    }

    private function handleAutoCleanTimeout() {
        $auto_clean_timer_key = $this->filter->parsePattern("__autoclean_timer");

        if(
            $this->filter->getConfiguration()->getStoreOptions()->isPOSTMethod()
            && ($this->filter->getConfiguration()->getStoreOptions()->getAutoCleanTimeout() > 0)
        ) {
            if(!empty($this->request_parameters)) {
                $this->session_parameters[$auto_clean_timer_key] = Carbon::now();
            } else if(array_key_exists($auto_clean_timer_key, $this->session_parameters)) {
                /**
                 * @var Carbon $auto_clean_timer
                 */
                $auto_clean_timer = $this->session_parameters[$auto_clean_timer_key];

                if($auto_clean_timer->diffInSeconds(Carbon::now()) > $this->filter->getConfiguration()->getStoreOptions()->getAutoCleanTimeout()) {
                    $this->clean();
                }
            }
        } else {
            unset($this->session_parameters[$auto_clean_timer_key]);
        }
    }

    private function _getStore() {
        $store_parameters = $this->filter->getSession()->get($this->filter->getConfiguration()->getStoreOptions()->getSessionkey(), []);
        $scope_key = $this->filter->getRequest()->attributes->get('_route', $this->filter->getRequest()->getRequestUri());

        if(!array_key_exists($scope_key, $store_parameters)) {
            $store_parameters[$scope_key] = [];
        }

        return [$store_parameters, $store_parameters[$scope_key], $scope_key];
    }
}
