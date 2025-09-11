<?php

declare(strict_types=1);

namespace App\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<StaticCall>
 */
final class EventListenerMethodExistsRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Only Event::listen(...)
        if (!$node->class instanceof Name) {
            return [];
        }

        $calledOn = $scope->resolveName($node->class);
        $method = $node->name instanceof Identifier ? $node->name->toString() : null;

        $isEventFacade = \in_array($calledOn, [
            'Illuminate\Support\Facades\Event',
            'Illuminate\Events\Dispatcher',
        ], true);

        if (!$isEventFacade || $method !== 'listen') {
            return [];
        }

        // Need at least 2nd arg (listener) and it must be an Arg node
        if (\count($node->args) < 2 || !($node->args[1] instanceof Arg)) {
            return [];
        }

        $listenerExpr = $node->args[1]->value;

        // Case 1: array callable [Class::class, 'method']
        if ($listenerExpr instanceof Array_) {
            return $this->checkArrayCallable($listenerExpr, $scope);
        }

        // Case 2: class-string listener Class::class (implies handle())
        if ($listenerExpr instanceof ClassConstFetch
            && $listenerExpr->name instanceof Identifier
            && $listenerExpr->name->toString() === 'class'
            && $listenerExpr->class instanceof Name
        ) {
            $className = $scope->resolveName($listenerExpr->class);
            return $this->ensureMethodExists($className, 'handle', 'class-string listener');
        }

        // Closures or other forms: ignore
        return [];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function checkArrayCallable(Array_ $array, Scope $scope): array
    {
        if (\count($array->items) < 2) {
            return [];
        }

        /** @var ArrayItem|null $classItem */
        $classItem = $array->items[0] ?? null;
        /** @var ArrayItem|null $methodItem */
        $methodItem = $array->items[1] ?? null;

        if ($classItem === null || $methodItem === null) {
            return [];
        }

        $className = null;
        $methodName = null;

        if ($classItem->value instanceof ClassConstFetch
            && $classItem->value->name instanceof Identifier
            && $classItem->value->name->toString() === 'class'
            && $classItem->value->class instanceof Name
        ) {
            $className = $scope->resolveName($classItem->value->class);
        }

        if ($methodItem->value instanceof String_) {
            $methodName = $methodItem->value->value;
        }

        if ($className === null || $methodName === null) {
            return [];
        }

        return $this->ensureMethodExists($className, $methodName, 'array callable listener');
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function ensureMethodExists(string $className, string $methodName, string $context): array
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Event listener class %s does not exist.',
                    $className
                ))
                    ->identifier('event.listener.classNotFound')
                    ->build(),
            ];
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (!$classReflection->hasMethod($methodName)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Event listener %s refers to a non-existent method %s::%s().',
                    $context,
                    $className,
                    $methodName
                ))
                    ->identifier('event.listener.methodNotFound')
                    ->build(),
            ];
        }

        return [];
    }
}
