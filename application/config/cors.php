<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Allow requests from any origin
header('Access-Control-Allow-Origin: *');

// Allow the following HTTP methods
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');

// Allow the following headers
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Allow cookies to be sent in cross-origin requests
header('Access-Control-Allow-Credentials: true');
