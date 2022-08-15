<div class="warframe_header">
    <span class="warframe_header-title">Настройка Прошивки</span><br>
    <span id="message"></span>
</div>


<div class="warframe_flex-row-multicolum">
    <div class="warframe_flex-col">
        <div class="warframe_card">

            <div class="warframe_card-body">
                <h3>Устройство</h3>
                <div class="warframe_form-group">
                    <p>
                        <strong>Защита 'Guard':</strong> <?= ($device['guard']) ? '<span style="color:green">Включена</span>' : '<span style="color:red">Выключена</span>' ?>
                    </p>
                    <p>
                        <strong>Серия:</strong> <?= $device['series'] ?>
                    </p>
                    <p>
                        <strong>Прошивка:</strong> <?= $device['firmware'] ?>
                    </p>
                </div>
            </div>

        </div>
    </div>
    <div class="warframe_flex-col">
        <div class="warframe_card">

            <div class="warframe_card-body">
                <h3>Лицензия</h3>
                <div class="warframe_form-group">
                    <p>
                        <strong>Хост:</strong> <?= ($device['host']) ? '<span style="color:blue">' .$device['host']. '</span>' : '<span style="color:grey">Нет данных</span>' ?>
                    </p>
                    <p>
                        <strong>Api ключ:</strong> <?= ($device['api']) ? '<span style="color:green">Есть</span>' : '<span style="color:red">Нет</span>' ?>
                    </p>
                    <?php if($license): ?>
                        <p>
                            <strong>Действует: </strong>
                            от <?= date('Y-m-d', $license['licenseDateFrom']) ?> до <?= date('Y-m-d', $license['licenseDateTo']) ?>
                        </p>
                        <p>
                            <strong>Серия Устройства:</strong> <?= $license['motherboardSeries'] ?>
                        </p>
                        <?php $toDay = strtotime(date('Y-m-d')); ?>
                        <?php if($license['licenseDateFrom'] <= $toDay and $toDay <= $license['licenseDateTo']): ?>
                            <p style="color:green">
                                Лицензия актуальна.
                            </p>
                        <?php else: ?>
                            <p style="color:red">
                                Лицензия просрочена.
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color:grey">
                            Нет данных.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
    <div class="warframe_flex-col">
        <div class="warframe_card">
            
            <div class="warframe_card-body">
                <h3>Установить лицензию</h3>
                <div class="warframe_form-group">
                    <form action="/firewall/liceseSpell" method="post" enctype="multipart/form-data">
                        
                        <input type="file" name="license" id="inp-license">
                        <button type="submit" class="warframe_btn">Прошить</button>
                    
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>