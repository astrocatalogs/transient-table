function eSN(name, filename) {
	var value = encodeURIComponent(
		'{\n' +
		'\t"' + name + '":{\n' + 
		'\t\t"name":"' + name + '",\n' +
		'\t\t"aliases":[\n' +
		'\t\t\t"' + name + '"\n' +
		'\t\t]\n' +
		'\t}\n' +
		'}')
	var instructions = 'PLEASE READ: Welcome to the new JSON page for ' + name + '! Before editing this file, please read our JSON format guidelines [https://github.com/astrocatalogs/sne-internal/blob/master/OSC-JSON-format.md]. Please delete this message before committing.'
	var win  = window.open('https://github.com/astrocatalogs/sne-internal/new/master/?filename=' +
		encodeURIComponent(filename) + '.json&value=' + value + '&description=' + instructions, '_blank')
	win.focus();
}

function markSame(name1, name2, edit) {
	var filename = name1.replace("/", "_") + '.json';
	if (edit) {
		var win = window.open('https://github.com/astrocatalogs/sne-internal/edit/master/' +
			encodeURIComponent(filename), '_blank')
	} else {
		var value = encodeURIComponent(
			'### DELETE THIS LINE TO ENABLE COMMIT BUTTON ###\n' +
			'{\n' +
			'\t"' + name1 + '":{\n' + 
			'\t\t"name":"' + name1 + '",\n' +
			'\t\t"aliases":[\n' +
			'\t\t\t"' + name1 + '", "' + name2 + '"\n' +
			'\t\t]\n' +
			'\t}\n' +
			'}')
		var instructions = 'Events ' + name1 + ' and ' + name2 + ' marked as being the same via OSC duplicate finder.';
		var win = window.open('https://github.com/astrocatalogs/sne-internal/new/master/?filename=' +
			encodeURIComponent(filename) + '&value=' + value + '&message=' + instructions, '_blank')
	}
	win.focus();
}

function markDiff(name1, name2, edit) {
	var filename = name1.replace("/", "_") + '.json';
	if (edit) {
		var win = window.open('https://github.com/astrocatalogs/sne-internal/edit/master/' +
			encodeURIComponent(filename), '_blank')
	} else {
		var value = encodeURIComponent(
			'### DELETE THIS LINE TO ENABLE COMMIT BUTTON ###\n' +
			'{\n' +
			'\t"' + name1 + '":{\n' + 
			'\t\t"name":"' + name1 + '",\n' +
			'\t\t"distinctfrom":[\n' +
			'\t\t\t"' + name2 + '"\n' +
			'\t\t]\n' +
			'\t}\n' +
			'}')
		var instructions = 'Events ' + name1 + ' and ' + name2 + ' marked as being distinct from one another via OSC duplicate finder.';
		var win = window.open('https://github.com/astrocatalogs/sne-internal/new/master/?filename=' +
			encodeURIComponent(filename) + '&value=' + value + '&message=' + instructions, '_blank')
	}
	win.focus();
}
