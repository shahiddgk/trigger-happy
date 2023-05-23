<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'third_party/stripe-php/init.php';

use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class Stripe_webhook extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        // Load the Stripe configuration
        $this->config->load('stripe');
        $endpoint_secret = $this->config->item('endpoint_secret');
    }

    public function index()
    {
        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            echo '⚠️  Webhook error while parsing basic request.';
            http_response_code(400);
            exit();
        }

        if ($endpoint_secret) {
            // Only verify the event if there is an endpoint secret defined
            // Otherwise use the basic decoded event
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $endpoint_secret
                );
            } catch(SignatureVerificationException $e) {
                // Invalid signature
                echo '⚠️  Webhook error while validating signature.';
                http_response_code(400);
                exit();
            }
        }

        switch ($event->type) {
            case 'customer.created':
                $this->handleCustomerCreatedEvent($event->data->object);
                break;
            case 'customer.subscription.created':
                $this->handlePaymentSucceededEvent($event->data->object);
                break;
        }
    }

    private function handleCustomerCreatedEvent($customer)
    {
        // Do something with the customer data
        // ...

        // Create a log
        $message = 'Customer created: ' . $customer->id;
        $this->createLog($message);
    }

    private function handlePaymentSucceededEvent($payment)
    {
        // Do something with the payment data
        // ...

        // Create a log
        $message = 'Payment succeeded: ' . $payment->id;
        $this->createLog($message);
    }

    private function createLog($message)
    {
        $log_file = APPPATH . 'logs/stripe_webhooks_' . time(). '.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message\n";

        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }
}
