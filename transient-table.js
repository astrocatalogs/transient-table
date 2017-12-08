function showSpecCSV() {
	document.getElementById('speccsv').style.display = 'block';
	parent.resizeWindowToContents();
}

function eSN(name, filename, stem, bibcode) {
	var value = 
		'{\n' +
		'\t"' + name + '":{\n' + 
		'\t\t"name":"' + name + '",\n';
	if (bibcode !== undefined) {
		value +=
			'\t\t"sources":[\n' +
			'\t\t\t{\n' +
			'\t\t\t\t"bibcode":"' + bibcode + '"\n' +
			'\t\t\t}\n' +
			'\t\t],\n';
	}
	value +=
		'\t\t"alias":[\n' +
		'\t\t\t{\n' +
		'\t\t\t\t"value":"' + name + '"\n' +
		'\t\t\t}\n' +
		'\t\t]\n' +
		'\t}\n' +
		'}';
	value = encodeURIComponent(value);
	var instructions = 'PLEASE READ: Welcome to the new JSON page for ' + name + '! Before editing this file, please look at our JSON schema [https://github.com/astrocatalogs/supernovae/blob/master/SCHEMA.md]. Please replace this message with your own comments before committing.'
	var win  = window.open('https://github.com/astrocatalogs/' + stem + '-internal/new/master/?filename=' +
		encodeURIComponent(filename) + '.json&value=' + value + '&description=' + instructions, '_blank')
	win.focus();
}

function markSame(name1, name2, edit, catalog) {
	var filename = name1.replace("/", "_") + '.json';
	var codemessage = '';
	if (edit === "true") {
		codemessage += '### IMPORTANT: A FILE FOR THIS EVENT ALREADY EXISTS IN THE REPOSITORY.\n';
		codemessage += '### Due to limitations of the GitHub URL interface, you must copy the contents\n';
		codemessage += '### of this file into the existing JSON file for this event in order to edit it\n';
		codemessage += '### The location of the file to paste into is located at:\n';
		codemessage += '### https://github.com/astrocatalogs/' + catalog + '-internal/edit/master/' + encodeURIComponent(filename) + '\n';
		codemessage += '### COMMITTING THE FILE ON THIS PAGE WILL RESULT IN A "FILE ALREADY EXISTS" ERROR.\n';
		codemessage += '### Delete all lines preceded by a # before committing any changes to the file\n';
		codemessage += '### located at the above URL.\n';
	} else {
		codemessage = '';
	}
	var value = encodeURIComponent(
		codemessage +
		'{\n' +
		'\t"' + name1 + '":{\n' + 
		'\t\t"name":"' + name1 + '",\n' +
		'\t\t"alias":[\n' +
		'\t\t\t{\n' +
		'\t\t\t\t"value":"' + name1 + '"\n' +
		'\t\t\t},\n' +
		'\t\t\t{\n' +
		'\t\t\t\t"value":"' + name2 + '"\n' +
		'\t\t\t}\n' +
		'\t\t]\n' +
		'\t}\n' +
		'}')
	var instructions = encodeURIComponent('Events ' + name1 + ' and ' + name2 + ' marked as being the same via OSC duplicate finder.');
	var win = window.open('https://github.com/astrocatalogs/' + catalog + '-internal/new/master/?filename=' +
		encodeURIComponent(filename) + '&value=' + value + '&message=' + instructions, '_blank')
	win.focus();
}

function markDiff(name1, name2, edit, catalog) {
	var filename = name1.replace("/", "_") + '.json';
	var codemessage = '';
	if (edit === "true") {
		codemessage += '### IMPORTANT: A FILE FOR THIS EVENT ALREADY EXISTS IN THE REPOSITORY.\n';
		codemessage += '### Due to limitations of the GitHub URL interface, you must copy the contents\n';
		codemessage += '### of this file into the existing JSON file for this event in order to edit it\n';
		codemessage += '### The location of the file to paste into is located at:\n';
		codemessage += '### https://github.com/astrocatalogs/' + catalog + '-internal/edit/master/' + encodeURIComponent(filename) + '\n';
		codemessage += '### COMMITTING THE FILE ON THIS PAGE WILL RESULT IN A "FILE ALREADY EXISTS" ERROR.\n';
		codemessage += '### Delete all lines preceded by a # before committing any changes to the file\n';
		codemessage += '### located at the above URL.\n';
	} else {
		codemessage = '';
	}
	var value = encodeURIComponent(
		codemessage +
		'{\n' +
		'\t"' + name1 + '":{\n' + 
		'\t\t"name":"' + name1 + '",\n' +
		'\t\t"distinctfrom":[\n' +
		'\t\t\t{\n' +
		'\t\t\t\t"value":"' + name2 + '"\n' +
		'\t\t\t}\n' +
		'\t\t]\n' +
		'\t}\n' +
		'}')
	var instructions = encodeURIComponent('Events ' + name1 + ' and ' + name2 + ' marked as being distinct from one another via OSC duplicate finder.');
	var win = window.open('https://github.com/astrocatalogs/' + catalog + '-internal/new/master/?filename=' +
		encodeURIComponent(filename) + '&value=' + value + '&message=' + instructions, '_blank')

	win.focus();
}

function lookupSimbad(query) {
	var win = window.open('http://simbad.u-strasbg.fr/simbad/sim-basic?Ident=' + query, '_blank');
	win.focus();
}

