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

        $this->assertNotNull($lengthConstraint);
        $this->assertSame(8, $lengthConstraint->min);
        $this->assertSame(64, $lengthConstraint->max);
    }

    public function testGetConstraintsWithMinLengthOnly(): void
    {
        $constraint = new PasswordComposition(minLength: 10);
        $innerConstraints = $constraint->getNestedConstraints();
        $lengthConstraint = $this->findConstraint(Assert\Length::class, $innerConstraints);

        $this->assertNotNull($lengthConstraint);
        $this->assertSame(10, $lengthConstraint->min);
        $this->assertNull($lengthConstraint->max);
    }

    public function testGetConstraintsWithMaxLengthOnly(): void
    {
        $constraint = new PasswordComposition(maxLength: 128);
        $innerConstraints = $constraint->getNestedConstraints();
        $lengthConstraint = $this->findConstraint(Assert\Length::class, $innerConstraints);

        $this->assertNotNull($lengthConstraint);
        $this->assertNull($lengthConstraint->min);
        $this->assertSame(128, $lengthConstraint->max);
    }

    public function testGetConstraintsWithMinLowercase(): void
    {
        $constraint = new PasswordComposition(minLowercase: 2, lowercasePattern: 'a-z');
        $innerConstraints = $constraint->getNestedConstraints();
        $minRegex = $this->findConstraint(MinRegex::class, $innerConstraints);

        $this->assertNotNull($minRegex);
        $this->assertSame('/[a-z]{2,}/u', $minRegex->pattern);
        $this->assertSame(2, $minRegex->min);
        $this->assertSame('password.constraints.lowercase', $minRegex->message);
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

        $this->assertCount(8, $innerConstraints); // NotBlank, Type, NotCompromised, Length, and 4 MinRegex

        $lengthConstraint = $this->findConstraint(Assert\Length::class, $innerConstraints);
        $this->assertNotNull($lengthConstraint);
        $this->assertSame(12, $lengthConstraint->min);
        $this->assertSame(100, $lengthConstraint->max);

        $regexConstraints = array_values(array_filter($innerConstraints, fn ($c) => $c instanceof MinRegex));

        $this->assertCount(4, $regexConstraints);

        $this->assertSame('/[a-z]{2,}/u', $regexConstraints[0]->pattern);
        $this->assertSame(2, $regexConstraints[0]->min);

        $this->assertSame('/[A-Z]{3,}/u', $regexConstraints[1]->pattern);
        $this->assertSame(3, $regexConstraints[1]->min);

        $this->assertSame('/[0-9]{4,}/u', $regexConstraints[2]->pattern);
        $this->assertSame(4, $regexConstraints[2]->min);

        $this->assertSame('/[' . preg_quote('!@#$', '/') . ']{1,}/u', $regexConstraints[3]->pattern);
        $this->assertSame(1, $regexConstraints[3]->min);
    }

    /**
     * @param object[] $constraints
     */
    private function assertContainsInstanceOf(string $class, array $constraints): void
    {
        $found = (bool) $this->findConstraint($class, $constraints);
        $this->assertTrue($found, "Failed asserting that an instance of '$class' is in the constraint list.");
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
