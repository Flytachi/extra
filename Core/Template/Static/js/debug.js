function WarframeDebugBar() {
    if (document.getElementById("warframe_debug-bar")) {
        if (document.getElementById("warframe_debug-bar").dataset.status == '1') {
            document.getElementById("warframe_debug-bar").style.width = "0";
            document.getElementById("warframe_debug-btn_div").style.marginLeft= "0";
            document.getElementById("warframe_debug-bar").dataset.status = '0';
            document.getElementById("warframe_debug-btn").style.color = 'white';
        } else {
            document.getElementById("warframe_debug-bar").style.width = "47%";
            document.getElementById("warframe_debug-btn_div").style.marginLeft = "47%";
            document.getElementById("warframe_debug-bar").dataset.status = '1';
            document.getElementById("warframe_debug-btn").style.color = 'red';
        }
    }else{
        alert("Создайте debug блок в удобном вам месте !\n<div id=\"warframe_debug-bar\">...</div>");
    }
    
}