<?php
declare(strict_types=1);

namespace Stratify\Framework\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface MiddlewareFactory
{
    public function getSubMiddlewares() : array;

    public function create(ContainerInterface $container, array $newSubMiddlewares) : MiddlewareInterface;
}
