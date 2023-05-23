<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 
/* 
| ------------------------------------------------------------------- 
|  Stripe API Configuration 
| ------------------------------------------------------------------- 
| 
| You will get the API keys from Developers panel of the Stripe account 
| Login to Stripe account (https://dashboard.stripe.com/) 
| and navigate to the Developers » API keys page 
| Remember to switch to your live publishable and secret key in production! 
| 
|  stripe_api_key            string   Your Stripe API Secret key. 
|  stripe_publishable_key    string   Your Stripe API Publishable key. 
|  stripe_currency           string   Currency code. 
*/ 
// $config['stripe_api_key']         = 'sk_test_51N8Hi2Kz246r0iqsHBomNS20MyABREW4huTmno8W4yBnAfs5jHRWNPJOzDAoapr7lHINtlFpX2Gg5cGvLSVHXAAd00KroX0IHk'; 
// $config['stripe_publishable_key'] = 'pk_test_51N8Hi2Kz246r0iqsWc1zGhY1FvpkTjLaU17z0U4IVleROBmwcKlFoC5OSgNG6FQl8h1tRpszNJkMjqOmzEaMl6uE00Pufg2sMZ'; 
$config['stripe_api_key']         = 'sk_test_51N3E5AKEh1CBAftmuof4dwMJRE2lgHv9Li23mhNbcLDFzVtp84QZYzXbDFYj8moZSDcNRuPjsCou37rSm6If3DJ000EvM8epxj'; 
$config['stripe_publishable_key'] = 'pk_test_51N3E5AKEh1CBAftmLvkK7vzZbJSlVgI2M6VKnMK64IH7s5dB4NM0qj1rZWf3DGBozSkBibemjYwinxiy9f9kTon800qyGxP2aY'; 
$config['endpoint_secret'] = 'whsec_GGlkPWGc7pMzNXKZLU8x1Q3qKbrDohU0'; 
$config['stripe_currency']        = 'usd';