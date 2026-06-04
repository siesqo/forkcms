<?php

namespace Frontend\Core\Engine;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * Twig node for writing out a compiled version of a closing form tag.
 */
#[YieldReady]
class FormEndNode extends Node
{
    public function __construct(int $lineNumber)
    {
        parent::__construct([], [], $lineNumber);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write("yield '</form>';\n");
    }
}
