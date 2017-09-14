setCall();

chrome.alarms.create("1min", {
    periodInMinutes: 1
});

chrome.alarms.onAlarm.addListener(function(alarm){
    setCall();
});

function setCall(){
    for(i=0; i<6; i++){
        setTimeout(doRequest,10000*i);
    }
}

var status_text = "libero";
function doRequest() {
    var xhr = new XMLHttpRequest();

    //xhr.open("GET", "http://iot.tabucchi.it/sos_wc_status.php?debug=1", true);
    xhr.open("GET", "http://iot.tabucchi.it/sos_wc_status.php", true);
    xhr.onreadystatechange = function(){
        if( xhr.readyState == 4){


            if(xhr.status !== 200) {
                chrome.browserAction.setIcon({path: "images/status_red.png"});
                console.log("not valid response");
                chrome.runtime.sendMessage({msg: "occupato", data: 'error'});
                return;
            }

            result = JSON.parse(xhr.responseText);



            switch(result.status){
                case "free":
                    icon = 'images/status_green.png';
                    status_text = "libero";
                    break;
                case "warning":
                    icon = 'images/status_yellow.png';
                    status_text = "libero - sciaquone vuoto";
                    break;
                case "occupied":
                    icon = 'images/status_red.png'
                    status_text = "occupato";
                    break;
                default:
                    console.log("assert!");
            }

            chrome.runtime.sendMessage({msg: status_text, data: result.status});
            chrome.browserAction.setIcon({path: icon});

        }
    };
    xhr.send();
}


chrome.runtime.onMessage.addListener(function(message,sender,sendResponse){
    chrome.runtime.sendMessage({msg: status_text},function(response){});
});