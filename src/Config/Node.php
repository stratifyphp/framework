<?php

namespace Stratify\Framework\Config;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Node
{
    private $name;
    private $subNodes = [];

    public function __construct(string $name, array $subNodes)
    {
        $this->name = $name;
        $this->subNodes = $subNodes;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getSubNodes() : array
    {
        return $this->subNodes;
    }
}
