<div class="page-content">
    <div class="profile-page tx-13">
        <div class="row profile-body">
            <!-- left wrapper start -->
            <div class="d-none d-md-block col-md-4 col-xl-3 left-wrapper">
            </div>
            <!-- left wrapper end -->
            <!-- middle wrapper start -->
            <div class="col-md-8 col-xl-6 middle-wrapper">
                <div class="row">
                    <div class="col-md-12 stretch-card">
						<div class="card">
							<div class="card-body">
								<h6 class="card-title">Update Profile</h6>
                                <form method="post" action="<?php echo site_url('admin/update_profile'); ?>" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="control-label">Name</label>
                                                <input type="hidden" name="user_id" value="<?= $user_data['id'];?>">
                                                <input type="text" name="name" class="form-control" placeholder="Enter name" value="<?= $user_data['name'];?>">
                                            </div>
                                        </div><!-- Col -->
                                    </div><!-- Row -->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label">Email</label>
                                                <input type="email" name="email" class="form-control" placeholder="Enter email" value="<?= $user_data['email'];?>">
                                            </div>
                                        </div><!-- Col -->
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label">Password</label>
                                                <input type="password" name="password" class="form-control" autocomplete="off" placeholder="Password">
                                            </div>
                                        </div><!-- Col -->
                                    </div><!-- Row -->
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="control-label">Profile Image</label>
                                                <div class="custom-file">
                                                    <input type="file" name="profile_img" class="custom-file-input" id="profile-img">
                                                    <label class="custom-file-label" for="profile-img">Choose file</label>
                                                </div>
                                            </div>
                                        </div><!-- Col -->
                                    </div><!-- Row -->
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
							</div>
						</div>
                    </div>
                </div>
            </div>
            <!-- middle wrapper end -->
            <!-- right wrapper start -->
            <div class="d-none d-xl-block col-xl-3 right-wrapper">
            </div>
            <!-- right wrapper end -->
        </div>
    </div>
</div>