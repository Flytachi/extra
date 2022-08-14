<table class="warframe_table">
    <thead>
        <tr>
            <th style="width: 50px;">№</th>
            <th>Логин</th>
            <th>Имя</th>
            <th>Группа</th>
            <th style="width: 285px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data->list(1) as $user): ?>
            <tr>
                <th><?= $user->count ?></th>
                <td><?= $user->username ?></td>
                <td><?= $user->name ?></td>
                <td><?= $user->group ?></td>
                <td>
                    <?php if(isPermission('user_update')): ?>
                        <?php if(!$user->is_admin or ($user->is_admin and isAdmin())): ?>
                            <button onclick="checkModal('/user/get/<?= $user->id ?>')" type="button" class="warframe_btn" title="Редактировать">
                                Edit
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if(!$user->is_admin): ?>
                        
                        <?php if($user->is_delete): ?>
                            <?php if(isPermission('user_restore') or isAdmin()): ?>
                                <button onclick="AjaxQuery('/user/restore/<?= $user->id ?>')" type="button" class="warframe_btn" title="Восстановить">
                                    Restore
                                </button>
                            <?php endif; ?>
                            <?php if(isPermission('user_remove') or isAdmin()): ?>
                                <button onclick="AjaxQuery('/user/remove/<?= $user->id ?>')" type="button" class="warframe_btn" title="Удалить">
                                    Remove
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if(isPermission('user_delete') or isAdmin()): ?>
                                <button onclick="AjaxQuery('/user/delete/<?= $user->id ?>')" type="button" class="warframe_btn" title="Удалить">
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

<?php $data->panel() ?>