<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 
 
/** 
 * Stripe Library for CodeIgniter 3.x 
 * 
 * Library for Stripe payment gateway. It helps to integrate Stripe payment gateway 
 * in CodeIgniter application. 
 * 
 * This library requires the Stripe PHP bindings and it should be placed in the third_party folder. 
 * It also requires Stripe API configuration file and it should be placed in the config directory. 
 */ 
 
class Stripe_lib{ 
    var $CI; 
    var $api_error; 
     
    function __construct(){ 
        $this->api_error = ''; 
        $this->CI =& get_instance(); 
        $this->CI->load->config('stripe'); 
         
        // Include the Stripe PHP bindings library 
        require APPPATH .'third_party/stripe-php/init.php'; 
         
        // Set API key 
        \Stripe\Stripe::setApiKey($this->CI->config->item('stripe_api_key')); 
    } 
 
    function addCustomer($name, $email, $token){ 
        try { 
            $customer = \Stripe\Customer::create(array( 
                'name' => $name, 
                'email' => $email, 
                'source'  => $token 
            )); 
            return $customer; 
        }catch(Exception $e) { 
            return  $e->getMessage(); 
        } 
    } 
     
    function createPlan($planName, $planPrice, $planInterval){ 
        // Convert price to cents 
        // $priceCents = ($planPrice*100); 
        $currency = $this->CI->config->item('stripe_currency'); 
         
        try { 
            $plan = \Stripe\Plan::create(array( 
                "product" => [ 
                    "name" => $planName 
                ], 
                "amount" => $planPrice, 
                "currency" => $currency, 
                "interval" => $planInterval, 
                "interval_count" => 1 
            )); 
            return $plan; 
        }catch(Exception $e) { 
            return $e->getMessage(); 
        } 
    } 
     
    function createSubscription($customerID, $planID){ 
        try { 
            $subscription = \Stripe\Subscription::create(array( 
                "customer" => $customerID, 
                "items" => array( 
                    array( 
                        "plan" => $planID 
                    ), 
                ), 
            )); 
             
            $subsData = $subscription->jsonSerialize(); 
            return $subsData; 
        }catch(Exception $e) { 
            return $e->getMessage(); 
        } 
    } 
}