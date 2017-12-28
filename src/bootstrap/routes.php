<?php

use App\Controllers\EmailSubscriptionController;

// Email subscriptions
$app->group('/email-subscriptions', function () {
    $this->get('', EmailSubscriptionController::class . ':index');
    $this->post('', EmailSubscriptionController::class . ':store');

    $this->group('/{id}', function () {
        $this->get('', EmailSubscriptionController::class . ':show');
        $this->patch('', EmailSubscriptionController::class . ':update');
        $this->delete('', EmailSubscriptionController::class . ':destroy');
    });
});