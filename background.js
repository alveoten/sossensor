setInterval(function() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "http://iot.tabucchi.it/sos_wc_status.php", true);
    xhr.onreadystatechange = function(){
        if( xhr.readyState == 4){

            if(xhr.status !== 200) {
                chrome.browserAction.setIcon({path: "status_red.png"});
                console.log("not valid response");
                return;
            }

            result = JSON.parse(xhr.responseText);

            switch(result.status){
                case "free":
                    icon = 'status_green.png';
                    break;
                case "warning":
                    icon = 'status_yellow.png';
                    break;
                case "occupied":
                    icon = 'status_red.png'
                    break;
                default:
                    console.log("assert!");
            }

            chrome.browserAction.setIcon({path: icon});
        }
    };
    xhr.send();
}, 10000);
