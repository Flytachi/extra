<div class="warframe_header">
    <span class="warframe_header-title">Настройки</span><br>
    <span id="message"></span>
</div>

<div class="warframe_card">
    <div class="warframe_card-body">

        <div class="warframe_form-group">
        
            <?php if( is_writable(CFG_PATH_CLOSE) ): ?>
                <a class="warframe_btn" href="/firewall/license">License</a>
                <?php foreach($confList as $confItem): ?>
                    <a class="warframe_btn" href="/firewall/<?= mb_strtolower($confItem) ?>"><?= str_replace('_', ' ', $confItem) ?></a>
                <?php endforeach; ?>
            <?php else: ?>
                <span style="color:red">
                    Нет доступа на запись файла конфигураций!
                </span>
            <?php endif; ?>

        </div>

    </div>
</div>