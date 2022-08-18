<form action="/user/hook/<?= $model->id ?>" method="post" onsubmit="submitForm()">
    <h3><?= ($model->id) ? 'Изменить' : 'Создать' ?> Пользователя</h3>
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <label for="inp-name">Имя пользователя</label>
        <input type="text" id="inp-name" name="info[name]" value="<?= $userInfo->name ?? null ?>" placeholder="Введите имя пользователя" required>

        <label for="inp-username">Логин</label>
        <input type="text" id="inp-username" name="username" value="<?= $model->username ?>" placeholder="Введите логин" required>

        <?php if(!$model->id): ?>
            <label for="inp-password">Пароль</label>
            <input type="password" id="inp-password" name="password" placeholder="Введите пароль" required>
        <?php endif; ?>

        <?php if(isAdmin() and !$model->is_admin): ?>

            <label for="inp-group_id">Группа</label>
            <select id="inp-group_id" name="info[group_id]" placeholder="Выберите группу" required>
                <option></option>
                <?php foreach($groupList as $row): ?>
                    <option value="<?= $row->id ?>" <?php if($row->id == ($userInfo->group_id ?? 0)) echo 'selected' ?>><?= $row->name ?></option>
                <?php endforeach; ?>
            </select>

        <?php endif; ?>

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