<?php
declare(strict_types = 1);

namespace Stratify\Framework;

use Stratify\Router\Exception\UnknownRoute;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class UrlGenerator
{
    /**
     * @var \Stratify\Router\UrlGenerator[]
     */
    private $routeUrlGenerators;

    /**
     * @param \Stratify\Router\UrlGenerator[] $routeUrlGenerators
     */
    public function __construct(array $routeUrlGenerators)
    {
        $this->routeUrlGenerators = $routeUrlGenerators;
    }

    /**
     * @throws UnknownRoute
     */
    public function routeUrl(string $route, array $parameters = []) : string
    {
        foreach ($this->routeUrlGenerators as $urlGenerator) {
            if ($urlGenerator->has($route)) {
                return $urlGenerator->generate($route, $parameters);
            }
        }
        throw new UnknownRoute($route);
    }
}
