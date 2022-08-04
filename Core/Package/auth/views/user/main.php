<link rel="stylesheet" href="/static/assets/js/plugins/select2/css/select2.min.css">
<script src="/static/assets/js/plugins/select2/js/select2.full.min.js"></script>

<div class="content">
    
    <div class="row invisible" data-toggle="appear">

        <div class="col-md-12">
            <div class="block block-rounded block-bordered">

                <div class="block-header block-header-default">
                    <h3 class="block-title">Пользователи</h3>
                    <div class="block-options">
                        <?php if(isPermission('user_create')): ?>
                            <button onclick="checkModal('/user/get')" class="btn btn-sm btn-alt-success">
                                <i class="fa fa-plus"></i> Добавить
                            </button>
                        <?php endif; ?>
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