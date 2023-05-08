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
                            <input value="<?= isset($trellis['goal']) ? $trellis['goal'] : '' ?>" type="number"
                                class="form-control" name="goal" autocomplete="off" placeholder="Goal">
                        </div>
                        <div class="form-group">
                            <label for="achievements">Achievements</label>
                            <input value="<?= isset($trellis['achievements']) ? $trellis['achievements'] : '' ?>"
                                type="number" class="form-control" name="achievements" placeholder="Achievements">
                        </div>
                        <div class="form-group">
                            <label for="principle">Principle</label>
                            <input value="<?= isset($trellis['principle']) ? $trellis['principle'] : '' ?>"
                                type="number" class="form-control" name="principle" autocomplete="off"
                                placeholder="Principle">
                        </div>
                        <div class="form-group">
                            <label for="rhythms">Rhythms</label>
                            <input value="<?= isset($trellis['rhythms']) ? $trellis['rhythms'] : '' ?>" type="number"
                                class="form-control" name="rhythms" autocomplete="off" placeholder="Rhythms">
                        </div>
                        <div class="form-group">
                            <label for="needs">Needs</label>
                            <input value="<?= isset($trellis['needs']) ? $trellis['needs'] : '' ?>" type="number"
                                class="form-control" name="needs" autocomplete="off" placeholder="Needs">
                        </div>
                        <div class="form-group">
                            <label for=" ">Identity</label>
                            <input value="<?= isset($trellis['identity']) ? $trellis['identity'] : '' ?>" type="number"
                                class="form-control" name="identity" autocomplete="off" placeholder="Identity">
                        </div>
                        <div class="form-group">
                            <label for="tribe">Tribe</label>
                            <input value="<?= isset($trellis['tribe']) ? $trellis['tribe'] : '' ?>" type="number"
                                class="form-control" name="tribe" autocomplete="off" placeholder="Tribe">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">Save</button>
                </div>
            </div>
        </div>


    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title"> App versions </h6>
                    <div class="form-group">
                        <label for="cur_apple">Currant  Apple</label>
                        <input value="<?= isset($trellis['cur_apple']) ? $trellis['cur_apple'] : '' ?>" type="tel"
                            class="form-control" name="cur_apple" placeholder="Currant  Apple">
                    </div>
                    <div class="form-group">
                        <label for="coming_apple">Coming Apple</label>
                        <input value="<?= isset($trellis['coming_apple']) ? $trellis['coming_apple'] : '' ?>"
                            type="tel" class="form-control" name="coming_apple" placeholder="Coming Apple">
                    </div>
                    <div class="form-group">
                        <label for="cur_playstore">Currant  Playstore</label>
                        <input value="<?= isset($trellis['cur_playstore']) ? $trellis['cur_playstore'] : '' ?>"
                            type="tel" class="form-control" name="cur_playstore" 
                            placeholder="Currant  Playstore">
                    </div>
                    <div class="form-group">
                        <label for="coming_playstore">Coming Playstore</label>
                        <input value="<?= isset($trellis['coming_playstore']) ? $trellis['coming_playstore'] : '' ?>"
                            type="tel" class="form-control" name="coming_playstore" 
                            placeholder="Coming Playstore">
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Save</button>
                </form>
            </div>
        </div>
    </div>
 </div>
</div>
