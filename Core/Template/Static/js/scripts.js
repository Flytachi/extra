function checkModal(url) {
    var modal = document.querySelector("#modalDefault");
    var content = document.querySelector("#modalDefault-content");
    isModalLoading(modal, content);

    $.ajax({
        type: "GET",
        url: url,
        success: function (result) {
            $(content).html(result);
        },
    });
}

function AjaxQuery(url, func = "credoSearch") {
    var button = event.target;
    var action = "!";
    var mess = button.title;
    if (mess != null) action = ' "' + mess + '"!';

    if (confirm(`Подтвердите действие${action}`)) {
        $(button).prop("disabled", true);

        $.ajax({
            type: "GET",
            url: url,
            success: function (response) {
                $(button).prop("disabled", false);

                if (response.status === "success") {
                    $("#message").css("color", "green");
                    $("#message").html("Success!");
                    window[func]();
                } else {
                    $("#message").css("color", "red");
                    $("#message").html(response.message);
                }
            },
        });
    }
}
