function ExtraDebugBar()
{
    let bar = document.getElementById("extra_debug-bar");
    let btn = document.getElementById("extra_debug-btn");
    if (bar) {
        if (bar.style.display !== "block") {
            console.log('open debug panel');
            bar.style.display = "block";
            btn.style.bottom = "49%";
            btn.style.color = "white";
            btn.style.backgroundColor = "red";
        } else {
            console.log('close debug panel');
            bar.style.display = "none";
            btn.style.bottom = "5px";
            btn.style.color = "red";
            btn.style.backgroundColor = "#111";
        }
    } else {
        alert("Debug блок не найден");
    }
}