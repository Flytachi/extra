<form action="/firmwareEnterprise/hook/<?= $model->id ?>" method="post" onsubmit="submitForm()">
    <h3><?= ($model->id) ? 'Изменить' : 'Создать' ?> Предприятие</h3>
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <label for="inp-name">Название</label>
        <input type="text" id="inp-name" name="name" value="<?= $model->name ?>" placeholder="Введите название" required>

        <label for="inp-contact">Контакты</label>
        <input type="text" id="inp-contact" name="contact" value="<?= $model->contact ?>" placeholder="Введите контакты" required>

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