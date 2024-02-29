<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\ControlStructures\MultiLineConditionSniff;
use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\Alias\NoAliasLanguageConstructCallFixer;
use PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer;
use PhpCsFixer\Fixer\Basic\BracesFixer;
use PhpCsFixer\Fixer\Casing\ClassReferenceNameCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\SingleLineThrowFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\GetClassToClassKeywordFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTagCasingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLinePromotedPropertyFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use Util\PhpCsFixer\BlankLineAfterBlockStatementFixer;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->parallel();
    $ecsConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $ecsConfig->sets([SetList::COMMON]);
    $ecsConfig->sets([SetList::CLEAN_CODE]);
    $ecsConfig->sets([SetList::PSR_12]);
    $ecsConfig->import(__DIR__.'/utils/ecs/config/set/symfony.php');
    $ecsConfig->import(__DIR__.'/utils/ecs/config/set/doctrine-annotations.php');

    $ecsConfig->skip([
        // Temporary
        NoSuperfluousPhpdocTagsFixer::class,
        AssignmentInConditionSniff::class,
        // Has no exceptions for SimpleXMLElement
        StrictComparisonFixer::class,
        // Does not work with configuration chain calls ;(
        MethodChainingIndentationFixer::class,
        // Ugly rules
        NotOperatorWithSuccessorSpaceFixer::class,
        // Symfony opinionated rules
        SingleLineThrowFixer::class,
        // Symplify opinionated rules
        ArrayListItemNewlineFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        StandaloneLineInMultilineArrayFixer::class,
        StandaloneLinePromotedPropertyFixer::class,
    ]);

    $fixers = [
        ListSyntaxFixer::class => [],
        TrailingCommaInMultilineFixer::class => ['elements' => ['arrays', 'parameters']],
        BlankLineBeforeStatementFixer::class => [
            'statements' => [
                'case',
                'continue',
                'declare',
                'default',
                'do',
                'exit',
                'for',
                'foreach',
                'goto',
                'if',
                'include',
                'include_once',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
            ],
        ],
        BlankLineAfterBlockStatementFixer::class => [],
        BracesFixer::class => [
            'allow_single_line_closure' => true,
            'position_after_functions_and_oop_constructs' => 'next',
            'position_after_control_structures' => 'same',
            'position_after_anonymous_constructs' => 'same',
        ],
        CastSpacesFixer::class => [],
        ClassAttributesSeparationFixer::class => ['elements' => ['method' => 'one']],
        ClassDefinitionFixer::class => ['single_line' => true],
        ClassReferenceNameCasingFixer::class => [],
        CleanNamespaceFixer::class => [],
        ConcatSpaceFixer::class => [
            'spacing' => 'none',
        ],
        DeclareStrictTypesFixer::class => [],
        PhpdocTypesOrderFixer::class => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        PhpdocSeparationFixer::class => ['groups' => [['ORM\\*'], ['Assert\\*', 'AppAssert\\*'], ['Serializer\\*'], ['SWG\\*']]],
        PhpdocLineSpanFixer::class => ['const' => 'single', 'property' => 'single'],
        PhpdocTagCasingFixer::class => ['tags' => ['inheritdoc']],
        PhpdocOrderFixer::class => ['order' => ['param', 'var', 'return', 'throws']],
        // Enabling the "alias" group will replace our Callback entity with a callable :(
        PhpdocTypesFixer::class => ['groups' => ['simple', 'meta']],
        OrderedClassElementsFixer::class => [],
        OrderedImportsFixer::class => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
        ],
        VisibilityRequiredFixer::class => [
            'elements' => [
                'const',
                'property',
                'method',
            ],
        ],
        YodaStyleFixer::class => [
            'equal' => false,
            'identical' => false,
            'always_move_variable' => false,
            'less_and_greater' => false,
        ],
        // Prefer MultiLineConditionSniff over OperatorLinebreakFixer
        MultiLineConditionSniff::class => [],
        RandomApiMigrationFixer::class => [
            'replacements' => [
                'getrandmax' => 'random_int',
                'mt_rand' => 'random_int',
                'rand' => 'random_int',
                'srand' => 'random_int',
            ]
        ],
        NoAliasFunctionsFixer::class => ['sets' => ['@all']],
        NoAliasLanguageConstructCallFixer::class => [],
        CombineConsecutiveIssetsFixer::class => [],
        GetClassToClassKeywordFixer::class => [],
    ];

    foreach ($fixers as $class => $options) {
        if (is_subclass_of($class, ConfigurableFixerInterface::class)) {
            $ecsConfig->ruleWithConfiguration($class, $options);
        } else {
            $ecsConfig->rule($class);
        }
    }
};
