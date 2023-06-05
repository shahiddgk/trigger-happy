<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'third_party/stripe-php/init.php';

use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class Stripe_webhook extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $CI =& get_instance();

        // Load the email configuration file
        $this->load->library('email');
        $CI->config->load('email', TRUE);
        $this->smtp_user = $CI->config->item('smtp_user', 'email');      
        
        $CI->config->load('stripe', TRUE);
        $this->endpoint_secret = $CI->config->item('endpoint_secret', 'stripe');       
    }

    public function index()
    {   $endpoint_secret = $this->endpoint_secret;
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
            case 'invoice.payment_succeeded':
                $this->paymentSucceeded($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->paymentFailed($event->data->object);
                break;
        }
    }

    private function paymentSucceeded($data)
    {
        $status = $data->status;
        $billing_reason = $data->billing_reason;

        // Condition to vaerify payment is for <= 2nd iteration of subscription
        if($status == 'paid' && $billing_reason == 'subscription_cycle'){
            $strp_customer = $data->customer;
            $user_id = $this->common_model->select_single_field("user_id", "user_subscriptions", array('stripe_customer_id'=>$strp_customer));

            $customer_name = $data->customer_name;
            $customer_email = $data->customer_email;
            $amount_due = $data->amount_due/100;
            $amount_paid = $data->amount_paid/100;
           
            // Single Item
            $InvoiceLineItem = $data->lines->data[0];
            $interval = $InvoiceLineItem->plan->interval;
            $interval_count = $InvoiceLineItem->plan->interval_count;
            $currency = $InvoiceLineItem->plan->currency;
            $subscription = $InvoiceLineItem->subscription;
            $type = $InvoiceLineItem->type;
            

            $period_start =  date('Y-m-d H:i:s', $data->period_start);
            $period_end =  date('Y-m-d H:i:s', $data->period_end);

            $sub_data['user_id'] = $user_id;
            $sub_data['payment_method'] = 'stripe';
            $sub_data['stripe_subscription_id'] = $subscription;
            $sub_data['stripe_customer_id'] = $strp_customer;
            $sub_data['plan_amount'] = $amount_paid;
            $sub_data['plan_amount_currency'] = $currency;
            $sub_data['plan_interval'] = $interval;
            $sub_data['plan_interval_count'] = $interval_count;
            $sub_data['plan_period_start'] = $period_start;
            $sub_data['plan_period_end'] = $period_end;
            $sub_data['payer_email'] = $customer_email;
            $sub_data['status'] = $status;

            $this->common_model->insert_array('user_subscriptions', $sub_data);

            $last_insert_id = $this->db->insert_id(); 
            $sub_data['id'] = $last_insert_id;

            if(!empty($last_insert_id)){
                $update['is_premium'] = 'yes';
                $update['premium_type'] = $interval;
                $this->common_model->update_array(array('id'=> $user_id), 'users', $update);
            }
        }
        else{
            return false;
        }
    }

    private function paymentFailed($data)
    {
        if($data->billing_reason == 'subscription_cycle'){
            $customer_id = $data->customer;
            $subscription_id = $data->subscription;
            $customer_name = $data->customer_name;
            $customer_email = $data->customer_email;

            // Single Item
            $InvoiceLineItem = $data->lines->data[0];
            $plan_name = ucfirst($InvoiceLineItem->plan->interval);

            $user = $this->common_model->select_where("*", "user_subscriptions", array('stripe_subscription_id'=>$subscription_id, 'stripe_customer_id'=>$customer_id))->row_array();

            if(!empty($user)){
                $user_id = $user['user_id'];
                $update['is_premium'] = 'no';
                $update['premium_type'] = '';
                $this->common_model->update_array(array('id'=> $user_id), 'users', $update);
            }
            $title = 'Action Required: Recursive Payment Failed for Your Stripe Subscription';
            $message = '
            Dear '. $customer_name. ' <br>,
            
            We regret to inform you that the recurring payment for your Stripe subscription plan has failed. To avoid any interruption to your service, please take immediate action to update your payment information. <br>
            
            Action Required:<br>
            1. Contact Burgeon Support. <br>
            
            Your prompt attention to this matter is appreciated. <br>
            
            We apologize for any inconvenience caused and thank you for your cooperation.<br>
            
            Best regards,<br>
            
            Burgeon App';
            $this->send_email($title, $message, $customer_email);

            $admin_title = 'Action Required: Recursive Payment Failed:';
            $admin_message = '<b> User Details: </b> <br>';
            $admin_message .= 'Name: '. $customer_name. '<br>';
            $admin_message .= 'Email: '. $customer_email. '<br>';
            $admin_message .= 'Subscription ID: '. $subscription_id. '<br>';
            $admin_message .= 'Plan: '. $plan_name. '<br>';

            $this->send_email($admin_title, $admin_message, 'info@burgeon.app');

        }else{
            return false;
        }
    }

    private function send_email($subject, $message, $to_email)
    {
        $this->email->set_newline("\r\n");
        $this->email->set_mailtype('html');
        $this->email->from($this->smtp_user, 'Burgeon');
        $this->email->to($to_email);
        $this->email->subject($subject);
        $this->email->message($message);
        if($this->email->send()) {
            echo 'success'; exit;
        }
        else{
            echo $this->email->print_debugger(); exit;
        }
    }

    // private function createLog($message)
    // {
    //     $log_file = APPPATH . 'logs/stripe_webhooks_' . time(). '.log';
    //     $timestamp = date('Y-m-d H:i:s');
    //     $log_message = "[$timestamp] $message\n";

    //     file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    // }
}
