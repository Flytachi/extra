<div class="content">
    
    <div class="row invisible" data-toggle="appear">

        <div class="col-md-12">
            <div class="block block-rounded block-themed block-bordered">

                <div class="block-header bg-flat-darker">
                    <h3 class="block-title">Список Привелегий</h3>
                    <div class="block-options">
                        <button onclick="checkModal('/permission/get')" class="btn btn-sm btn-secondary">
                            <i class="fa fa-plus"></i> Добавить
                        </button>
                    </div>
                </div>
                
                <div class="block-content" id="search_display"></div>

            </div>
        </div>

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