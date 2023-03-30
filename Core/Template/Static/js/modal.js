var modal = document.getElementById("modalDefault");

window.onclick = function (event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};

function isModalLoading(modal, content) {
    modal.style.display = "block";
    $(content).html(`<div class="text-center mt-2 mb-2">
        <div class="spinner-border text-dark" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>`);
}
