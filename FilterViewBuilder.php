<?php


namespace Parad0xe\Bundle\FilterBundle;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class FilterViewBuilder
{
    /**
     * @var FilterInterface|AbstractFilter
     */
    private $filter;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    /**
     * ViewBuilder constructor.
     * @param FilterInterface|AbstractFilter $filter
     */
    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;
        $this->session = $filter->getSession();
        $this->request = $filter->getRequest();
    }

    /**
     * @param string $methodname
     * @param bool $multiple
     * @return string
     * @throws Exception
     */
    public function getName(string $methodname, $multiple = false): string {
        $methodname = $this->filter->toCustomCase($methodname);

        return $this->parseRenderName($this->filter->parsePattern($methodname), true, $multiple);
    }

    /**
     * @return string
     */
    public function getCleanButtonName(): string {
        return $this->filter->getConfiguration()->getStoreOptions()->getCleanerkey();
    }

    /**
     * @param string $methodname
     * @return string
     * @throws Exception
     */
    public function getValue(string $methodname): string {
        $value = $this->getParameter($methodname);

        if(is_array($value)) {
            return $value[0];
        }

        return trim($value);
    }

    /**
     * @param string $methodname
     * @param string $value
     * @return bool
     * @throws Exception
     */
    public function containValue(string $value, string $methodname): bool {
        $params = $this->getParameter($methodname);

        if(is_array($params)) {
            return in_array($value, $params);
        }

        return $params === $value;
    }

    /**
     * @param string $value
     * @param string $methodname
     * @return bool
     * @throws Exception
     */
    public function isSelected(string $value, string $methodname): bool {
        return $this->containValue($value, $methodname);
    }

    /**
     * @param string $methodname
     * @return bool
     * @throws Exception
     */
    public function emptyValue(string $methodname): bool {
        $params = $this->getParameter($methodname);

        if(is_array($params)) {
            return empty($params) || empty($params[0]);
        }

        return mb_strlen($params) === 0;
    }

    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->filter->getConfiguration()->getStoreOptions()->getMethod();
    }

    /**
     * @param $methodname
     * @param bool $scoped
     * @param bool $multiple
     * @return string
     */
    private function parseRenderName($methodname, $scoped, $multiple = false): string {
        $methodname = $this->parseMethodname($methodname);

        if($scoped && $this->filter->getConfiguration()->getStoreOptions()->isPOSTMethod()) {
            $methodname = $this->filter->getConfiguration()->getStoreOptions()->getRequestkey(). "[" . $methodname . "]";
        }

        if($multiple) {
            $methodname .= "[]";
        }

        return $methodname;
    }

    /**
     * @param $methodname
     * @return mixed
     * @throws Exception
     */
    private function getParameter($methodname) {
        $methodname = $this->parseMethodname($methodname);

        $parsed_methodname = $this->filter->parsePattern($this->filter->toCustomCase($methodname));

        $params = $this->filter->getParameters()->getRequestedParameters();

        if(array_key_exists($parsed_methodname, $params)) {
            $param = $params[$parsed_methodname];
        } else {
            $param = "";
        }

        return $param;
    }

    /**
     * @param string $methodname
     * @return string
     */
    private function parseMethodname(string $methodname): string {
        return str_replace($this->filter->getConfiguration()->getClassOptions()->getAvailableMethodsPrefix(), "", $methodname);
    }
}
