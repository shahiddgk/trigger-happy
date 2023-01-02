
<?php
if($this->session->userdata('user_logged_in')){
    if($this->session->userdata('usertype')!='admin'){
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
	<title>Trigger Happy</title>
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
  <!-- Layout styles -->  
	<link rel="stylesheet" href="<?= base_url()?>assets/admin/css/demo_1/style.css">
  <!-- End layout styles -->
  <link rel="shortcut icon" href="<?= base_url()?>assets/admin/images/favicon.png" />
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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
		<a href="<?= base_url('admin/dashboard'); ?>" class="nav-link">
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
		</ul>
		</div>
	</li>
	
	</ul>
	</div>
	</nav>
	<nav class="settings-sidebar">
	<div class="sidebar-body">
	<!-- <a href="#" class="settings-sidebar-toggler">
		<i data-feather="settings"></i>
	</a> -->
	<h6 class="text-muted">Sidebar:</h6>
	<div class="form-group border-bottom">
	<div class="form-check form-check-inline">
		<label class="form-check-label">
		<input type="radio" class="form-check-input" name="sidebarThemeSettings" id="sidebarLight" value="sidebar-light" checked>
		Light
		</label>
	</div>
	<div class="form-check form-check-inline">
		<label class="form-check-label">
		<input type="radio" class="form-check-input" name="sidebarThemeSettings" id="sidebarDark" value="sidebar-dark">
		Dark
		</label>
	</div>
	</div>
	<div class="theme-wrapper">
	<h6 class="text-muted mb-2">Light Theme:</h6>
	<a class="theme-item active" href="<?= base_url()?>demo_1/dashboard-one.html">
		<img src="<?= base_url()?>assets/admin/images/screenshots/light.jpg" alt="light theme">
	</a>
	<h6 class="text-muted mb-2">Dark Theme:</h6>
	<a class="theme-item" href="<?= base_url()?>demo_2/dashboard-one.html">
		<img src="<?= base_url()?>assets/admin/images/screenshots/dark.jpg" alt="light theme">
	</a>
	</div>
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
					<!-- <li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="flag-icon flag-icon-us mt-1" title="us"></i> <span class="font-weight-medium ml-1 mr-1">English</span>
						</a>
						<div class="dropdown-menu" aria-labelledby="languageDropdown">
			<a href="javascript:;" class="dropdown-item py-2"><i class="flag-icon flag-icon-us" title="us" id="us"></i> <span class="ml-1"> English </span></a>
			<a href="javascript:;" class="dropdown-item py-2"><i class="flag-icon flag-icon-fr" title="fr" id="fr"></i> <span class="ml-1"> French </span></a>
			<a href="javascript:;" class="dropdown-item py-2"><i class="flag-icon flag-icon-de" title="de" id="de"></i> <span class="ml-1"> German </span></a>
			<a href="javascript:;" class="dropdown-item py-2"><i class="flag-icon flag-icon-pt" title="pt" id="pt"></i> <span class="ml-1"> Portuguese </span></a>
			<a href="javascript:;" class="dropdown-item py-2"><i class="flag-icon flag-icon-es" title="es" id="es"></i> <span class="ml-1"> Spanish </span></a>
						</div>
		</li>
					<li class="nav-item dropdown nav-apps">
						<a class="nav-link dropdown-toggle" href="#" id="appsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i data-feather="grid"></i>
						</a>
						<div class="dropdown-menu" aria-labelledby="appsDropdown">
							<div class="dropdown-header d-flex align-items-center justify-content-between">
								<p class="mb-0 font-weight-medium">Web Apps</p>
								<a href="javascript:;" class="text-muted">Edit</a>
							</div>
							<div class="dropdown-body">
								<div class="d-flex align-items-center apps">
									<a href="pages/apps/chat.html"><i data-feather="message-square" class="icon-lg"></i><p>Chat</p></a>
									<a href="pages/apps/calendar.html"><i data-feather="calendar" class="icon-lg"></i><p>Calendar</p></a>
									<a href="pages/email/inbox.html"><i data-feather="mail" class="icon-lg"></i><p>Email</p></a>
									<a href="pages/general/profile.html"><i data-feather="instagram" class="icon-lg"></i><p>Profile</p></a>
								</div>
							</div>
							<div class="dropdown-footer d-flex align-items-center justify-content-center">
								<a href="javascript:;">View all</a>
							</div>
						</div>
					</li>
					<li class="nav-item dropdown nav-messages">
						<a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i data-feather="mail"></i>
						</a>
						<div class="dropdown-menu" aria-labelledby="messageDropdown">
							<div class="dropdown-header d-flex align-items-center justify-content-between">
								<p class="mb-0 font-weight-medium">9 New Messages</p>
								<a href="javascript:;" class="text-muted">Clear all</a>
							</div>
							<div class="dropdown-body">
								<a href="javascript:;" class="dropdown-item">
									<div class="figure">
										<img src="https://via.placeholder.com/30x30" alt="userr">
									</div>
									<div class="content">
										<div class="d-flex justify-content-between align-items-center">
											<p>Leonardo Payne</p>
											<p class="sub-text text-muted">2 min ago</p>
										</div>	
										<p class="sub-text text-muted">Project status</p>
									</div>
								</a>
								<a href="javascript:;" class="dropdown-item">
									<div class="figure">
										<img src="https://via.placeholder.com/30x30" alt="userr">
									</div>
									<div class="content">
										<div class="d-flex justify-content-between align-items-center">
											<p>Carl Henson</p>
											<p class="sub-text text-muted">30 min ago</p>
										</div>	
										<p class="sub-text text-muted">Client meeting</p>
									</div>
								</a>
								<a href="javascript:;" class="dropdown-item">
									<div class="figure">
										<img src="https://via.placeholder.com/30x30" alt="userr">
									</div>
									<div class="content">
										<div class="d-flex justify-content-between align-items-center">
											<p>Jensen Combs</p>												
											<p class="sub-text text-muted">1 hrs ago</p>
										</div>	
										<p class="sub-text text-muted">Project updates</p>
									</div>
								</a>
								<a href="javascript:;" class="dropdown-item">
									<div class="figure">
										<img src="https://via.placeholder.com/30x30" alt="userr">
									</div>
									<div class="content">
										<div class="d-flex justify-content-between align-items-center">
											<p>Amiah Burton</p>
											<p class="sub-text text-muted">2 hrs ago</p>
										</div>
										<p class="sub-text text-muted">Project deadline</p>
									</div>
								</a>
								<a href="javascript:;" class="dropdown-item">
									<div class="figure">
										<img src="https://via.placeholder.com/30x30" alt="userr">
									</div>
									<div class="content">
										<div class="d-flex justify-content-between align-items-center">
											<p>Yaretzi Mayo</p>
											<p class="sub-text text-muted">5 hr ago</p>
										</div>
										<p class="sub-text text-muted">New record</p>
									</div>
								</a>
							</div>
							<div class="dropdown-footer d-flex align-items-center justify-content-center">
								<a href="javascript:;">View all</a>
							</div>
						</div>
					</li>
					<li class="nav-item dropdown nav-notifications">
						<a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i data-feather="bell"></i>
							<div class="indicator">
								<div class="circle"></div>
							</div>
						</a>
						<div class="dropdown-menu" aria-labelledby="notificationDropdown">
							<div class="dropdown-header d-flex align-items-center justify-content-between">
								<p class="mb-0 font-weight-medium">6 New Notifications</p>
								<a href="javascript:;" class="text-muted">Clear all</a>
							</div>
							<div class="dropdown-body">
								<a href="javascript:;" class="dropdown-item">
									<div class="icon">
										<i data-feather="user-plus"></i>
									</div>
									<div class="content">
										<p>New customer registered</p>
										<p class="sub-text text-muted">2 sec ago</p>
									</div>
								</a>
								<a href="javascript:;" class="dropdown-item">
									<div class="icon">
										<i data-feather="gift"></i>
									</div>
									<div class="content">
										<p>New Order Recieved</p>
										<p class="sub-text text-muted">30 min ago</p>
									</div>
								</a>
								<a href="javascript:;" class="dropdown-item">
									<div class="icon">
										<i data-feather="alert-circle"></i>
									</div>
									<div class="content">
										<p>Server Limit Reached!</p>
										<p class="sub-text text-muted">1 hrs ago</p>
									</div>
								</a>
								<a href="javascript:;" class="dropdown-item">
									<div class="icon">
										<i data-feather="layers"></i>
									</div>
									<div class="content">
										<p>Apps are ready for update</p>
										<p class="sub-text text-muted">5 hrs ago</p>
									</div>
								</a>
								<a href="javascript:;" class="dropdown-item">
									<div class="icon">
										<i data-feather="download"></i>
									</div>
									<div class="content">
										<p>Download completed</p>
										<p class="sub-text text-muted">6 hrs ago</p>
									</div>
								</a>
							</div>
							<div class="dropdown-footer d-flex align-items-center justify-content-center">
								<a href="javascript:;">View all</a>
							</div>
						</div>
					</li> -->
					<li class="nav-item dropdown nav-profile">
						<a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<img src="https://via.placeholder.com/30x30" alt="userr">
						</a>
						<div class="dropdown-menu" aria-labelledby="profileDropdown">
							<div class="dropdown-header d-flex flex-column align-items-center">
								<div class="figure mb-3">
									<img src="https://via.placeholder.com/80x80" alt="">
								</div>
								<div class="info text-center">
									<p class="name font-weight-bold mb-0"><?= ucfirst($this->session->userdata('username')); ?></p>
									<p class="email text-muted mb-3"><?= $this->session->userdata('useremail'); ?></p>
								</div>
							</div>
							<div class="dropdown-body">
								<ul class="profile-nav p-0 pt-3">
									<li class="nav-item">
										<a href="javascript:;" class="nav-link">
											<i data-feather="user"></i>
											<span>Profile</span>
										</a>
									</li>
									<li class="nav-item">
										<a href="javascript:;" class="nav-link">
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
