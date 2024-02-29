<?php

declare(strict_types=1);

namespace Util\PhpCsFixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

final class BlankLineAfterBlockStatementFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    private const CANDIDATE_TOKENS = [
        \T_FOR,
        \T_FOREACH,
        \T_IF,
        \T_SWITCH,
        \T_TRY,
        \T_WHILE,
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('An empty line feed must precede any configured statement.', [
            new CodeSample(
                '<?php
function A() {
    echo 1;
    return 1;
}
'
            ),
            new CodeSample(
                '<?php
$i = 0;
do {
    echo $i;
} while ($i > 0);
$foo->bar();
'
            ),
            new CodeSample(
                '<?php
if (true) {
return
}
$foo->bar();
'
            ),
            new CodeSample(
                '<?php
$a = 9000;
switch ($a) {
    case 42:
        break;
}
$foo->bar();
'
            ),
            new CodeSample(
                '<?php
if (null === $a) {
    throw new \\UnexpectedValueException("A cannot be null.");
}
$foo->bar();
'
            ),
            new CodeSample(
                '<?php
try {
    $foo->bar();
} catch (\\Exception $exception) {
    $a = -1;
}
$a = 9000;
'
            ),
            new CodeSample(
                '<?php
try {
    $foo->bar();
} finally {
    $a = -1;
}
$foo->baz();
'
            ),
        ]);
    }

    /**
     * Must run after NoExtraBlankLinesFixer, NoUselessReturnFixer, ReturnAssignmentFixer.
     */
    public function getPriority(): int
    {
        return -21;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(self::CANDIDATE_TOKENS);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $analyzer = new TokensAnalyzer($tokens);

        for ($tokenIndex = 0; $tokenIndex < $tokens->count(); ++$tokenIndex) {
            /** @var Token $token */
            $token = $tokens[$tokenIndex] ?? throw new \LogicException();

            if (!$token->equals('}')) {
                continue;
            }

            /** @var Token $nextTokenIndex */
            $nextTokenIndex = $tokens->getNextNonWhitespace($tokenIndex);

            if (!is_int($nextTokenIndex)) {
                return;
            }

            /** @var Token $nextToken */
            $nextToken = $tokens[$nextTokenIndex] ?? throw new \LogicException();

            if ($nextToken->isComment()) {
                continue;
            }

            if ($nextToken->isGivenKind(\T_WHILE) && $analyzer->isWhilePartOfDoWhile($nextTokenIndex)) {
                $tokenIndex = $this->findDoWhileEnd($tokens, $nextTokenIndex);
                $nextTokenIndex = $tokens->getNextMeaningfulToken($tokenIndex);

                if (!is_int($nextTokenIndex)) {
                    return;
                }
            }

            if ($this->shouldAddBlankLine($tokens, $nextTokenIndex)) {
                $this->insertBlankLine($tokens, $tokenIndex);
                $tokenIndex = $nextTokenIndex;
            }
        }
    }

    private function shouldAddBlankLine(Tokens $tokens, int $tokenIndex): bool
    {
        /** @var Token $token */
        $token = $tokens[$tokenIndex] ?? throw new \LogicException();

        if (\str_contains($token->getContent(), "\n")) {
            return \false;
        }

        return !($token->equalsAny([',', ';', '}', ')'])
            || $token->isGivenKind([\T_CATCH, \T_FINALLY, \T_ELSE, \T_ELSEIF, \T_WHILE]));
    }

    private function insertBlankLine(Tokens $tokens, int $tokenIndex): void
    {
        $nextTokenIndex = $tokenIndex + 1;
        $nextToken = $tokens[$nextTokenIndex] ?? throw new \LogicException();
        $lineEnding = $this->whitespacesConfig->getLineEnding();

        if ($nextToken->isWhitespace()) {
            $newlinesCount = \substr_count($nextToken->getContent(), "\n");

            if ($newlinesCount === 0) {
                $tokens[$nextTokenIndex] = new Token(
                    [\T_WHITESPACE, \rtrim($nextToken->getContent(), " \t").$lineEnding.$lineEnding]
                );
            } elseif ($newlinesCount === 1) {
                $tokens[$nextTokenIndex] = new Token([\T_WHITESPACE, $lineEnding.$nextToken->getContent()]);
            }
        } else {
            $tokens->insertAt($tokenIndex, new Token([\T_WHITESPACE, $lineEnding.$lineEnding]));
        }
    }

    private function findDoWhileEnd(Tokens $tokens, int $tokenIndex): int
    {
        for (; $tokenIndex < $tokens->count(); ++$tokenIndex) {
            /** @var Token $token */
            $token = $tokens[$tokenIndex] ?? throw new \LogicException();

            if ($token->equals(';')) {
                return $tokenIndex;
            }
        }

        throw new \LogicException();
    }
}
