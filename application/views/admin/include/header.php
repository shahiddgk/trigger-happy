
<?php 
if($this->session->userdata('user_logged_in')){
    if($this->session->userdata('usertype') == 'user'){
       redirect(site_url()); 
    }
}
else{
    $this->session->set_userdata('msg','Login to access your account');
        redirect(site_url()); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Burgeon</title>
	<!-- core:css -->
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/core/core.css">
	<!-- endinject -->
	<!-- plugin css for this page -->
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/select2/select2.min.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/jquery-tags-input/jquery.tagsinput.min.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/dropzone/dropzone.min.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/dropify/dist/dropify.min.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/bootstrap-colorpicker/bootstrap-colorpicker.min.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/tempusdominus-bootstrap-4/tempusdominus-bootstrap-4.min.css">
	<!-- end plugin css for this page -->
	<!-- inject:css -->
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/fonts/feather-font/css/iconfont.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/flag-icon-css/css/flag-icon.min.css">
	<!-- endinject -->
	<!-- DataTable -->
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
	<!-- DataTable End -->
  <!-- Layout styles -->  
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/css/style.css">
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/css/custom.css">
  <!-- End layout styles -->
  <link rel="shortcut icon" href="<?= base_url()?>assets/admin/images/favicon.png" />
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.16.10/dist/xlsx.full.min.js"></script>
</head>


<body class="sidebar-dark">

<div class="main-wrapper">

	<!-- partial:partials/_sidebar.html -->
	<nav class="sidebar">
		<div class="sidebar-header">
		<a href="#" class="sidebar-brand">
			<img src="<?= base_url('uploads/final_logo.png') ?>" alt="Logo" height="" width="150">
		</a>
		<div class="sidebar-toggler not-active">
		<span></span>
		<span></span>
		<span></span>
		</div>
		</div>
		<div class="sidebar-body">
		<ul class="nav">
			<li class="nav-item nav-category">Main</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo site_url('admin/dashboard'); ?>">
				<i class="link-icon" data-feather="box"></i>
				<span class="link-title">Dashboard</span>
				</a>
			</li>
			<li class="nav-item nav-category">web apps</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="collapse" href="#general-pages" role="button" aria-expanded="false" aria-controls="general-pages">
				<i class="link-icon" data-feather="book"></i>
				<span class="link-title">Questions</span>
				<i class="link-arrow" data-feather="chevron-down"></i>
				</a>
				<div class="collapse" id="general-pages">
				<ul class="nav sub-menu">
					<li class="nav-item">
						<a href="<?= base_url('admin/add_question'); ?>" class="nav-link">Add Question</a>
					</li>
					<li class="nav-item">
						<a href="<?= base_url('admin/questions'); ?>" class="nav-link">Questions List</a>
					</li>
					<!-- <li class="nav-item">
						<a href="<?= base_url('admin/pire_positive'); ?>" class="nav-link">Pire Positive</a>
					</li> -->
				</ul>
				</div>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="collapse" href="#user-pages" role="button" aria-expanded="false" aria-controls="user-pages">
				<i class="link-icon" data-feather="users"></i>
				<span class="link-title">Users</span>
				<i class="link-arrow" data-feather="chevron-down"></i>
				</a>
				<div class="collapse" id="user-pages">
				<ul class="nav sub-menu">
					<li class="nav-item">
						<a href="<?= base_url('admin/users_list') ?>" class="nav-link">Users</a>
					</li>
					<li class="nav-item">
						<a href="<?= base_url('admin/users_by_date') ?>" class="nav-link">Response By Date</a>
					</li>
				</ul>
				</div>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo site_url('admin/trellis'); ?>">
				<i class="link-icon" data-feather="layout"></i>
				<span class="link-title">Trellis</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo site_url('admin/settings'); ?>">
				<i class="link-icon" data-feather="settings"></i>
				<span class="link-title">Settings</span>
				</a>
			</li>

			<li class="nav-item">
				<a class="nav-link" href="<?php echo site_url('admin/sage_list'); ?>">
				<i class="link-icon" data-feather="message-square"></i>
				<span class="link-title">Feddback</span>
				</a>
			</li>
			
			<!-- <li class="nav-item">
				<a class="nav-link" href="<?php echo site_url('admin/chat_demo'); ?>">
				<i class="link-icon" data-feather="message-square"></i>
				<span class="link-title">Chat</span>
				</a>
			</li> -->

			<li class="nav-item nav-category">Reports</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo site_url('admin/user_activity'); ?>">
				<i class="link-icon" data-feather="book"></i>
				<span class="link-title">User Activity</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo site_url('admin/naq_report'); ?>">
				<i class="link-icon" data-feather="file-minus"></i>
				<span class="link-title">NAQ Report </span>
				</a>
			</li>			
		</ul>
		</div>
	</nav>
	<!-- partial -->

	<div class="page-wrapper">
				
		<!-- partial:partials/_navbar.html -->
		<nav class="navbar">
			<a href="#" class="sidebar-toggler">
				<i data-feather="menu"></i>
			</a>
			<div class="navbar-content">
				<form class="search-form">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								<i data-feather="search"></i>
							</div>
						</div>
						<input type="text" class="form-control" id="navbarForm" placeholder="Search here...">
					</div>
				</form>
				<ul class="navbar-nav">
					<li class="nav-item dropdown nav-profile">
						<a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<img src="<?= base_url().'uploads/profile/'.$this->session->userdata('userimage')?>" alt="Admin" height="50" width="50">
						</a>
						<div class="dropdown-menu" aria-labelledby="profileDropdown">
							<div class="dropdown-header d-flex flex-column align-items-center">
								<div class="figure mb-3">
									<img src="<?= base_url().'uploads/profile/'.$this->session->userdata('userimage')?>" alt="Admin" height="80" width="80">
								</div>
								<div class="info text-center">
									<p class="name font-weight-bold mb-0"><?= ucfirst($this->session->userdata('username')); ?></p>
									<p class="email text-muted mb-3"><?= $this->session->userdata('useremail'); ?></p>
								</div>
							</div>
							<div class="dropdown-body">
								<ul class="profile-nav p-0 pt-3">
									<li class="nav-item">
										<a href="<?= base_url('admin/edit_profile')?>" class="nav-link">
											<i data-feather="edit"></i>
											<span>Edit Profile</span>
										</a>
									</li>
									<li class="nav-item">
										<a href="<?= base_url('admin/logout')?>" class="nav-link">
											<i data-feather="log-out"></i>
											<span>Log Out</span>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</nav>
		<!-- partial -->
