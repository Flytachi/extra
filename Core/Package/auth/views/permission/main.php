<div class="warframe_header">
    <span class="warframe_header-title">Привелегии</span><br>
    <button onclick="checkModal('/user/changePassword/<?= $_SESSION['id'] ?>')" class="warframe_btn">Изменить мой пароль</button>
    <a href="/auth/logout" class="warframe_btn">Выйти</a>
</div>

<div class="warframe_card">
    <div class="warframe_card-body">

        <button onclick="checkModal('/permission/get')" class="warframe_btn">Добавить</button>
        <span id="message"></span>
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
                url: "permission/list"+params,
                success: function (result) {
                    isLoaded(display);
                    display.innerHTML = result;
                },
            });

        }
    }

    $(document).ready(() => credoSearch());

</script>