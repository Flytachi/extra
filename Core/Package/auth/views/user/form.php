
<form action="/user/hook/<?= $model->getData('id') ?>" method="post" onsubmit="submitForm()">
    <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
            <h3 class="block-title"><?= ($model->getData('id')) ? 'Изменить' : 'Создать' ?> Пользователя</h3>
            <div class="block-options">
                <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                    <i class="si si-close"></i>
                </button>
            </div>
        </div>
        <div class="block-content">

            <?php $model->csrfToken() ?>

            <div class="form-group">
                <label for="example-name">Имя пользователя</label>
                <input type="text" class="form-control" id="example-name" name="info[name]" value="<?= $userInfo->name ?? null ?>" placeholder="Введите имя пользователя" required>
            </div>

            <div class="form-group">
                <label for="example-username">Логин</label>
                <input type="text" class="form-control" id="example-username" name="username" value="<?= $model->getData('username') ?>" placeholder="Введите логин" required>
            </div>

            <?php if(!$model->getData('id')): ?>
                <div class="form-group">
                    <label for="example-password">Пароль</label>
                    <input type="password" class="form-control" id="example-password" name="password" placeholder="Введите пароль" required>
                </div>
            <?php endif; ?>

            <?php if(isAdmin()): ?>

                <div class="form-group row">
                    <label class="col-12" for="example-group_id">Группа</label>
                    <div class="col-md-12">
                        <select class="js-select2 form-control" id="example-group_id" name="info[group_id]" style="width: 100%;" data-placeholder="Выберите группу" required>
                            <option></option>
                            <?php foreach($groupList as $row): ?>
                                <option value="<?= $row->id ?>" <?php if($row->id == ($userInfo->group_id ?? 0)) echo 'selected' ?>><?= $row->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-alt-secondary" data-dismiss="modal">Закрыть</button>
        <button type="submit" class="btn btn-alt-success">
            <i class="fa fa-check"></i> Сохранить
        </button>
    </div>
</form>

<script>
    jQuery(function(){ Codebase.helpers(['select2']); });
    
    function submitForm() {
        event.preventDefault();
        $.ajax({
            type: $(event.target).attr("method"),
            url: $(event.target).attr("action"),
            data: $(event.target).serializeArray(),
            success: function (response) {
                $('#modalDefault').modal('hide');
                if (response.status == "success") {
                    Codebase.helpers('notify', {
                        align: 'right',
                        from: 'top',
                        type: response.status,
                        icon: 'fa fa-check mr-5',
                        message: "Успешно!"
                    });
                    credoSearch();
                } else {
                    Codebase.helpers('notify', {
                        align: 'right',
                        from: 'top',
                        type: 'danger',
                        icon: 'fa fa-times mr-5',
                        message: response.message
                    });
                }
            },
        });
    }
    
</script>