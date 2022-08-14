<form action="/firmwareWebhook/hook/<?= $webHook->id ?? null ?>" method="post" onsubmit="submitForm()">
    <h3>Api ключ</h3>
    <div class="warframe_form-group">

        <?php $model->csrfToken() ?>

        <input type="hidden" name="enterprise_id" value="<?= $model->getData('id') ?>">

        <label for="inp-unique_key">Api ключ</label>
        <input type="text" id="inp-unique_key" name="unique_key" value="<?= $webHook->unique_key ?? uniqid(time()) ?>" required>

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