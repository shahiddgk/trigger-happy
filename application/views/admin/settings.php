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
                    <form class="forms-sample" action="<?=base_url('admin/trilles_settings');?>" method="post">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <label>Show Alias Name</label>
                            <div class="custom-control custom-switch">
                                <input name="show_alias_name" type="checkbox" value="1" class="custom-control-input" id="show_alias_name"
                                    <?php echo ( $trellis['show_alias_name'] == 'yes') ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="show_alias_name"></label>
                            </div>
                        </div>
                        
                        <h6 class="card-title">Trellis Settings</h6>
                        <div class="form-group">
                            <label for="goal"><b>Ladder:</b> Goals / Challenges</label>
                            <input value="<?= isset($trellis['goal']) ? $trellis['goal'] : '' ?>" type="number"
                                class="form-control" name="goal" autocomplete="off" placeholder="Goal">
                        </div>
                        <div class="form-group">
                            <label for="achievements"><b>Ladder:</b> Memories / Achievements</label>
                            <input value="<?= isset($trellis['achievements']) ? $trellis['achievements'] : '' ?>"
                                type="number" class="form-control" name="achievements" placeholder="Achievements">
                        </div>
                        <div class="form-group">
                            <label for="principle">Organizing Principles</label>
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
                    </form>
                </div>
            </div>
        </div>


    <div class="col-md-6 grid-margin">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title"> App Versions </h6>
                <form class="forms-sample" action="<?=base_url('admin/trilles_settings');?>" method="post">
                    <div class="form-group">
                        <label for="cur_apple">Current  Apple</label>
                        <input value="<?= isset($trellis['cur_apple']) ? $trellis['cur_apple'] : '' ?>" type="tel"
                            class="form-control" name="cur_apple" placeholder="Currant  Apple">
                    </div>
                    <div class="form-group">
                        <label for="coming_apple">Incoming Apple</label>
                        <input value="<?= isset($trellis['coming_apple']) ? $trellis['coming_apple'] : '' ?>"
                            type="tel" class="form-control" name="coming_apple" placeholder="Coming Apple">
                    </div>
                    <div class="form-group">
                        <label for="cur_playstore">Current  Playstore</label>
                        <input value="<?= isset($trellis['cur_playstore']) ? $trellis['cur_playstore'] : '' ?>"
                            type="tel" class="form-control" name="cur_playstore" 
                            placeholder="Currant  Playstore">
                    </div>
                    <div class="form-group">
                        <label for="coming_playstore">Incoming Playstore</label>
                        <input value="<?= isset($trellis['coming_playstore']) ? $trellis['coming_playstore'] : '' ?>"
                            type="tel" class="form-control" name="coming_playstore" 
                            placeholder="Coming Playstore">
                    </div>
                    <div class="form-group">
                        <label for="new_updates">Incoming Updates</label>
                            <textarea name="new_updates" class="form-control" rows="10" placeholder="Incoming Playstore">
                            <?= isset($trellis['new_updates']) ? $trellis['new_updates'] : '' ?>
                            </textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Save</button>
                </form>
            </div>
        </div>
    </div>
 </div>
</div>
