<div class="warframe_header">
    <span class="warframe_header-title">Настройки <?= $settName ?></span><br>
    <span id="message"></span>
</div>

<div class="warframe_card">
    <div class="warframe_card-body">

        <div class="warframe_form-group">
            <form action="/firewall/spell" method="post">
            
                <?php foreach ($confList as $key => $value): ?>
                    <label for="inp-<?= $key ?>" class="col-md-4"><b><?= $key ?>:</b></label>
                    <input type="text" id="inp-<?= $key ?>" name="<?= $settName ?>[<?= $key ?>]" value="<?= $value ?>" >
                <?php endforeach; ?>
    
                <button type="submit" class="warframe_btn">Сохранить</button>
            
            </form>
        </div>

    </div>
</div>