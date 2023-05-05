<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Payments extends CI_Controller {

    public function __construct()
	{
		parent::__construct();
        // require_once(APPPATH.'vendor/autoload.php');

		// require_once APPPATH.'vendor/Stripe/init.php';;
        // \Stripe\Stripe::setApiKey('sk_test_51N3doDEHAlgcgaavVbiAq1TEgNAETCOJ1ujlXRnv6ZyiTDG6fvqdboGbkJ8oMIqWeCR2Kk3QMaDVt6dwcx86fVtO0087Wq0t35');

	}

  public function index() {
    $data['stripe_publishable_key'] = 'pk_test_51N3doDEHAlgcgaavwYcouJ1HDY3pvsuOa6dVGXB2b8X3XUZvBrw8muIkNqaqPQv27QWGrlwSrrxLB2WSzskbnddg00v7Eb7Jfj';
    $this->load->view('admin/include/header');
    $this->load->view('admin/payment_form', $data);
    $this->load->view('admin/include/footer');

  }

public function charge() {

    $token = $this->input->post('stripeToken');

    try {
        \Stripe\Stripe::setApiKey('sk_test_51N3doDEHAlgcgaavVbiAq1TEgNAETCOJ1ujlXRnv6ZyiTDG6fvqdboGbkJ8oMIqWeCR2Kk3QMaDVt6dwcx86fVtO0087Wq0t35');

        $charge = \Stripe\Charge::create(array(
            'amount' => 5000,
            'currency' => 'usd',
            'source' => $token,
            'description' => 'Payment for something'
        ));

        $data = array(
            'customer_id' => $charge->customer,
            'card_id' => $charge->source->id,
            'card_brand' => $charge->source->brand,
            'card_last4' => $charge->source->last4,
        );
        $this->common_model->insert_array('payments', $data);

        echo '<script>alert("Payment was successful!");</script>';

    } catch (\Stripe\Error\Card $e) {

        echo '<script>alert("Payment failed: '.$e->getMessage().'");</script>';
    }

    // Redirect back to the payment page
    redirect('payments/index');
}
}
