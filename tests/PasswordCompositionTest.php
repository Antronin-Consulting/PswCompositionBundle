<?php

declare(strict_types=1);

namespace Antronin\PswCompositionBundle\Tests\Validator\Constraints;

use Antronin\PswCompositionBundle\Validator\Constraints\MinRegex;
use Antronin\PswCompositionBundle\Validator\Constraints\PasswordComposition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordCompositionTest extends TestCase
{
    public function testGetConstraintsIncludesBaseConstraints(): void
    {
        $constraint = new PasswordComposition();
        $innerConstraints = $constraint->getNestedConstraints();

        $this->assertContainsInstanceOf(Assert\NotBlank::class, $innerConstraints);
        $this->assertContainsInstanceOf(Assert\Type::class, $innerConstraints);
        $this->assertContainsInstanceOf(Assert\NotCompromisedPassword::class, $innerConstraints);
    }

    public function testGetConstraintsWithLength(): void
    {
        $constraint = new PasswordComposition(minLength: 8, maxLength: 64);
        $innerConstraints = $constraint->getNestedConstraints();
        $lengthConstraint = $this->findConstraint(Assert\Length::class, $innerConstraints);

        self::assertNotNull($lengthConstraint);
        self::assertSame(8, $lengthConstraint->min);
        self::assertSame(64, $lengthConstraint->max);
    }

    public function testGetConstraintsWithMinLengthOnly(): void
    {
        $constraint = new PasswordComposition(minLength: 10);
        $innerConstraints = $constraint->getNestedConstraints();
        $lengthConstraint = $this->findConstraint(Assert\Length::class, $innerConstraints);

        self::assertNotNull($lengthConstraint);
        self::assertSame(10, $lengthConstraint->min);
        self::assertNull($lengthConstraint->max);
    }

    public function testGetConstraintsWithMaxLengthOnly(): void
    {
        $constraint = new PasswordComposition(maxLength: 128);
        $innerConstraints = $constraint->getNestedConstraints();
        $lengthConstraint = $this->findConstraint(Assert\Length::class, $innerConstraints);

        self::assertNotNull($lengthConstraint);
        self::assertNull($lengthConstraint->min);
        self::assertSame(128, $lengthConstraint->max);
    }

    public function testGetConstraintsWithMinLowercase(): void
    {
        $constraint = new PasswordComposition(minLowercase: 2, lowercasePattern: 'a-z');
        $innerConstraints = $constraint->getNestedConstraints();
        $minRegex = $this->findConstraint(MinRegex::class, $innerConstraints);

        self::assertNotNull($minRegex);
        self::assertSame('/[a-z]{2,}/u', $minRegex->pattern);
        self::assertSame(2, $minRegex->min);
        self::assertSame('password.constraints.lowercase', $minRegex->message);
    }

    public function testGetConstraintsWithAllOptions(): void
    {
        $constraint = new PasswordComposition(
            minLength: 12,
            maxLength: 100,
            minLowercase: 2,
            minUppercase: 3,
            minNumber: 4,
            minSpecial: 1,
            lowercasePattern: 'a-z',
            uppercasePattern: 'A-Z',
            numberPattern: '0-9',
            specialsPattern: '!@#$'
        );

        $innerConstraints = $constraint->getNestedConstraints();

        self::assertCount(8, $innerConstraints); // NotBlank, Type, NotCompromised, Length, and 4 MinRegex

        $lengthConstraint = $this->findConstraint(Assert\Length::class, $innerConstraints);
        self::assertNotNull($lengthConstraint);
        self::assertSame(12, $lengthConstraint->min);
        self::assertSame(100, $lengthConstraint->max);

        $regexConstraints = array_values(array_filter($innerConstraints, static fn($c) => $c instanceof MinRegex));

        self::assertCount(4, $regexConstraints);

        self::assertSame('/[a-z]{2,}/u', $regexConstraints[0]->pattern);
        self::assertSame(2, $regexConstraints[0]->min);

        self::assertSame('/[A-Z]{3,}/u', $regexConstraints[1]->pattern);
        self::assertSame(3, $regexConstraints[1]->min);

        self::assertSame('/[0-9]{4,}/u', $regexConstraints[2]->pattern);
        self::assertSame(4, $regexConstraints[2]->min);

        self::assertSame('/[' . preg_quote('!@#$', '/') . ']{1,}/u', $regexConstraints[3]->pattern);
        self::assertSame(1, $regexConstraints[3]->min);
    }

    /**
     * @param object[] $constraints
     */
    private function assertContainsInstanceOf(string $class, array $constraints): void
    {
        $found = (bool) $this->findConstraint($class, $constraints);
        self::assertTrue($found, "Failed asserting that an instance of '$class' is in the constraint list.");
    }

    /**
     * @param string $class
     * @param object[] $constraints
     * @return object|null
     */
    private function findConstraint(string $class, array $constraints): ?object
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof $class) {
                return $constraint;
            }
        }
        return null;
    }
}
