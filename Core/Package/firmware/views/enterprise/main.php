<div class="warframe_header">
    <span class="warframe_header-title">Enterprise</span><br>
    <span id="message"></span>
</div>

<div class="warframe_card">
    <div class="warframe_card-body">

        <button onclick="checkModal('/firmwareEnterprise/get')" class="warframe_btn">Add</button>
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
                url: "/firmwareEnterprise/list"+params,
                success: function (result) {
                    isLoaded(display);
                    display.innerHTML = result;
                },
            });

        }
    }

    $(document).ready(() => credoSearch());

</script>