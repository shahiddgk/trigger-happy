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
        $this->CI->load->model('common_model'); 

        // Include the Stripe PHP bindings library 
        require APPPATH .'third_party/stripe-php/init.php'; 
         
        // Set API key 
		$result_array = $this->CI->common_model->select_all("*", 'payment_settings')->row_array();
        
        \Stripe\Stripe::setApiKey($result_array['stripe_live_key']); 
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
        $priceCents = ($planPrice*100); 
        $currency = 'usd'; 
         
        try { 
            $plan = \Stripe\Plan::create(array( 
                "product" => [ 
                    "name" => $planName 
                ], 
                "amount" => $priceCents, 
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

    function cancelSuscription($subscriptionID){
        try { 
            $subscription = \Stripe\Subscription::retrieve($subscriptionID); 
            return $subscription->cancel(); 
        }catch(Exception $e) { 
            return $e->getMessage(); 
        }
    }

    function updateSuscription($subscriptionID){
        try { 
            $subscription = \Stripe\Subscription::retrieve($subscriptionID); 
            return $subscription; 
            
        }catch(Exception $e) { 
            return $e->getMessage(); 
        }
    }

    function getSubscribersList(){
        try { 
            $subscription = \Stripe\Subscription::all(['expand' => ['data.customer', 'data.plan']]); 
            return $subscription; 
            
        }catch(Exception $e) { 
            return array(); 
        }
    }
}