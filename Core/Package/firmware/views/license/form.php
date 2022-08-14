<form action="/firmwareLicense/hook/<?= $model->getData('id') ?>" method="post" onsubmit="submitForm()">
    <h3><?= ($model->getData('id')) ? 'Изменить' : 'Создать' ?> Лицензию</h3>
        
    <div class="warframe_form-group">

        <?php $model->csrfToken() ?>

        <label for="inp-enterprise_id">Предприятие</label>
        <select id="inp-enterprise_id" name="enterprise_id" style="width: 100%;" data-placeholder="Выберите предприятие" required>
            <option></option>
            <?php foreach($enterpriseList as $row): ?>
                <option value="<?= $row->id ?>" <?php if($row->id == $model->getData('enterprise_id')) echo 'selected' ?>><?= $row->name ?></option>
            <?php endforeach; ?>
        </select>

        <label for="inp-series">Серия устройства</label>
        <input type="text" id="inp-series" name="series" value="<?= $model->getData('series') ?>" placeholder="Введите серию" required>


        <label for="example-date_from">Дата (от)</label>
        <input type="date" name="date_from" id="example-date_from" value="<?= $model->getData('date_from') ?>" required>

        <label for="example-date_to">Дата (до)</label>
        <input type="date" name="date_to" id="example-date_to" value="<?= $model->getData('date_to') ?>" required>

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