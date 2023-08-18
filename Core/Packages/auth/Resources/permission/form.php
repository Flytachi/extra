<form action="/permission/hook/<?= $model->name ?>" method="post" onsubmit="submitForm()">
    <h3><?= ($model->name) ? 'Изменить' : 'Создать' ?> Привелегию</h3>
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <label for="inp-name">Код</label>
        <input type="text" id="inp-name" name="name" value="<?= $model->name ?>" placeholder="Введите код" required>
        
        <label for="inp-description">Описание</label>
        <input type="text" id="inp-description" name="description" value="<?= $model->description ?>" placeholder="Введите описание" required>

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