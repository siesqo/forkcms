<?php

namespace Frontend\Core\Engine;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * Twig node for writing out the compiled version of a form field error.
 */
#[YieldReady]
class FormFieldErrorNode extends Node
{
    /** Name of the template var holding the form this field error belongs to. */
    private string $form;

    /** Name of the field of which we need to render the error. */
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
        $field = "'{$this->field}'";
        $getErrors = $form . "->getField($field)->getErrors()";

        $compiler
            ->addDebugInfo($this)
            ->write("yield ($getErrors ? '<span class=\"invalid-feedback\">' . $getErrors . '</span>' : '');\n")
        ;
    }
}
