<form action="/user/hookPassword/<?= $model->id ?>" method="post" onsubmit="submitForm()">
    <h3>Сменить Пароль Пользователя "<?= $model->username ?>"</h3>
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <label for="inp-password">Пароль</label>
        <input type="password" id="inp-password" name="password" placeholder="Введите пароль" required>

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