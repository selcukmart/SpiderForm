<?php

/**
 * FormGenerator V2 - Doctrine Integration Example
 *
 * This example shows how to use Doctrine ORM for data population
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\DataProvider\DoctrineDataProvider;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Contracts\ScopeType;
use Doctrine\ORM\EntityManager;

// Assume you have EntityManager configured
/** @var EntityManager $entityManager */
// $entityManager = getEntityManager(); // Your Doctrine setup

// Create data provider for User entity
// $userProvider = new DoctrineDataProvider($entityManager, User::class);

// Create data provider for Country options
// $countryProvider = new DoctrineDataProvider($entityManager, Country::class);

$renderer = new TwigRenderer(
    templatePaths: __DIR__ . '/../../src/V2/Theme/templates'
);

$theme = new Bootstrap5Theme();

// Build edit form with Doctrine data
$userId = 1; // Example user ID

$form = FormBuilder::create('user_edit')
    ->setAction('/users/' . $userId . '/update')
    ->setMethod('POST')
    ->setScope(ScopeType::EDIT)
    ->setRenderer($renderer)
    ->setTheme($theme)
    // ->setDataProvider($userProvider)
    // ->loadData($userId) // Load user data from Doctrine

    ->addHidden('id', $userId)

    ->addText('username', 'Username')
        ->readonly() // Username cannot be changed
        ->add()

    ->addText('firstName', 'First Name')
        ->required()
        ->add()

    ->addText('lastName', 'Last Name')
        ->required()
        ->add()

    ->addEmail('email', 'Email')
        ->required()
        ->add()

    ->addSelect('country', 'Country')
        ->required()
        // Load options from Doctrine
        // ->optionsFromProvider($countryProvider, 'code', 'name')
        ->options([
            'US' => 'United States',
            'UK' => 'United Kingdom',
        ])
        ->add()

    ->addTextarea('bio', 'Biography')
        ->attribute('rows', 5)
        ->helpText('Tell us about yourself')
        ->add()

    ->addSubmit('update', 'Update Profile')
    ->addButton('cancel', 'Cancel')

    ->build();

echo $form;

// Example showing how it integrates with Symfony Form Component
/*
use FormGenerator\V2\Integration\Symfony\FormType\FormGeneratorType;
use Symfony\Component\Form\FormFactoryInterface;

$symfonyForm = $formFactory->create(FormGeneratorType::class, $user, [
    'generator_builder' => $form,
]);
*/
