<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Commands Paths
    |--------------------------------------------------------------------------
    |
    | This value determines the "paths" that should be loaded by the console's
    | kernel. Paths can be either relative or absolute, and they will be
    | loaded recursively.
    |
    */

    'paths' => [
        app_path('Console/Commands'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hidden Commands
    |--------------------------------------------------------------------------
    |
    | Your application commands will always be available, but there are certain
    | scenarios where you may want to "hide" commands from users. They will
    | still be available, but they won't show up when the user runs the
    | list command.
    |
    */

    'hidden' => [],

    /*
    |--------------------------------------------------------------------------
    | Removed Commands
    |--------------------------------------------------------------------------
    |
    | Alternatively, you may want to completely remove commands from the list.
    | The commands listed below will be removed from your application's list
    | of commands.
    |
    */

    'remove' => [
        'app:install',
        'make:command',
        'make:component',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Command
    |--------------------------------------------------------------------------
    |
    | Laravel Zero will always run the command specified below when no command name
    | is provided. Consider pointing this to a command that provides information
    | about your application and its available commands.
    |
    */

    'default' => '',
];