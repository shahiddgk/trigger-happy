<div class="page-content">

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Settings</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
            <h6 class="card-title">Trellis Settings</h6>
                <form class="forms-sample" action="<?=base_url('admin/trilles_settings');?>" method="post">
                    <div class="form-group">
                        <label for="goal">Goal</label>
                        <input value="<?php echo $trellis['goal'];?>" type="number" class="form-control" name="goal" autocomplete="off" placeholder="Goal">
                    </div>
                    <div class="form-group">
                        <label for="achievements">Achievements</label>
                        <input value="<?php echo $trellis['achievements'];?>" type="number" class="form-control" name="achievements" placeholder="Achievements">
                    </div>
                    <div class="form-group">
                        <label for="principle">Principle</label>
                        <input value="<?php echo $trellis['principle'];?>" type="number" class="form-control" name="principle" autocomplete="off" placeholder="Principle">
                    </div>
                    <div class="form-group">
                        <label for="rhythms">Rhythms</label>
                        <input value="<?php echo $trellis['rhythms'];?>" type="number" class="form-control" name="rhythms" autocomplete="off" placeholder="Rhythms">
                    </div>                    
                    <div class="form-group">
                        <label for="needs">Needs</label>
                        <input value="<?php echo $trellis['needs'];?>" type="number" class="form-control" name="needs" autocomplete="off" placeholder="Needs">
                    </div>                   
                     <div class="form-group">
                        <label for=" ">Identity</label>
                        <input value="<?php echo $trellis['identity'];?>"type="number" class="form-control" name="identity" autocomplete="off" placeholder="Identity">
                    </div>
                    <div class="form-group">
                        <label for="tribe">Tribe</label>
                        <input value="<?php echo $trellis['tribe'];?>" type="number" class="form-control" name="tribe" autocomplete="off" placeholder="Tribe">
                    </div>
					<button type="submit" class="btn btn-primary mr-2">Submit</button>
					<button class="btn btn-light">Cancel</button>
				</form>
            </div>
        </div>
    </div>
</div>

</div>