function lookupTns(query) {
	var win = window.open('https://wis-tns.weizmann.ac.il/object/' + query, '_blank');
	win.focus();
}

function markError(name, quantity, sourcekind, source, edit, catalog) {
	var filename = name.replace("/", "_") + '.json';
	var sks = sourcekind.split(',');
	var sis = source.split(',');
	var sourcestring = '';
	for (i = 0; i < sks.length; i++) {
		sourcestring +=
			'\t\t\t{\n' +
			'\t\t\t\t"value":"' + sis[i] + '",\n' +
			'\t\t\t\t"kind":"' + sks[i] + '",\n' +
			'\t\t\t\t"extra":"' + quantity + '"\n' +
			'\t\t\t}' + ((i == sks.length - 1) ? '' : ',') + '\n';
	}
	var codemessage = '';
	if (edit === "true") {
		codemessage += '### IMPORTANT: A FILE FOR THIS EVENT ALREADY EXISTS IN THE REPOSITORY.\n';
		codemessage += '### Due to limitations of the GitHub URL interface, you must copy the contents\n';
		codemessage += '### of this file into the existing JSON file for this event in order to edit it\n';
		codemessage += '### The location of the file to paste into is located at:\n';
		codemessage += '### https://github.com/astrocatalogs/' + catalog + '-internal/edit/master/' + encodeURIComponent(filename) + '\n';
		codemessage += '### COMMITTING THE FILE ON THIS PAGE WILL RESULT IN A "FILE ALREADY EXISTS" ERROR.\n';
		codemessage += '### Delete all lines preceded by a # before committing any changes to the file\n';
		codemessage += '### located at the above URL.\n';
	} else {
		codemessage = '';
	}
	var value = encodeURIComponent(
		codemessage +
		'{\n' +
		'\t"' + name + '":{\n' + 
		'\t\t"name":"' + name + '",\n' +
		'\t\t"errors":[\n' +
		sourcestring +
		'\t\t]\n' +
		'\t}\n' +
		'}');
	var instructions = encodeURIComponent(name + '\'s ' + quantity + ' from ' + source + ' marked as being erroneous.');
	var win = window.open('https://github.com/astrocatalogs/' + catalog + '-internal/new/master/?filename=' +
		encodeURIComponent(filename) + '&value=' + value + '&message=' + instructions, '_blank');
	win.focus();
}

function addQuantity(name, quantity, edit, catalog) {
	var filename = name.replace("/", "_") + '.json';
	var codemessage = '';
	if (edit === "true") {
		codemessage += '### IMPORTANT: A FILE FOR THIS EVENT ALREADY EXISTS IN THE REPOSITORY.\n';
		codemessage += '### Due to limitations of the GitHub URL interface, you must copy the contents\n';
		codemessage += '### of this file into the existing JSON file for this event in order to edit it\n';
		codemessage += '### The location of the file to paste into is located at:\n';
		codemessage += '### https://github.com/astrocatalogs/' + catalog + '-internal/edit/master/' + encodeURIComponent(filename) + '\n';
		codemessage += '### COMMITTING THE FILE ON THIS PAGE WILL RESULT IN A "FILE ALREADY EXISTS" ERROR.\n';
		codemessage += '### Delete all lines preceded by a # before committing any changes to the file\n';
		codemessage += '### located at the above URL.\n';
	} else {
		codemessage = '';
	}
	var value = encodeURIComponent(
		codemessage +
		'{\n' +
		'\t"' + name + '":{\n' + 
		'\t\t"name":"' + name + '",\n' +
		'\t\t"sources":[\n' +
		'\t\t\t{\n' +
		'\t\t\t\t"bibcode":"### BIBCODE FOR NEW VALUE ###"\n' +
		'\t\t\t}\n' +
		'\t\t],\n' +
		'\t\t"' + quantity + '":[\n' +
		'\t\t\t{\n' +
		'\t\t\t\t"value":"### NEW VALUE ###",\n' +
		'\t\t\t\t"e_value":"### ERROR IN VALUE (if applicable) ###",\n' +
		'\t\t\t\t"u_value":"### VALUE\'S UNIT (if applicable) ###"\n' +
		'\t\t\t}\n' +
		'\t\t]\n' +
		'\t}\n' +
		'}');
	var instructions = encodeURIComponent('"' + quantity + '" added to ' + name + '.');
	var win = window.open('https://github.com/astrocatalogs/' + catalog + '-internal/new/master/?filename=' +
		encodeURIComponent(filename) + '&value=' + value + '&message=' + instructions, '_blank');
	win.focus();
}

function googleNames(names) {
	var namearr = names.split(',');
	
	for (var i = 0; i < namearr.length; i++) {
		if (namearr[i].startsWith('SN') && !isNaN(parseFloat(namearr[i].slice(2,6)))) {
			namearr[i] = namearr[i].replace('SN', '');
		}
		if (namearr[i].startsWith('AT') && !isNaN(parseFloat(namearr[i].slice(2,6)))) {
			namearr[i] = namearr[i].replace('AT', '');
		}
	}
	namearr = jQuery.grep(namearr, function(v, k){
		return jQuery.inArray(v, namearr) === k;
	});
	var win = window.open('https://www.google.com/#q=' + encodeURIComponent(namearr.join(" ")), '_blank')
	win.focus();
}
