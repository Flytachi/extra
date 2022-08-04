<table class="table table-vcenter table-sm">
    <thead>
        <tr>
            <th>Наименование</th>
            <th class="text-center" style="width: 100px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data->list() as $group): ?>
            <tr>
                <td><?= $group->name ?></td>
                <td class="text-center">
                    <div class="btn-group">
                        <button onclick="checkModal('/group/get/<?= $group->id ?>')" type="button" class="btn btn-sm btn-secondary js-tooltip-enabled" data-toggle="tooltip" title="" data-original-title="Изменить">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button onclick="AjaxQuery('/group/remove/<?= $group->id ?>')" type="button" class="btn btn-sm btn-secondary js-tooltip-enabled" data-toggle="tooltip" title="" data-original-title="Удалить">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $data->panel() ?>