<div class="warframe_header">
    <span class="warframe_header-title">Пользователи</span><br>
    <span id="message"></span>
</div>

<div class="warframe_card">
    <div class="warframe_card-body">
        
        <?php if(isPermission('user_create')): ?>
            <button onclick="checkModal('/user/get')" class="warframe_btn">Добавить</button>
        <?php endif; ?>
        <div id="search_display"></div>

    </div>
</div>

<script type="text/javascript">

    var cXhr = null;
    function credoSearch(params = '') {
        if (document.querySelector('#search_display')) {
            if(cXhr && cXhr.readyState != 4) cXhr.abort();
            var display = document.querySelector('#search_display');
            isLoading(display);

            cXhr = $.ajax({
                type: "GET",
                url: "/user/list"+params,
                success: function (result) {
                    isLoaded(display);
                    display.innerHTML = result;
                },
            });

        }
    }

    $(document).ready(() => credoSearch());

</script>