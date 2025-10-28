<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataTransformer;

/**
 * DateTime To String Transformer
 *
 * Transforms between DateTime objects and formatted date strings.
 *
 * Usage Example:
 * ```php
 * $form->addDate('birthday', 'Birthday')
 *     ->addTransformer(new DateTimeToStringTransformer('Y-m-d'))
 *     ->add();
 * ```
 *
 * @author selcukmart
 * @since 2.3.1
 */
class DateTimeToStringTransformer extends AbstractDataTransformer
{
    /**
     * @param string $format The date format (e.g., 'Y-m-d', 'd/m/Y H:i:s')
     * @param string $inputTimezone Input timezone (default: UTC)
     * @param string $outputTimezone Output timezone (default: UTC)
     */
    public function __construct(
        private readonly string $format = 'Y-m-d',
        private readonly string $inputTimezone = 'UTC',
        private readonly string $outputTimezone = 'UTC'
    ) {
    }

    /**
     * Transforms a DateTime object into a formatted string.
     *
     * @param mixed $value A DateTime object or null
     * @return string|null The formatted date string or null
     * @throws \InvalidArgumentException If the value is not a DateTime or null
     */
    public function transform(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $this->validateType($value, \DateTimeInterface::class, 'transform');

        // Clone to avoid modifying original
        if ($value instanceof \DateTimeImmutable) {
            $dateTime = $value;
        } else {
            $dateTime = clone $value;
        }

        // Convert to output timezone if needed
        if ($this->outputTimezone !== $this->inputTimezone) {
            $dateTime = $dateTime->setTimezone(new \DateTimeZone($this->outputTimezone));
        }

        return $dateTime->format($this->format);
    }

    /**
     * Transforms a formatted string into a DateTime object.
     *
     * @param mixed $value A formatted date string or null
     * @return \DateTime|null The DateTime object or null
     * @throws \InvalidArgumentException If the string cannot be parsed
     */
    public function reverseTransform(mixed $value): ?\DateTime
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected string, got "%s" instead.',
                get_debug_type($value)
            ));
        }

        try {
            // Parse the date using the specified format
            $dateTime = \DateTime::createFromFormat($this->format, $value, new \DateTimeZone($this->outputTimezone));

            if ($dateTime === false) {
                $errors = \DateTime::getLastErrors();
                throw new \InvalidArgumentException(sprintf(
                    'Unable to parse date string "%s" using format "%s". Errors: %s',
                    $value,
                    $this->format,
                    json_encode($errors)
                ));
            }

            // Convert to input timezone if needed
            if ($this->outputTimezone !== $this->inputTimezone) {
                $dateTime->setTimezone(new \DateTimeZone($this->inputTimezone));
            }

            return $dateTime;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf(
                'The date string "%s" could not be parsed: %s',
                $value,
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * Get the configured format
     */
    public function getFormat(): string
    {
        return $this->format;
    }
}
