<?php declare(strict_types=1);

namespace Shopware\Core\DevOps\StaticAnalyze\PHPStan\Rules\Migration;

use Doctrine\DBAL\Connection;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Shopware\Core\Framework\Log\Package;

/**
 * @implements Rule<MethodCall>
 *
 * @internal
 */
#[Package('framework')]
class AddColumnRule implements Rule
{
    use InMigrationClassTrait;

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     *
     * @return array<array-key, RuleError|string>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof MethodCall) {
            return [];
        }

        if (!$this->isInMigrationClass($scope)) {
            return [];
        }

        // ignore namespace V6_4 and V6_3
        $fullyQualifiedClassName = $scope->getClassReflection()?->getName() ?? '';
        if (str_contains($fullyQualifiedClassName, 'Shopware\\Core\\Migration\\V6_4')
            || str_contains($fullyQualifiedClassName, 'Shopware\\Core\\Migration\\V6_3')
        ) {
            return [];
        }

        // is \Doctrine\DBAL\Connection::executeStatement?
        if (!$node->name instanceof Identifier) {
            return [];
        }

        if ($node->name->name !== 'executeStatement') {
            return [];
        }

        if (!$node->var instanceof Variable) {
            return [];
        }

        $varType = $scope->getType($node->var);

        if (!$varType->isSuperTypeOf(new ObjectType(Connection::class))->yes()) {
            return [];
        }

        // is called with `ADD COLUMN` in string
        if (\count($node->args) !== 1) {
            return [];
        }

        if (!\array_key_exists(0, $node->args)) {
            return [];
        }

        $arg = $node->args[0];

        if (!$arg instanceof Arg) {
            return [];
        }

        $arg = $arg->value;

        if (!$arg instanceof String_) {
            return [];
        }

        if (str_contains($arg->value, 'GENERATED ALWAYS AS')) {
            return [];
        }

        $pattern = '/ALTER TABLE .* ADD CONSTRAINT.*/m';
        if (preg_match($pattern, $arg->value)) {
            return [];
        }

        $pattern = '/ALTER TABLE .* ADD INDEX.*/m';
        if (preg_match($pattern, $arg->value)) {
            return [];
        }

        $pattern = '/ALTER TABLE .* ADD UNIQUE INDEX.*/m';
        if (preg_match($pattern, $arg->value)) {
            return [];
        }
        $pattern = '/ALTER TABLE .* ADD FOREIGN KEY.*/m';
        if (preg_match($pattern, $arg->value)) {
            return [];
        }

        $pattern = '/ALTER TABLE .* ADD .*/m';
        if (preg_match($pattern, $arg->value)) {
            return [
                RuleErrorBuilder::message('Do not use `ALTER TABLE ... ADD COLUMN` in migration. Use MigrationStep::addColumn instead')
                    ->identifier('shopware.addColumn')
                    ->build(),
            ];
        }

        return [];
    }
}
