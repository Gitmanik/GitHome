function re() {
    location.reload();
}

function toggle(event) {
    $.post("/api/change.php", {
        id: $(event).attr('device_id')
    }, re);
}

// $( document ).ready(function() {
//     $.post("/api/log.php", {
//         id: "SCREEN01",
//         data: "Battery status: " + window.AppInventor.getWebViewString()
//     });
// });