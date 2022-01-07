<?php

declare(strict_types=1);

return [

    // Manage autoload migrations
    'autoload_migrations' => true,

    // Addresses Database Tables
    'tables' => [
        'addresses' => 'addresses',
    ],

    // Addresses Model
    'models' => [
        'address' => \Grnspc\Addresses\Models\Address::class,
    ],

    // Addresses type flags
    'flags' => ['primary', 'billing', 'shipping'],

    // Addresses Geocoding Options
    'geocoding' => [
        'enabled' => false,
        'api_key' => env('GOOGLE_APP_KEY'),
    ],

    'rules' => [
        'label'             => ['nullable', 'string', 'max:150'],
        'given_name'        => ['nullable', 'string', 'max:150'],
        'family_name'       => ['nullable', 'string', 'max:150'],
        'organization'      => ['nullable', 'string', 'max:150'],
        'line_1'            => ['required', 'string', 'max:255'],
        'line_2'            => ['nullable', 'string', 'max:255'],
        'city'              => ['required', 'string', 'max:150'],
        'province'          => ['required', 'string', 'max:150'],
        'postal_code'       => ['required', 'string', 'max:150'],
        'country_code'      => ['required', 'alpha', 'size:2', 'country'],
        'latitude'          => ['nullable', 'numeric'],
        'longitude'         => ['nullable', 'numeric'],
    ],

];
