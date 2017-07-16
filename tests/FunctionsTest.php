<?php

namespace Stratify\Framework\Test;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function does_not_error_if_required_twice()
    {
        require __DIR__ . '/../src/functions.php';
        require __DIR__ . '/../src/functions.php';
    }
}
