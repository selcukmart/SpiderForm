<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Symfony\FormType;

use FormGenerator\V2\Builder\FormBuilder as GeneratorFormBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Symfony Form Type for Form Generator Integration
 *
 * This allows using FormGenerator within Symfony's Form component
 *
 * @author selcukmart
 * @since 2.0.0
 */
class FormGeneratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['generator_builder'] instanceof GeneratorFormBuilder) {
            // Build form from generator configuration
            $generatorConfig = $options['generator_builder']->toArray();

            foreach ($generatorConfig['inputs'] as $inputConfig) {
                // Map generator input types to Symfony form types
                $formType = $this->mapInputTypeToSymfonyType($inputConfig['type']);

                $builder->add($inputConfig['name'], $formType, [
                    'label' => $inputConfig['label'],
                    'required' => $inputConfig['required'],
                    'attr' => $inputConfig['attributes'],
                ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'generator_builder' => null,
            'data_class' => null,
        ]);

        $resolver->setAllowedTypes('generator_builder', ['null', GeneratorFormBuilder::class]);
    }

    private function mapInputTypeToSymfonyType(string $type): string
    {
        return match ($type) {
            'text' => \Symfony\Component\Form\Extension\Core\Type\TextType::class,
            'email' => \Symfony\Component\Form\Extension\Core\Type\EmailType::class,
            'password' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class,
            'textarea' => \Symfony\Component\Form\Extension\Core\Type\TextareaType::class,
            'number' => \Symfony\Component\Form\Extension\Core\Type\NumberType::class,
            'date' => \Symfony\Component\Form\Extension\Core\Type\DateType::class,
            'time' => \Symfony\Component\Form\Extension\Core\Type\TimeType::class,
            'datetime-local' => \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,
            'select' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
            'checkbox' => \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class,
            'radio' => \Symfony\Component\Form\Extension\Core\Type\RadioType::class,
            'file' => \Symfony\Component\Form\Extension\Core\Type\FileType::class,
            'hidden' => \Symfony\Component\Form\Extension\Core\Type\HiddenType::class,
            'submit' => \Symfony\Component\Form\Extension\Core\Type\SubmitType::class,
            default => \Symfony\Component\Form\Extension\Core\Type\TextType::class,
        };
    }
}
