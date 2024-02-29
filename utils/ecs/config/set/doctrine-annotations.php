<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\DoctrineAnnotation\DoctrineAnnotationArrayAssignmentFixer;
use PhpCsFixer\Fixer\DoctrineAnnotation\DoctrineAnnotationBracesFixer;
use PhpCsFixer\Fixer\DoctrineAnnotation\DoctrineAnnotationIndentationFixer;
use PhpCsFixer\Fixer\DoctrineAnnotation\DoctrineAnnotationSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rulesWithConfiguration([
        DoctrineAnnotationArrayAssignmentFixer::class => ['operator' => ':'],
        DoctrineAnnotationBracesFixer::class => ['syntax' => 'without_braces'],
        DoctrineAnnotationIndentationFixer::class => ['indent_mixed_lines' => true],
        DoctrineAnnotationSpacesFixer::class => [
            'before_array_assignments_colon' => false,
            'after_array_assignments_colon' => false
        ],
    ]);
};
