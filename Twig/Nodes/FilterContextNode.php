<?php


namespace Parad0xe\Bundle\FilterBundle\Twig\Nodes;


use Parad0xe\Bundle\FilterBundle\AbstractFilter;
use Parad0xe\Bundle\FilterBundle\FilterInterface;
use Twig\Compiler;
use Twig\Node\Node;

class FilterContextNode extends Node
{
    public function __construct(array $nodes = [], array $attributes = [], int $lineno = 0, string $tag = null)
    {
        parent::__construct($nodes, $attributes, $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->raw("\$_parameter = '{$this->getAttribute('name')}';");

        $compiler->raw(<<<'EOT'
$_value = $context['
EOT);

        $compiler->raw($this->getAttribute('name'));

        $compiler->raw(<<<'EOT'
'];

$value = $_value;

if(!empty($value)) {
    if(is_array($value)) {
        $value = $value[0];
    }
    
    $value = str_replace("Entity", "Filters", get_class($value)."Filter");
    
    if(is_a($value, 
EOT);
        $compiler->raw("'" . FilterInterface::class . "'");

        $compiler->raw(<<<'EOT'
, true)) {
        try {
            $context["filter"] = $value::view();
        } catch (\Exception $e) {
            $classname = get_class($value);
            throw new \Exception("Failed get filter from \"$value\"");
        }
    } else {
        throw new \Exception("Variable \"$_parameter\" does not implement FilterInterface, got $value");
    }
} else {

}
EOT);

        $compiler->subcompile($this->getNode('body'));

        $compiler->raw("unset(\$context['filter']);");
        $compiler->raw("unset(\$_parameter);");
    }
}
