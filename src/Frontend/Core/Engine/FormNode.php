<?php

namespace Frontend\Core\Engine;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * Twig node for writing out the compiled representation of an opening form tag.
 */
#[YieldReady]
class FormNode extends Node
{
    /** Template variable holding the form. */
    private string $form;

    public function __construct(string $form, int $lineNumber)
    {
        parent::__construct([], [], $lineNumber);
        $this->form = $form;
    }

    public function compile(Compiler $compiler): void
    {
        // Set some string representations to make the code writing via the
        // compiler a bit more readable. ("a bit")
        $form = "\$context['form_{$this->form}']";
        $formAction = $form . '->getAction()';
        $formMethod = $form . '->getMethod()';
        $formName = $form . '->getName()';
        $formToken = $form . '->getToken()';
        $formUseToken = $form . '->getUseToken()';
        $formParamsHtml = $form . '->getParametersHTML()';

        $compiler
            ->addDebugInfo($this)
            ->write("yield '<form method=\"' . $formMethod . '\" action=\"' . $formAction . '\"' . $formParamsHtml . '>';\n")
            ->write("yield '<input type=\"hidden\" name=\"form\" value=\"' . $formName . '\" id=\"form' . ucfirst($formName) . '\" />';\n")
            ->write("if ($formUseToken) yield '<input type=\"hidden\" name=\"form_token\" value=\"' . $formToken . '\" id=\"formToken' . ucfirst($formName) . '\" />';\n")
        ;
    }
}
