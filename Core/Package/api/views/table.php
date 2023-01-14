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
            <tr <?php if($row->getIsDelete()) echo 'style="background-color:red; color:white;"' ?>>
                <td><?= $row->getType() ?></td>
                <td>
                    <?php if($row->getType() == 'Bearer'): ?>
                        <b>Token:</b> <?= $row->getToken() ?>
                    <?php elseif($row->getType() == 'Basic'): ?>
                        <b>Username:</b> <?= $row->getUsername() ?>
                        <b>Password:</b> <?= $row->getPassword() ?>
                    <?php endif; ?>
                </td>
                <td>
                    <button onclick="checkModal('/cPanelApi/get/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <?php if($row->getIsDelete()): ?>
                        <button onclick="AjaxQuery('/cPanelApi/restore/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Восстановить">
                            Restore
                        </button>
                        <button onclick="AjaxQuery('/cPanelApi/remove/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Удалить">
                            Remove
                        </button>
                    <?php else: ?>
                        <button onclick="AjaxQuery('/cPanelApi/delete/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Удалить">
                            Delete
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $panel ?>