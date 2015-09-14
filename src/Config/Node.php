<?php

namespace Stratify\Framework\Config;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Node
{
    private $name;
    private $subNodes = [];

    /**
     * @param array|string $subNodes
     */
    public function __construct(string $name, $subNodes)
    {
        $this->name = $name;
        $this->subNodes = $subNodes;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return array|string
     */
    public function getSubNodes()
    {
        return $this->subNodes;
    }
}
