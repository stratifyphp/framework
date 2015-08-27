<?php

namespace Stratify\Framework\Test\Mock;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FakeResponseEmitter implements EmitterInterface
{
    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * @var string
     */
    public $output;

    public function emit(ResponseInterface $response)
    {
        $this->response = $response;
        $this->output = $response->getBody()->__toString();
    }
}
