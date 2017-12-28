<?php

// Make sure DotEnv is initialized for local configuration settings
$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

// Initialize the configuration
$settingsDir = __DIR__ . '/settings/';
$settings = [
    'settings' => []
];

// Read all of the configuration files
foreach (scandir($settingsDir) as $filename) {
    $path = $settingsDir . $filename;

    if (is_file($path)) {
        $settingsPart = require $path;
        $settings['settings'] = array_merge($settings['settings'], $settingsPart);
    }
}

return $settings;