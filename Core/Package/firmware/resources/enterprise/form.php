<form action="/firmwareEnterprise/hook/<?= $model->id ?>" method="post" onsubmit="submitForm()">
    <h3><?= ($model->id) ? 'Edit' : 'Create' ?> Enterprise</h3>
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <label for="inp-name">Name</label>
        <input type="text" id="inp-name" name="name" value="<?= $model->name ?>" placeholder="Enter name..." required>

        <label for="inp-contact">Contact</label>
        <input type="text" id="inp-contact" name="contact" value="<?= $model->contact ?>" placeholder="Enter contact..." required>

        <label for="inp-contact">Url</label>
        <input type="text" id="inp-contact" name="url" value="<?= $model->url ?>" placeholder="Enter url...">

        <button type="submit" class="warframe_btn">Save</button>

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
                if (response.status === "success") {
                    $("#message").css("color", "green");
                    $("#message").html("Success!");
                    credoSearch();
                } else {
                    $("#message").css("color", "red");
                    $("#message").html(response.message);
                }
            },
        });
    }
</script>