<table class="warframe_table">
    <thead>
        <tr>
            <th>Наименование</th>
            <th>Контакты</th>
            <th style="width: 357px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($table as $row): ?>
            <tr <?php if($row->getIsDelete()) echo 'style="background-color:red; color:white;"' ?>>
                <td><?= $row->getName() ?></td>
                <td><?= $row->getContact() ?></td>
                <td>
                    <button onclick="checkModal('/firmwareEnterprise/getWebhook/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Api Ключ">
                        Api
                    </button>
                    <button onclick="checkModal('/firmwareEnterprise/get/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <?php if($row->getIsDelete()): ?>
                        <button onclick="AjaxQuery('/firmwareEnterprise/restore/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Восстановить">
                            Restore
                        </button>
                        <button onclick="AjaxQuery('/firmwareEnterprise/remove/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Удалить">
                            Remove
                        </button>
                    <?php else: ?>
                        <button onclick="AjaxQuery('/firmwareEnterprise/delete/<?= $row->getId() ?>')" type="button" class="warframe_btn" title="Удалить">
                            Delete
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $panel ?>