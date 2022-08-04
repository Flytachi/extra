<form action="/group/hook/<?= $model->getData('id') ?>" method="post" onsubmit="submitForm()">
    <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
            <h3 class="block-title"><?= ($model->getData('id')) ? 'Изменить' : 'Создать' ?> Группу</h3>
            <div class="block-options">
                <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                    <i class="si si-close"></i>
                </button>
            </div>
        </div>
        <div class="block-content">

            <?php $model->csrfToken() ?>

            <div class="form-group">
                <label for="example-name">Название</label>
                <input type="text" class="form-control" id="example-name" name="name" value="<?= $model->getData('name') ?>" placeholder="Введите название" required>
            </div>

            <div class="form-group row">
                <label class="col-12">Привелегии</label>

                <?php foreach ($permissionList as $item): ?>
                    <div class="col-12">
                        <div class="custom-control custom-checkbox mb-5">
                            <input class="custom-control-input" type="checkbox" name="permission[]" id="perm-<?= $item->name ?>" value="<?= $item->name ?>" <?php if(in_array($item->name, $permission)) echo 'checked' ?>>
                            <label class="custom-control-label" for="perm-<?= $item->name ?>"><?= $item->description ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            </div>

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