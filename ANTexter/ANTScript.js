var message = document.getElementById("message").value;
var tag = document.getElementById("tags").value;
var phone = document.getElementById("phone").value;
var count = 0;
var total = 0;
var ANAdress = "https://actionnetwork.org/api/v2/";
var sendServer = "https://";
//var ANapiKey = "095b4e51dccf9c92c464c0e564dd6f32";

//////////////////////////////////////////////////////////////////////////////////////////////
// Get tag categories from Action Network
function fetchTags() {
    var xhttp = new XMLHttpRequest();
    xhttp.addEventListener("load", getHrefs);
    xhttp.open("GET", ANAdress + "tags/", true);
    xhttp.setRequestHeader("OSDI-API-Token", ANapiKey);
    xhttp.send();
}

function getHrefs() {            
    var resp = JSON.parse(this.responseText);
    var tags = resp._links["osdi:tags"];

    count = tags.length;
    for (var x = 0; x < tags.length; x++) {
        addToSelect(fetchName(tags[x].href));
    }
}
    
function fetchName (element) {
    var xhr = new XMLHttpRequest();
    xhr.addEventListener("load", getName);
    xhr.open("GET", element, true);
    xhr.setRequestHeader("OSDI-API-Token", ANapiKey);
    xhr.send();
}

function getName() {
    var respon = JSON.parse(this.responseText);
    addToSelect(respon.name); 
}

function addToSelect(content) {
    var newDiv = document.createElement("option");
    var newOption = newDiv.appendChild(document.createTextNode(content));
    document.querySelector("select").appendChild(newOption);   
}

//////////////////////////////////////////////////////////////////////////////////////////////
// Send text message
function tester() {
    var xhttp = new XMLHttpRequest();
    xhttp.addEventListener("load", serverResponse);
    xhttp.open("GET", sendServer, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("message="+message+"&TWapiKey="+TWapiKey+"&testphone="+phone);
}

// Send real messages
function sender() {
    var xhttp = new XMLHttpRequest();
    xhttp.addEventListener("load", serverResponse);
    xhttp.open("GET", sendServer, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("message="+message+"&ANTag="+tag+"&ANapiKey="+ANapiKey+"&TWapiKey="+TWapiKey);
}

// Post response from server
function serverResponse() {
    document.getElementById("response").innerHTML = this.responseText;
}
