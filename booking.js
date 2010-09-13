function edit_booking(info_array,keys_array) {
	showElement('spec_field');
	for (var i=0;i<info_array.length;i++) {
		if(keys_array[i].indexOf('phone') != -1) 
			setEditShow(keys_array[i],info_array[i],'tel:'+info_array[i]);
		else if(keys_array[i].indexOf('epost') != -1) 
			setEditShow(keys_array[i],info_array[i],'mailto:'+info_array[i]);
		else if(keys_array[i].indexOf('spec') != -1 && info_array[i] == -1) {
			setEditShow(keys_array[i],'1','');
			hideElement('spec_field');
		} else
			setEditShow(keys_array[i],info_array[i],'');
		
		if(keys_array[i] == 'namn' && info_array[i] == '') showEdit();
	}
	
	document.getElementById('kalender').className='hide';
	document.getElementById('bokningsform').className='show';
}

function save_booking() {
	
	var bokningsform = document.getElementById('bokning');
	
	var url = wpath+'index.php?ajax=cal&action=save';
	
	var val = '';
	for (var i in columns) {
		url += '&data%5B'+columns[i]+'%5D='+urlencode(getFieldValue(columns[i]));
	}
	
	document.getElementById('kalender').innerHTML='<div class="overlay"> </div><div class="dialogue"><img src="'+wpath+'loading.gif" /> Sparar...</div>'+document.getElementById('kalender').innerHTML;
	retrieveURL(url, 'kalender');
	
	hideEdit();
	
	document.getElementById('kalender').className='show';
	document.getElementById('bokningsform').className='hide';
}

function cancelEdit() {

	if(document.getElementById('savebutton').className=='hide' || document.getElementById('bokning').bokningsid.value == '-1') {
		document.getElementById('kalender').className='show';
		document.getElementById('bokningsform').className='hide';
	}
	hideEdit();
}

function hideEdit() {
	for (var i in columns) {
		if(columns[i] != 'bokningsid') {
			hideElement(columns[i]+'_edit');
			showElement(columns[i]+'_show');
		}
	}
	hideElement('savebutton');
}

function getFieldValue(column) {
	eval("var formfield = document.getElementById('bokning')."+column+";");
	if(formfield.length != undefined) {
		for(var i = 0; i < formfield.length; i++) {
			if(formfield[i].checked) return formfield[i].value;
		}
	} else {
		return formfield.value;
	}
}

function setEditShow(column,value,href) {
	eval("var formfield = document.getElementById('bokning')."+column+";");
	if(formfield.length != undefined) {
		for(var i = 0; i < formfield.length; i++) {
			formfield[i].checked = false;
			if(formfield[i].value == value)
				formfield[i].checked = true;
		}
	} else {
		formfield.value = value;
	}
	
	if(column != 'bokningsid') {
		if(is_array(value_arrays[column])) value = value_arrays[column][value];
		if(value=='') value = '<br />';
		document.getElementById(column+'_show').innerHTML = value;
		if(href != '' && href != 'mailto:' && href != 'tel:') {
			document.getElementById(column+'_show').href = href;
		}
	}
}

function getFormValue(fieldname) {
	eval("var formfield = document.getElementById('bokning')."+fieldname+";");
	if(formfield.length == undefined) {
		return formfield.value;
	} else {
		for(var i = 0; i < formfield.length; i++) {
			if(formfield[i].checked) {
				return formfield[i].value;
			}
		}
		return '';
	}
}

function changeMonth(month,year,go) {
	if(go=='next') month++;
	else month--;
	
	if(month == 0) {
		month = 12;
		year--;
	}
	var url = wpath+'index.php?ajax=cal&month='+month+'&year='+year;
	
	retrieveURL(url, 'kalender');
}

function showElement(id) {
	document.getElementById(id).className='show';
}

function hideElement(id) {
	document.getElementById(id).className='hide';
}

function showHide(id) {
	if(document.getElementById(id).className == 'show')
		hideElement(id);
	else
		showElement(id);
}

function is_array(input){
	return typeof(input)=='object'&&(input instanceof Array);
}
