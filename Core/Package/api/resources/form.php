<form action="/cPanelApi/hook/<?= $model->id ?>" method="post" onsubmit="submitForm()">
    <h3><?= ($model->id) ? 'Изменить' : 'Создать' ?> Группу</h3>
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <label for="inp-type">Тип авторизации</label>
        <select id="inp-type" name="type" placeholder="Выберите тип авторизации" onchange="changeType(this)" required>
            <option></option>
            <option value="Bearer" <?php if($model->type == 'Bearer') echo 'selected' ?>>Bearer Token</option>
            <option value="Basic" <?php if($model->type == 'Basic') echo 'selected' ?>>Basic Auth</option>
        </select>

        <div id="areaToken" style="display:none">
            <label for="inp-token">Token</label>
            <input type="text" id="inp-token" name="token" value="<?= $model->token ?>" placeholder="Введите токен">
        </div>

        <div id="areaUsername" style="display:none">
            <label for="inp-username">Username</label>
            <input type="text" id="inp-username" name="username" value="<?= $model->username ?>" placeholder="Введите username">
        </div>
    
        <div id="areaPassword" style="display:none">
            <label for="inp-password">Password</label>
            <input type="text" id="inp-password" name="password" value="<?= $model->password ?>" placeholder="Введите password">
        </div>

        <button type="submit" class="warframe_btn">Сохранить</button>

    </div>
</form>

<script>

    var select = document.querySelector("#inp-type");
    var areaToken = document.querySelector("#areaToken");
    var areaUsername = document.querySelector("#areaUsername");
    var areaPassword = document.querySelector("#areaPassword");

    function submitForm() {
        event.preventDefault();
        $.ajax({
            type: $(event.target).attr("method"),
            url: $(event.target).attr("action"),
            data: $(event.target).serializeArray(),
            success: function (response) {
                console.log(response);
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

    function changeType() {
        if (select.value == "Bearer") {
            areaToken.style.display = "block";
            areaToken.required = "true";
            areaUsername.style.display = "none";
            areaUsername.required = "false";
            areaPassword.style.display = "none";
            areaPassword.required = "false";
        } else if (select.value == "Basic") {
            areaToken.style.display = "none";
            areaToken.required = "false";
            areaUsername.style.display = "block";
            areaUsername.required = "true";
            areaPassword.style.display = "block";
            areaPassword.required = "true";
        } else {
            areaToken.style.display = "none";
            areaToken.required = "false";
            areaUsername.style.display = "none";
            areaUsername.required = "false";
            areaPassword.style.display = "none";
            areaPassword.required = "false";
        }
    }

    $(document).ready(() => changeType());

</script>