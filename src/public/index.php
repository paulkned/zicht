<?php

// Autoload some stuff
require '../vendor/autoload.php';

// Then run app
$app = (new App\App())->get();
$app->run();