chrome.runtime.sendMessage({data:"Handshake"},function(response){

});

chrome.runtime.onMessage.addListener(
    function(request, sender, sendResponse) {
        var status = $("#status");
        status.innerHTML = request.msg;
        status.removeClass(status.attr('class')).addClass("label " + request.className);

        $("#refresh").find("i").removeClass("faa-spin animated");
    }
);

var background = chrome.extension.getBackgroundPage();

$(document).ready(function() {
    $("#refresh").click(function() {
        background.doRequest();
        $(this).find("i").addClass("faa-spin animated");
    });
});