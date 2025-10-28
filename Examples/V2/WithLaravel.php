<?php

/**
 * FormGenerator V2 - Laravel Integration Example
 *
 * This file shows example usage within Laravel
 */

namespace App\Http\Controllers;

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\DataProvider\EloquentDataProvider;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Security\SecurityManager;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;

class UserController extends Controller
{
    public function create()
    {
        // Services are auto-injected via Laravel's container
        $renderer = app(TwigRenderer::class);
        $theme = app(Bootstrap5Theme::class);
        $security = app(SecurityManager::class);

        // Create Eloquent data provider for countries
        $countryProvider = new EloquentDataProvider(Country::class);

        $form = FormBuilder::create('user_create')
            ->setAction(route('users.store'))
            ->setMethod('POST')
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->setSecurity($security)

            ->addText('name', 'Full Name')
                ->required()
                ->add()

            ->addEmail('email', 'Email')
                ->required()
                ->add()

            ->addPassword('password', 'Password')
                ->required()
                ->minLength(8)
                ->add()

            ->addSelect('country_id', 'Country')
                ->required()
                ->optionsFromProvider($countryProvider, 'id', 'name')
                ->add()

            ->addSubmit('create', 'Create User')

            ->build();

        return view('users.create', ['form' => $form]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $renderer = app(TwigRenderer::class);
        $theme = app(Bootstrap5Theme::class);

        // Create Eloquent data provider
        $userProvider = new EloquentDataProvider(User::class);

        $form = FormBuilder::create('user_edit')
            ->setAction(route('users.update', $id))
            ->setMethod('PUT')
            ->edit() // Set edit mode
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->setDataProvider($userProvider)
            ->loadData($id) // Auto-load user data

            ->addText('name', 'Full Name')
                ->required()
                ->add()

            ->addEmail('email', 'Email')
                ->required()
                ->add()

            ->addSubmit('update', 'Update User')

            ->build();

        return view('users.edit', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}

// In your Blade template (resources/views/users/create.blade.php):
/*
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create User</h1>

        @formGenerator($form)
    </div>
@endsection

@section('styles')
    @formAssets($theme)
@endsection
*/

// Or using traditional Blade syntax:
/*
{!! $form !!}
*/
