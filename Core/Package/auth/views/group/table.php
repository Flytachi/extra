<table class="warframe_table">
    <thead>
        <tr>
            <th>Наименование</th>
            <th style="width: 200px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($table as $row): ?>
            <tr>
                <td><?= $row->name ?></td>
                <td>
                    <button onclick="checkModal('/group/get/<?= $row->id ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <button onclick="AjaxQuery('/group/remove/<?= $row->id ?>')" type="button" class="warframe_btn" title="Удалить">
                        Remove
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $panel ?>