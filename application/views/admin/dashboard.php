
<div class="page-content">
  <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <div>
      <h4 class="mb-3 mb-md-0">Welcome to Dashboard</h4>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-xl-12 stretch-card">
      <div class="row flex-grow">
        <div class="col-md-4 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-baseline">
                <h6 class="card-title mb-0">Total Users</h6>
                <div class="dropdown mb-2">
                  <button class="btn p-0" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item d-flex align-items-center" href="<?= base_url('admin/users_list'); ?>"><i data-feather="eye" class="icon-sm mr-2"></i> <span class="">View</span></a>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-6 col-md-12 col-xl-5">
                  <!-- Hide test user for app from total count -->
                  <h3 class="mb-2"><?= $num_rows-1;?></h3>
                </div>
                <div class="col-6 col-md-12 col-xl-7">
                  <div id="apexChart1" class="mt-md-3 mt-xl-0"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
          <div class="card">
          </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
          <div class="card">
          </div>
        </div>
      </div>
    </div>
  </div> <!-- row -->

  <div class="row">
    <div class="col-12 col-xl-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-baseline mb-2">
            <h6 class="card-title mb-0">subscriptions</h6>
            <div class="dropdown mb-2">
              <button class="btn p-0" type="button" id="dropdownMenuButton7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton7">
                <a class="dropdown-item d-flex align-items-center" href="#"><i data-feather="printer" class="icon-sm mr-2"></i> <span class="">Print</span></a>
                <a class="dropdown-item d-flex align-items-center" href="#"><i data-feather="download" class="icon-sm mr-2"></i> <span class="">Download</span></a>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0 mt-5">
              <thead>
                <tr>
                  <th class="pt-0">#</th>
                  <th class="pt-0">User Name</th>
                  <th class="pt-0">Plan</th>
                  <th class="pt-0">Start Date</th>
                  <th class="pt-0">Due Date</th>
                  <th class="pt-0">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if(isset($subscriptions)){ foreach (@$subscriptions as $key => $value) {
                  $interval = $value['items']['data'][0]['plan']['interval'];  ?>
                <tr>
                  <td><?= $key+1 ?></td>
                  <td><?= $value['customer']['name'] ?></td>
                  <td><?= $duration = ($interval == 'day') ? 'Daily' : (ucfirst($interval). 'ly'); ?></td>
                  <td><?= date('d/m/Y', $value['created']) ?></td>
                  <td><?= date('d/m/Y', $value['current_period_end'])?></td>
                  <td><span class="badge badge-warning"><?= $value['status'] ?></span></td>
                </tr>
                <?php } }  ?>
              </tbody>
            </table>
          </div>
        </div> 
      </div>
    </div>
  </div> <!-- row -->

</div>


		

