<table class="warframe_table">
    <thead>
        <tr>
            <th>Код</th>
            <th>Описание</th>
            <th style="width: 200px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($table as $row): ?>
            <tr>
                <td><?= $row->name ?></td>
                <td><?= $row->description ?></td>
                <td>
                    <button onclick="checkModal('/permission/get/<?= $row->name ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <button onclick="AjaxQuery('/permission/delete/<?= $row->name ?>')" type="button" class="warframe_btn" title="Удалить">
                        Remove
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $panel ?>