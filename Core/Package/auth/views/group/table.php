<table class="warframe_table">
    <thead>
        <tr>
            <th>Наименование</th>
            <th style="width: 200px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data->list() as $group): ?>
            <tr>
                <td><?= $group->name ?></td>
                <td>
                    <button onclick="checkModal('/group/get/<?= $group->id ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <button onclick="AjaxQuery('/group/remove/<?= $group->id ?>')" type="button" class="warframe_btn" title="Удалить">
                        Remove
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $data->panel() ?>