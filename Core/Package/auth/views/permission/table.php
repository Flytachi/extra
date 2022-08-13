<table class="warframe_table">
    <thead>
        <tr>
            <th>Код</th>
            <th>Описание</th>
            <th style="width: 200px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data->list() as $perm): ?>
            <tr>
                <td><?= $perm->name ?></td>
                <td><?= $perm->description ?></td>
                <td>
                    <button onclick="checkModal('/permission/get/<?= $perm->name ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <button onclick="AjaxQuery('/permission/remove/<?= $perm->name ?>')" type="button" class="warframe_btn" title="Удалить">
                        Remove
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $data->panel() ?>