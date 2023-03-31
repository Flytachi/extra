<form action="/firmwareLicense/hook/<?= $model->id ?>" method="post" onsubmit="submitForm()">
    <h3><?= ($model->id) ? 'Edit' : 'Create' ?> License</h3>
        
    <div class="warframe_form-group">

        <?= $inputCsrf ?>

        <label for="inp-enterprise_id">Enterprise</label>
        <select id="inp-enterprise_id" name="enterprise_id" style="width: 100%;" data-placeholder="Select enterprise..." required>
            <option></option>
            <?php foreach($enterpriseList as $row): ?>
                <option value="<?= $row->id ?>" <?php if($row->id == $model->enterprise_id) echo 'selected' ?>><?= $row->name ?></option>
            <?php endforeach; ?>
        </select>

        <label for="inp-series">Serial</label>
        <input type="text" id="inp-series" name="series" value="<?= $model->series ?>" placeholder="Enter serial..." required>

        <label for="example-date_from">Service Life (from)</label>
        <input type="date" name="date_from" id="example-date_from" value="<?= $model->date_from ?>" required>

        <label for="example-date_to">Service Life (to)</label>
        <input type="date" name="date_to" id="example-date_to" value="<?= $model->date_to ?>" required>

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