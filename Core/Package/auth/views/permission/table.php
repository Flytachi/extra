<table class="table table-vcenter table-sm">
    <thead>
        <tr>
            <th>Код</th>
            <th>Описание</th>
            <th class="text-center" style="width: 100px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data->list() as $perm): ?>
            <tr>
                <td><?= $perm->name ?></td>
                <td><?= $perm->description ?></td>
                <td class="text-center">
                    <div class="btn-group">
                        <button onclick="checkModal('/permission/get/<?= $perm->name ?>')" type="button" class="btn btn-sm btn-secondary js-tooltip-enabled" data-toggle="tooltip" title="" data-original-title="Edit">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button onclick="AjaxQuery('/permission/remove/<?= $perm->name ?>')" type="button" class="btn btn-sm btn-secondary js-tooltip-enabled" data-toggle="tooltip" title="" data-original-title="Delete">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $data->panel() ?>