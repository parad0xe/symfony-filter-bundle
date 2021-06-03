<?php


namespace Parad0xe\Bundle\FilterBundle\Configuration\Options;


class ViewOptions
{
    /**
     * @var int
     */
    private $id_length;

    /**
     * @var string
     */
    private $custom_case_separator;

    /**
     * @var string
     */
    private $default_render_pattern;

    public function __construct($options)
    {
        $this->id_length = $options->id_length;
        $this->custom_case_separator = $options->custom_case_separator;
        $this->default_render_pattern = $options->default_render_pattern;
    }

    /**
     * @return int
     */
    public function getIDLength(): int
    {
        return $this->id_length;
    }

    /**
     * @return string
     */
    public function getCustomCaseSeparator(): string
    {
        return $this->custom_case_separator;
    }

    /**
     * @return string
     */
    public function getDefaultRenderPattern(): string
    {
        return $this->default_render_pattern;
    }
}
