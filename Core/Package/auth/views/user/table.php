<table class="table table-vcenter table-sm">
    <thead>
        <tr>
            <th class="text-center" style="width: 50px;">№</th>
            <th>Логин</th>
            <th>Имя</th>
            <th>Группа</th>
            <th class="text-center" style="width: 100px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data->list(1) as $user): ?>
            <tr>
                <th class="text-center" scope="row"><?= $user->count ?></th>
                <td><?= $user->username ?></td>
                <td><?= $user->name ?></td>
                <td><?= $user->group ?></td>
                <td class="text-center">
                    <div class="btn-group">
                        <?php if(isPermission('user_update') or isAdmin()): ?>
                            <?php if(!$user->is_admin or ($user->is_admin and isAdmin())): ?>
                                <button onclick="checkModal('/user/get/<?= $user->id ?>')" type="button" class="btn btn-sm btn-secondary js-tooltip-enabled" data-toggle="tooltip" title="" data-original-title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if(!$user->is_admin): ?>
                            <button onclick="AjaxQuery('/user/delete/<?= $user->id ?>')" type="button" class="btn btn-sm btn-secondary js-tooltip-enabled" data-toggle="tooltip" title="" data-original-title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $data->panel() ?>