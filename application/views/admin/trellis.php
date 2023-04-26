
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Trellis</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><?= $page_title ?></h6>
                    <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Purpose</th>
                                <th>Mentor</th>
                                <th>Mentor Description</th>
                                <th>peer</th>
                                <th>peer Description</th>
                                <th>mentee</th>
                                <th>mentee Description</th>
                                <th>needs</th>
                                <th>identity</th>
                                <th>principles</th>
                                <th>rhythms</th>
                                <th>goal</th>
                                <th>ACHIEVEMENTS </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($trellis as $trel) { ?>
                                <tr>
                                    <td><?=$trel['name']?></td>
                                    <td>
                                        <?php $nameDescWords = explode(' ', $trel['name_desc']); ?>
                                        <span id="name_desc_<?=$trel['id']?>"><?=implode(' ', array_slice($nameDescWords, 0, 2))?>...</span>
                                        <?php if(count($nameDescWords) > 2) { ?>
                                            <a href="javascript:void(0);" onclick="toggleText('name_desc_<?=$trel['id']?>', '<?=implode(' ', $nameDescWords)?>')">Read More</a>
                                            <a href="javascript:void(0);" onclick="toggleText('name_desc_<?=$trel['id']?>', '<?=implode(' ', $nameDescWords)?>')" style="display:none;">Hide</a>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php $purposeWords = explode(' ', $trel['purpose']); ?>
                                        <span id="purpose_<?=$trel['id']?>"><?=implode(' ', array_slice($purposeWords, 0, 2))?>...</span>
                                        <?php if(count($purposeWords) > 2) { ?>
                                            <a href="javascript:void(0);" onclick="toggleText('purpose_<?=$trel['id']?>', '<?=implode(' ', $purposeWords)?>')">Read More</a>
                                            <a href="javascript:void(0);" onclick="toggleText('purpose_<?=$trel['id']?>', '<?=implode(' ', $purposeWords)?>')" style="display:none;">Hide</a>
                                        <?php } ?>
                                    </td>
                                    <td><?=$trel['mentor']?></td>
                                    <td><?=$trel['mentor_desc']?></td>
                                    <td><?=$trel['peer']?></td>
                                    <td><?=$trel['peer_desc']?></td>
                                    <td><?=$trel['mentee']?></td>
                                    <td><?=$trel['mentee_desc']?></td>
                                    <td><a href="<?= base_url('admin/user_needs/').$trel['user_id'] ?>">Needs</a></td>
                                    <td><a href="<?= base_url('admin/user_identity/').$trel['user_id'] ?>">Identity</a></td>
                                    <td><a href="<?= base_url('admin/user_principle/').$trel['user_id'] ?>">Principle</a></td>
                                    <td><a href="<?= base_url('admin/user_rhythms/').$trel['user_id'] ?>">Rhythms</a></td>
                                    <td><a href="<?= base_url('admin/user_goal/').$trel['user_id'] ?>">Goal</a></td>
                                    <td><a href="<?= base_url('admin/user_achievements/').$trel['user_id'] ?>">Achievements</a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function toggleText(id, text) {
        var elem = document.getElementById(id);
        var readMoreLink = elem.nextElementSibling;
        if (elem.innerText == text) {
            elem.innerText = text.substr(0, text.lastIndexOf(' ')) + '...';
            readMoreLink.style.display = 'inline';
        } else {
            elem.innerText = text;
            readMoreLink.style.display = 'none';
        }
    }
</script>