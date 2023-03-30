<table class="warframe_table">
    <thead>
        <tr>
            <th>Предприятие</th>
            <th>Устройство</th>
            <th>Срок</th>
            <th style="width: 285px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($table as $row): ?>
            <tr <?php if($row->is_delete) echo 'style="background-color:red; color:white;"' ?>>
                <td><?= $row->enterprise ?></td>
                <td><?= $row->series ?></td>
                <td><?= $row->date_from ?> => <?= $row->date_to ?></td>
                <td>
                    <?php if(!$row->is_delete): ?>
                        <a href="/firmwareLicense/getFile/<?= $row->id ?>" type="button" class="warframe_btn" title="Скачать файл">
                            Load
                        </a>
                    <?php endif; ?>
                    <button onclick="checkModal('/firmwareLicense/get/<?= $row->id ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <?php if($row->is_delete): ?>
                        <button onclick="AjaxQuery('/firmwareLicense/restore/<?= $row->id ?>')" type="button" class="warframe_btn" title="Восстановить">
                            Restore
                        </button>
                        <button onclick="AjaxQuery('/firmwareLicense/remove/<?= $row->id ?>')" type="button" class="warframe_btn" title="Удалить">
                            Remove
                        </button>
                    <?php else: ?>
                        <button onclick="AjaxQuery('/firmwareLicense/delete/<?= $row->id ?>')" type="button" class="warframe_btn" title="Удалить">
                            Delete
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $panel ?>