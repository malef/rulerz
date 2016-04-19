<?php

namespace RulerZ\Target\Native;

use Hoa\Ruler\Model as AST;

use RulerZ\Model;
use RulerZ\Target\GenericVisitor;

class NativeVisitor extends GenericVisitor
{
    /**
     * {@inheritDoc}
     */
    public function visitAccess(AST\Bag\Context $element, &$handle = null, $eldnah = null)
    {
        $flattenedDimensions = [
            sprintf('["%s"]', $element->getId())
        ];

        foreach ($element->getDimensions() as $dimension) {
            $flattenedDimensions[] = sprintf('["%s"]', $dimension[AST\Bag\Context::ACCESS_VALUE]);
        }

        return '$target' . implode('', $flattenedDimensions);
    }

    /**
     * {@inheritDoc}
     */
    public function visitParameter(Model\Parameter $element, &$handle = null, $eldnah = null)
    {
        return sprintf('$parameters["%s"]', $element->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function visitArray(AST\Bag\RulerArray $element, &$handle = null, $eldnah = null)
    {
        return sprintf('array(%s)', implode(', ', parent::visitArray($element, $handle, $eldnah)));
    }

    protected function visitRuntimeOperator(AST\Operator $element, &$handle = null, $eldnah = null)
    {
        $arguments = array_map(function ($argument) use (&$handle, $eldnah) {
            return $argument->accept($this, $handle, $eldnah);
        }, $element->getArguments());
        $inlinedArguments = empty($arguments) ? '' : ', ' . implode(', ', $arguments);

        return sprintf('call_user_func($operators["%s"]%s)', $element->getName(), $inlinedArguments);
    }
}
