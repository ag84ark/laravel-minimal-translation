<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    // Url for accessing the interface
    'route' => 'ag84ark/translations',

    // Middleware to apply to route
    'route_middleware' => ['web'],

    // Base file for translations ( base => resources/lang/base.json)
    // Do not use language files and don't add the .json extension
    'base_file' => 'base',

    // Supported language for translation module
    'supported_languages' => ['fr', 'en'],

    // The main language to load for translation
    'main_language' => 'en',

    // The layout to extend, default layout
    'extend_layout' => 'layouts.app',

    // The layout to extend, default layout
    'content_section' => 'content',

    // The interface is made with tailwind
    'import_tailwind' => true,


    // Command settings
    'scan_vue' => true,
    'scan_server' => true,

    'vue_paths' => [public_path('js'), resource_path('js')],
    'server_paths' => [app_path(), resource_path()],

    'vue_i18n_functions' => ['\$t', 'this.\$t'],
    'server_i18n_functions' => ['trans', '__', '@lang'],

    // This is a second varient for searching (experimental)
    'enable_second_search' => false,
];
