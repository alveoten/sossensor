chrome.runtime.sendMessage({data:"Handshake"},function(response){

});

chrome.runtime.onMessage.addListener(
    function(request, sender, sendResponse) {
        document.getElementById("status").innerHTML = request.msg;
    }
);