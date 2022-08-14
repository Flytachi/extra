<table class="warframe_table">
    <thead>
        <tr>
            <th style="width: 50px;">№</th>
            <th>Предприятие</th>
            <th>Устройство</th>
            <th>Срок</th>
            <th style="width: 300px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data->list(1) as $license): ?>
            <tr>
                <th><?= $license->count ?></th>
                <td><?= $license->enterprise ?></td>
                <td><?= $license->series ?></td>
                <td><?= $license->date_from ?> => <?= $license->date_to ?></td>
                <td>
                    <a href="/firmwareLicense/getFile/<?= $license->id ?>" type="button" class="warframe_btn" title="Скачать файл">
                        Load
                    </a>
                    <button onclick="checkModal('/firmwareLicense/get/<?= $license->id ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <button onclick="AjaxQuery('/firmwareLicense/delete/<?= $license->id ?>')" type="button" class="warframe_btn" title="Удалить">
                        Remove
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $data->panel() ?>