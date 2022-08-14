<table class="warframe_table">
    <thead>
        <tr>
            <th style="width: 50px;">№</th>
            <th>Наименование</th>
            <th>Контакты</th>
            <th style="width: 255px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data->list(1) as $enterprice): ?>
            <tr>
                <th><?= $enterprice->count ?></th>
                <td><?= $enterprice->name ?></td>
                <td><?= $enterprice->contact ?></td>
                <td>
                    <button onclick="checkModal('/firmwareEnterprise/getWebhook/<?= $enterprice->id ?>')" type="button" class="warframe_btn" title="Api Ключ">
                        Api
                    </button>
                    <button onclick="checkModal('/firmwareEnterprise/get/<?= $enterprice->id ?>')" type="button" class="warframe_btn" title="Редактировать">
                        Edit
                    </button>
                    <button onclick="AjaxQuery('/firmwareEnterprise/delete/<?= $enterprice->id ?>')" type="button" class="warframe_btn" title="Удалить">
                        Remove
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $data->panel() ?>