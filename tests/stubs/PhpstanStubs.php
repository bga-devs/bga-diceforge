<?php

/**
 * PHPStan type stubs for IDE support.
 *
 * PHPStan ships as a PHAR, so its classes are not visible to PHP language servers
 * like Intelephense. This file provides minimal stub definitions for the PHPStan
 * types used in custom rule classes so that IDE tooling can resolve them.
 *
 * This file must NOT be added to phpstan.neon scanFiles — it is for the IDE only.
 */

namespace PHPStan\Rules {
    interface Rule
    {
        public function getNodeType(): string;

        /**
         * @param \PhpParser\Node $node
         * @param \PHPStan\Analyser\Scope $scope
         * @return list<\PHPStan\Rules\IdentifierRuleError>
         */
        public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope): array;
    }

    interface RuleError
    {
    }

    interface IdentifierRuleError extends RuleError
    {
    }

    class RuleErrorBuilder
    {
        public static function message(string $message): static
        {
            throw new \LogicException('stub');
        }

        public function identifier(string $identifier): static
        {
            throw new \LogicException('stub');
        }

        public function build(): IdentifierRuleError
        {
            throw new \LogicException('stub');
        }
    }
}

namespace PHPStan\Analyser {
    class Scope
    {
        public function getClassReflection(): ?\PHPStan\Reflection\ClassReflection
        {
            throw new \LogicException('stub');
        }
    }
}

namespace PHPStan\Reflection {
    class ClassReflection
    {
        public function isSubclassOf(string $className): bool
        {
            throw new \LogicException('stub');
        }

        public function implementsInterface(string $interfaceName): bool
        {
            throw new \LogicException('stub');
        }

        public function getDisplayName(): string
        {
            throw new \LogicException('stub');
        }
    }
}
