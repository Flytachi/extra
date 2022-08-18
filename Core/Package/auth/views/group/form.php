<form action="/group/hook/<?= $model->id ?>" method="post" onsubmit="submitForm()">
    <h3><?= ($model->id) ? 'Изменить' : 'Создать' ?> Группу</h3>
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <label for="inp-name">Название</label>
        <input type="text" id="inp-name" name="name" value="<?= $model->name ?>" placeholder="Введите название" required>

        <label>Привелегии</label>
        <?php foreach ($permissionList as $item): ?>
            <div>
                <input type="checkbox" name="permission[]" id="perm-<?= $item->name ?>" value="<?= $item->name ?>" <?php if(in_array($item->name, $permission)) echo 'checked' ?>>
                <label for="perm-<?= $item->name ?>"><?= $item->description ?></label>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="warframe_btn">Сохранить</button>

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
                modal.style.display = "none";
                if (response.status == "success") {
                    $("#message").css("color", "green");
                    $("#message").html("Успешно!");
                    credoSearch();
                } else {
                    $("#message").css("color", "red");
                    $("#message").html(response.message);
                }
            },
        });
    }
</script>