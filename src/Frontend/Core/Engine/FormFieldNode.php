<?php

namespace Frontend\Core\Engine;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * Twig node for writing out the compiled version of a form field.
 */
#[YieldReady]
class FormFieldNode extends Node
{
    /** Name of the template var holding the form this field belongs to. */
    private string $form;

    /** Name of the field to render. */
    private string $field;

    public function __construct(string $form, string $field, int $lineNumber)
    {
        parent::__construct([], [], $lineNumber);
        $this->form = $form;
        $this->field = $field;
    }

    public function compile(Compiler $compiler): void
    {
        $form = "\$context['form_{$this->form}']";
        $parseField = $form . "->getField('{$this->field}')->parse()";

        $compiler
            ->addDebugInfo($this)
            ->write("yield $parseField;\n")
        ;
    }
}
