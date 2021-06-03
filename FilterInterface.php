<?php


namespace Parad0xe\Bundle\FilterBundle;


interface FilterInterface
{
    /**
     * Linked Entity
     * @return string
     */
    public function fromModel(): string;

    /**
     * - use %id% : replace with entity name hash (Optional) (use if many filter type in same page)
     * - use %method% : replace with filter method to use (Required)
     *
     * @example return "filter-%id%-%method%"
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * @return string
     */
    public function __toString(): string;
}
