<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

use FormGenerator\V2\Contracts\{ValidatorInterface, ValidationResult};
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Symfony Validator Integration
 *
 * Integrates Symfony Validator component with FormGenerator
 * Supports DTO/Entity validation
 *
 * @author selcukmart
 * @since 2.0.0
 */
class SymfonyValidator implements ValidatorInterface
{
    public function __construct(
        private readonly SymfonyValidatorInterface $validator,
        private readonly NativeValidator $nativeValidator
    ) {
    }

    /**
     * Validate a value against rules
     */
    public function validate(mixed $value, array $rules, array $context = []): ValidationResult
    {
        $errors = [];

        // Convert rules to Symfony constraints
        $constraints = $this->rulesToConstraints($rules);

        if (!empty($constraints)) {
            $violations = $this->validator->validate($value, $constraints);

            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath() ?: 'value'] = $violation->getMessage();
            }
        }

        // Also run native validation for JS-compatible rules
        $nativeResult = $this->nativeValidator->validate($value, $rules, $context);
        if ($nativeResult->isFailed()) {
            $errors = array_merge($errors, $nativeResult->getErrors());
        }

        return empty($errors)
            ? ValidationResult::success()
            : ValidationResult::failure($errors);
    }

    /**
     * Validate DTO/Entity object
     */
    public function validateObject(object $dto, ?array $groups = null): ValidationResult
    {
        $violations = $this->validator->validate($dto, null, $groups);

        if ($violations->count() === 0) {
            return ValidationResult::success();
        }

        $errors = [];
        foreach ($violations as $violation) {
            $property = $violation->getPropertyPath();
            $errors[$property] = $violation->getMessage();
        }

        return ValidationResult::failure($errors);
    }

    /**
     * Extract validation rules from DTO/Entity
     */
    public function extractRulesFromObject(object $dto): array
    {
        $metadata = $this->validator->getMetadataFor($dto);
        $rules = [];

        foreach ($metadata->getConstrainedProperties() as $property) {
            $propertyMetadata = $metadata->getPropertyMetadata($property);
            $propertyRules = [];

            foreach ($propertyMetadata as $meta) {
                foreach ($meta->getConstraints() as $constraint) {
                    $ruleName = $this->constraintToRuleName($constraint);
                    if ($ruleName) {
                        $propertyRules[$ruleName] = $this->constraintToParams($constraint);
                    }
                }
            }

            if (!empty($propertyRules)) {
                $rules[$property] = $propertyRules;
            }
        }

        return $rules;
    }

    /**
     * Add custom validation rule
     */
    public function addRule(string $name, callable $callback, string $message): void
    {
        $this->nativeValidator->addRule($name, $callback, $message);
    }

    /**
     * Check if rule exists
     */
    public function hasRule(string $name): bool
    {
        return $this->nativeValidator->hasRule($name);
    }

    /**
     * Get JavaScript validation code for rules
     */
    public function getJavaScriptCode(array $rules): string
    {
        return $this->nativeValidator->getJavaScriptCode($rules);
    }

    /**
     * Validate entire form data
     */
    public function validateForm(array $data, array $fieldsRules): array
    {
        $results = [];

        foreach ($fieldsRules as $fieldName => $rules) {
            $value = $data[$fieldName] ?? null;
            $results[$fieldName] = $this->validate($value, $rules, $data);
        }

        return $results;
    }

    /**
     * Convert rules array to Symfony constraints
     */
    private function rulesToConstraints(array $rules): array
    {
        $constraints = [];

        foreach ($rules as $ruleName => $params) {
            $constraint = match ($ruleName) {
                'required' => new Assert\NotBlank(),
                'email' => new Assert\Email(),
                'minLength' => new Assert\Length(['min' => $params]),
                'maxLength' => new Assert\Length(['max' => $params]),
                'min' => new Assert\GreaterThanOrEqual($params),
                'max' => new Assert\LessThanOrEqual($params),
                'pattern' => new Assert\Regex([
                    'pattern' => $params['regex'] ?? $params,
                    'message' => $params['message'] ?? 'Invalid format',
                ]),
                'url' => new Assert\Url(),
                'numeric' => new Assert\Type('numeric'),
                'integer' => new Assert\Type('integer'),
                'date' => new Assert\Date(),
                'in' => new Assert\Choice(['choices' => $params]),
                'notIn' => new Assert\Choice(['choices' => $params, 'match' => false]),
                default => null,
            };

            if ($constraint) {
                $constraints[] = $constraint;
            }
        }

        return $constraints;
    }

    /**
     * Convert Symfony constraint to rule name
     */
    private function constraintToRuleName(object $constraint): ?string
    {
        return match (get_class($constraint)) {
            Assert\NotBlank::class, Assert\NotNull::class => 'required',
            Assert\Email::class => 'email',
            Assert\Length::class => 'length',
            Assert\Range::class => 'range',
            Assert\Regex::class => 'pattern',
            Assert\Url::class => 'url',
            Assert\Type::class => 'type',
            Assert\Choice::class => 'in',
            default => null,
        };
    }

    /**
     * Convert Symfony constraint to params
     */
    private function constraintToParams(object $constraint): mixed
    {
        return match (get_class($constraint)) {
            Assert\Length::class => [
                'min' => $constraint->min,
                'max' => $constraint->max,
            ],
            Assert\Range::class => [
                'min' => $constraint->min,
                'max' => $constraint->max,
            ],
            Assert\Regex::class => [
                'regex' => $constraint->pattern,
                'message' => $constraint->message,
            ],
            Assert\Choice::class => $constraint->choices,
            default => true,
        };
    }
}
