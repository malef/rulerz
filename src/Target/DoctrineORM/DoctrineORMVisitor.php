<?php

namespace RulerZ\Target\DoctrineORM;

use Hoa\Ruler\Model as AST;

use RulerZ\Compiler\Context;
use RulerZ\Exception;
use RulerZ\Model;
use RulerZ\Target\GenericSqlVisitor;
use RulerZ\Target\Operators\Definitions as OperatorsDefinitions;

class DoctrineORMVisitor extends GenericSqlVisitor
{
    /**
     * @var DoctrineAutoJoin
     */
    private $autoJoin;

    public function __construct(Context $context, OperatorsDefinitions $operators, $allowStarOperator = true)
    {
        parent::__construct($context, $operators, $allowStarOperator);

        $this->autoJoin = new DoctrineAutoJoin($context['em'], $context['root_entities'], $context['root_aliases'], $context['joins']);
    }

    /**
     * @inheritDoc
     */
    public function getCompilationData()
    {
        return [
            'detectedJoins' => $this->removeDuplicateJoins(
                $this->autoJoin->getDetectedJoins()
            ),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function visitAccess(AST\Bag\Context $element, &$handle = null, $eldnah = null)
    {
        return $this->autoJoin->buildAccessPath($element);
    }

    /**
     * {@inheritDoc}
     */
    public function visitParameter(Model\Parameter $element, &$handle = null, $eldnah = null)
    {
        // placeholder for a positional parameters
        if (is_int($element->getName())) {
            return '?' . $element->getName();
        }

        // placeholder for a named parameter
        return ':' . $element->getName();
    }

    /**
     * @param array $joins
     * @return array
     */
    protected function removeDuplicateJoins(array $joins)
    {
        $uniqueJoins = [];
        foreach ($joins as $join) {
            if (!$this->isJoinContainedInUniqueJoins($join, $uniqueJoins)) {
                $uniqueJoins[] = $join;
            }
        }

        return $uniqueJoins;
    }

    /**
     * @param array $join
     * @param array $uniqueJoins
     * @return bool
     */
    protected function isJoinContainedInUniqueJoins(array $join, array $uniqueJoins)
    {
        foreach ($uniqueJoins as $uniqueJoin) {
            if (
                $join['root'] === $uniqueJoin['root']
                && $join['column'] === $uniqueJoin['column']
                && $join['as'] === $uniqueJoin['as']
            ) {
                return true;
            }
        }

        return false;
    }
}
