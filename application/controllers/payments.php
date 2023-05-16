<?php
defined('BASEPATH') OR exit('No direct script access allowed');


use Stripe\Stripe;
use Stripe\Subscription;

class Payments extends CI_Controller {

    public function __construct(){
		parent::__construct();

        require_once(FCPATH.'vendor/autoload.php');

		require_once FCPATH.'vendor/Stripe/init.php';;
        \Stripe\Stripe::setApiKey('sk_test_51N3doDEHAlgcgaavVbiAq1TEgNAETCOJ1ujlXRnv6ZyiTDG6fvqdboGbkJ8oMIqWeCR2Kk3QMaDVt6dwcx86fVtO0087Wq0t35');

	}

    public function index() {
        $data['stripe_publishable_key'] = 'pk_test_51N3doDEHAlgcgaavwYcouJ1HDY3pvsuOa6dVGXB2b8X3XUZvBrw8muIkNqaqPQv27QWGrlwSrrxLB2WSzskbnddg00v7Eb7Jfj';
        $this->load->view('admin/include/header');
        $this->load->view('admin/payment_form', $data);
        $this->load->view('admin/include/footer');

    }

    public function charge(){


        try {
            \Stripe\Stripe::setApiKey('sk_test_51N3doDEHAlgcgaavVbiAq1TEgNAETCOJ1ujlXRnv6ZyiTDG6fvqdboGbkJ8oMIqWeCR2Kk3QMaDVt6dwcx86fVtO0087Wq0t35');

            // $customer = \Stripe\Customer::create([
            //     'source' => $token,
            //     'name' => $name,
            //     'email' => $email,
            // ]);

            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    [
                        'price' => 'price_1N7t8cEHAlgcgaavm0TJAZpf'
                    ],
                ],
            ]);

        $data = [
            'name' => $name,
            'email' => $email,
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'exp_year' => $expYear, 
            'status' => 'succeeded',
        ];

        $this->common_model->insert_array('payments', $data);

            echo '<script>alert("Payment was successful!");</script>';
        } catch (\Stripe\Exception\Card $e) {
            echo '<script>alert("Payment failed: ' . $e->getMessage() . '");</script>';
        }
    }

}