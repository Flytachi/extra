<table class="warframe_table">
    <thead>
        <tr>
            <th style="width: 200px;">Тип Авторизации</th>
            <th>Данные</th>
            <th style="width: 200px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($table as $row): ?>
            <tr <?php if($row->is_delete) echo 'style="background-color:red; color:white;"' ?>>
                <td><?= $row->type ?></td>
                <td>
                    <?php if($row->type == 'Bearer'): ?>
                        <b>Token:</b> <?= $row->token ?>
                    <?php elseif($row->type == 'Basic'): ?>
                        <b>Username:</b> <?= $row->username ?>
                        <b>Password:</b> <?= $row->password ?>
                    <?php endif; ?>
                </td>
                <td>
                    <button onclick="checkModal('/cPanelApi/get/<?= $row->id ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <?php if($row->is_delete): ?>
                        <button onclick="AjaxQuery('/cPanelApi/restore/<?= $row->id ?>')" type="button" class="warframe_btn" title="Восстановить">
                            Restore
                        </button>
                        <button onclick="AjaxQuery('/cPanelApi/remove/<?= $row->id ?>')" type="button" class="warframe_btn" title="Удалить">
                            Remove
                        </button>
                    <?php else: ?>
                        <button onclick="AjaxQuery('/cPanelApi/delete/<?= $row->id ?>')" type="button" class="warframe_btn" title="Удалить">
                            Delete
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $panel ?>