<form action="/user/hook/<?= $model->getData('id') ?>" method="post" onsubmit="submitForm()">
    <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
            <h3 class="block-title">Сменить Пароль Пользователя "<?= $model->getData('username') ?>"</h3>
            <div class="block-options">
                <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                    <i class="si si-close"></i>
                </button>
            </div>
        </div>
        <div class="block-content">

            <?php $model->csrfToken() ?>

            <div class="form-group">
                <label for="example-password">Пароль</label>
                <input type="password" class="form-control" id="example-password" name="password" placeholder="Введите пароль">
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