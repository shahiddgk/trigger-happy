<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config = array(
    'protocol'  => 'ssmtp', // 'mail', 'sendmail', or 'smtp'
    'smtp_host' => 'ssl://ssmtp.googlemail.com',
    'smtp_port' =>  465,
    'smtp_user' => 'mustaqim.ratedsolution@gmail.com',
    'smtp_pass' => 'RatedMail*135',
    'mailtype'  => 'html', //plaintext 'text' mails or 'html'
    // 'smtp_timeout' => '10', //in seconds
    'charset' => 'iso-8859-1',
    'wordwrap' => TRUE
);