<?php
declare(strict_types = 1);

namespace Stratify\Framework\Middleware;

use Psr\Container\ContainerInterface;
use Stratify\Http\Middleware\Middleware;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface MiddlewareFactory
{
    public function getSubMiddlewares();

    public function create(ContainerInterface $container, array $newSubMiddlewares) : Middleware;
}
