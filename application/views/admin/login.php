<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Trigger Happy</title>
	<!-- core:css -->
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/core/core.css">
	<!-- endinject -->
  <!-- plugin css for this page -->
	<!-- end plugin css for this page -->
	<!-- inject:css -->
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/fonts/feather-font/css/iconfont.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/flag-icon-css/css/flag-icon.min.css">
	<!-- endinject -->
  <!-- Layout styles -->  
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/css/demo_1/style.css">
  <!-- End layout styles -->
  <link rel="shortcut icon" href="<?= base_url()?>assets/admin/images/favicon.png" />
</head>
     

<body class="sidebar-dark">
    <div class="main-wrapper">
        <div class="page-wrapper full-page bg-primary">
            <div class="page-content d-flex align-items-center justify-content-center">
                <div class="row w-100 mx-0 auth-page">
                    <div class="col-md-8 col-xl-6 mx-auto">
                        <div class="card">
                            <div class="row">
                                <div class="col-md-4 pr-md-0">
                                    <div class="auth-left-wrapper">
                                    </div>
                                </div>
                            <div class="col-md-8 pl-md-0">
                                <div class="auth-form-wrapper px-4 py-5">
                                    <a href="#" class="noble-ui-logo d-block mb-2">		
                                        <img src="<?= base_url('uploads/final_logo.png') ?>" alt="Logo" height="" width="150">
                                    </a>
                                    <h5 class="text-muted font-weight-normal mb-4">Welcome back! Log in to your account.</h5>
                                    <form class="forms-sample" action="<?=base_url('admin/login')?>" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Email address</label>
                                            <input type="email" class="form-control" id="exampleInputEmail1" name="email" placeholder="Email">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">Password</label>
                                            <input type="password" class="form-control" id="exampleInputPassword1" name="password" autocomplete="current-password" placeholder="Password">
                                        </div>
                                        <!-- <div class="form-check form-check-flat form-check-primary">
                                            <label class="form-check-label">
                                            <input type="checkbox" name="remember_me" class="form-check-input">
                                            Remember me
                                            </label>
                                        </div> -->
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0 text-white">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

	<!-- core:js -->
	<script src="<?= base_url()?>assets/admin/vendors/core/core.js"></script>
	<script src="<?= base_url()?>assets/admin/vendors/feather-icons/feather.min.js"></script>
	<script src="<?= base_url()?>assets/admin/js/template.js"></script>
</body>
</html>