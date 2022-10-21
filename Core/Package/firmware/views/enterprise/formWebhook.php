<form action="/firmwareWebhook/hook/<?= $webHook->id ?? null ?>" method="post" onsubmit="submitForm()">
    <h3>Api ключ</h3>
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <input type="hidden" name="enterprise_id" value="<?= $model->id ?>">

        <label for="inp-unique_key">Api ключ: 
            <a onclick="genUid()" style="color:red" href="#">сгенерировать</a>
        </label>
        <input type="text" id="inp-unique_key" name="unique_key" value="<?= $webHook->unique_key ?? '' ?>" placeholder="Введите уникальный ключ" required>

        <button type="submit" class="warframe_btn">Сохранить</button>

    </div>
</form>

<script>
    function genUid() {
        event.preventDefault();
        let a = new Uint32Array(3);
        window.crypto.getRandomValues(a);
        var uniq = (performance.now().toString(36)+Array.from(a).map(A => A.toString(36)).join("")).replace(/\./g,"");
        $("#inp-unique_key").val(uniq);
    }

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