function retrieveURL(url, elementId) {
	var xmlHttp = GetXmlHttpObject(); 
	if (xmlHttp==null) {
		alert ("Your browser does not support AJAX!");
		return;
	}
	
	xmlHttp.onreadystatechange=function() {
		if (xmlHttp.readyState == 4) { // Complete
			if (xmlHttp.status == 200) { // OK response
				if(elementId != '')
					document.getElementById(elementId).innerHTML = xmlHttp.responseText;
			} else {
				alert("Status: "+xmlHttp.statusText);
			}
		}
	}
	if(url.lastIndexOf('?') > -1)
		url = url + "&dummy=" + new Date().getTime();
	else
		url = url + "?dummy=" + new Date().getTime();
	
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function GetXmlHttpObject() {
	var xmlHttp=null;
	try {
		// If IE7, Mozilla, Safari, etc: Use native object
		xmlHttp=new XMLHttpRequest();
	} catch (e) {
		// Internet Explorer < 7
		try {
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

function urlencode(str) {
	str = escape(str);
	str = str.replace('+', '%2B');
	str = str.replace('%20', '+');
	str = str.replace('*', '%2A');
	str = str.replace('/', '%2F');
	str = str.replace('@', '%40');
	return str;
}

function urldecode(str) {
	str = str.replace('+', ' ');
	str = unescape(str);
	return str;
}