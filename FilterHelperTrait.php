<?php


namespace Parad0xe\Bundle\FilterBundle;


trait FilterHelperTrait
{
    /**
     * @var FilterInterface|AbstractFilter
     */
    public $_filter = null;

    public function __initTrait(FilterInterface $filter) {
        $this->_filter = $filter;
    }

    /**
     * @return string
     */
    public function getDefaultMethodNamePattern(): string {
        return "([A-Za-z0-9_\-@]+)";
    }

    /**
     * @param string $modelname
     * @return string
     */
    public function modelToLower(string $modelname): string {
        if(strpos($modelname, '\\') !== false) {
            return strtolower(array_slice(explode('\\', $modelname), -1)[0]);
        }

        return strtolower($modelname);
    }

    /**
     * @param string $methodname
     * @return string
     */
    public function parsePattern(string $methodname): string {
        $id_hash = hash('sha256', $this->modelToLower($this->_filter->fromModel()) . $this->_filter);

        $fieldname = str_replace(
            "{id}",
            substr($id_hash, 0, $this->_filter->getConfiguration()->getViewOptions()->getIDLength()),
            $this->_filter->getPattern()
        );

        $fieldname = str_replace(".", "_", $fieldname);

        return $fieldname = str_replace("{method}", $methodname, $fieldname);
    }

    /**
     * @param string $data
     * @return string
     */
    public function toCustomCase(string $data): string {
        return strtolower(preg_replace(
            '/(?<!^)[A-Z]/',
            $this->_filter->getConfiguration()->getViewOptions()->getCustomCaseSeparator() . '$0',
            $data
        ));
    }

    /**
     * @param string $data
     * @return string
     */
    public function toCamelCase(string $data): string {
        return lcfirst(str_replace(
            $this->_filter->getConfiguration()->getViewOptions()->getCustomCaseSeparator(),
            "",
            ucwords($data, $this->_filter->getConfiguration()->getViewOptions()->getCustomCaseSeparator())
        ));
    }
}
