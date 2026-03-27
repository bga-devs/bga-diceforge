<?php

declare(strict_types=1);

namespace Bga\Games\diceforge\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids calling DB methods via self::, static:: or Table:: in all classes except Db implementations.
 * Non-Db classes should use $this->db->...() wrappers instead.
 * Db implementations are allowed because they forward to Table statics.
 *
 * @implements Rule<StaticCall>
 */
class EnforceDbWrapperRule implements Rule
{
    private const BANNED_METHODS = [
        'DbQuery',
        'getUniqueValueFromDB',
        'getCollectionFromDB',
        'getObjectListFromDB',
        'getNonEmptyCollectionFromDB',
        'getObjectFromDB',
    ];

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier || !$node->class instanceof Node\Name) {
            return [];
        }

        $methodName = $node->name->toString();
        if (!in_array($methodName, self::BANNED_METHODS, true)) {
            return [];
        }

        $callerName = $node->class->toString();
        if (!in_array($callerName, ['self', 'static', 'Table', 'Bga\GameFramework\Table'], true)) {
            return [];
        }

        // Allow calls within Db implementations
        $classReflection = $scope->getClassReflection();
        if ($classReflection !== null && $classReflection->implementsInterface('Bga\Games\diceforge\Framework\Db\Db')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Do not call %s::%s() directly. Use the $this->db wrapper instead.',
                    $callerName,
                    $methodName,
                )
            )->identifier('bga.enforceDbWrapper')->build(),
        ];
    }
}
