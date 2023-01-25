<table class="warframe_table">
    <thead>
        <tr>
            <th>Логин</th>
            <th>Имя</th>
            <th>Группа</th>
            <th style="width: 285px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($table as $row): ?>
            <tr <?php if($row->is_delete) echo 'style="background-color:red; color:white;"' ?>>
                <td><?= $row->username ?></td>
                <td><?= $row->name ?></td>
                <td><?= $row->group ?></td>
                <td>
                    <?php if(isPermission('user_update')): ?>
                        <?php if(!$row->is_admin or isAdmin()): ?>
                            <button onclick="checkModal('/user/get/<?= $row->id ?>')" type="button" class="warframe_btn" title="Редактировать">
                                Edit
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if(!$row->is_admin): ?>
                        
                        <?php if($row->is_delete): ?>
                            <?php if(isPermission('user_restore') or isAdmin()): ?>
                                <button onclick="AjaxQuery('/user/restore/<?= $row->id ?>')" type="button" class="warframe_btn" title="Восстановить">
                                    Restore
                                </button>
                            <?php endif; ?>
                            <?php if(isPermission('user_remove') or isAdmin()): ?>
                                <button onclick="AjaxQuery('/user/remove/<?= $row->id ?>')" type="button" class="warframe_btn" title="Удалить">
                                    Remove
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if(isPermission('user_delete') or isAdmin()): ?>
                                <button onclick="AjaxQuery('/user/delete/<?= $row->id ?>')" type="button" class="warframe_btn" title="Удалить">
                                    Delete
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $panel ?>