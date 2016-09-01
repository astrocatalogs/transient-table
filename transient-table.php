<?php
/**
 * Plugin Name: Transient Table
 * Plugin URI: http://astrocrash.net
 * Description: DataTables implementation
 * Version: 1.0.0
 * Author: James Guillochon
 * Author URI: http://astrocrash.net
 * License: GPL2
 */

$tt = explode("\n", file_get_contents(__DIR__ . '/tt.dat'));
$stem = trim($tt[0]);
$modu = trim($tt[1]);
$subd = trim($tt[2]);
$invi = '"' . implode('","', explode(",", trim($tt[3]))) . '"';
$nowr = '"' . implode('","', explode(",", trim($tt[4]))) . '"';
$nwnm = '"' . implode('","', explode(",", trim($tt[5]))) . '"';
$revo = '"' . implode('","', explode(",", trim($tt[6]))) . '"';
$ocol = intval(trim($tt[7]));

function datatables_functions() {
	global $stem, $modu, $subd, $invi, $nowr, $nwnm, $revo, $ocol;
?>
	<script>
	var searchFields;
	var stem = '<?php echo $stem;?>';
	var modu = '<?php echo $modu;?>';
	var subd = '<?php echo $subd;?>';
	var invi = [<?php echo $invi;?>];
	var nowr = [<?php echo $nowr;?>];
	var nwnm = [<?php echo $nwnm;?>];
	var revo = [<?php echo $revo;?>];
	var ocol = <?php echo $ocol;?>;
	var urlstem = 'https://' + subd + '.space/' + stem + '/'; 
	function getSearchFields(allSearchCols) {
		var sf = {};
		var alen = allSearchCols.length;
		sf['search'] = jQuery('.dataTables_filter input');
		for (i = 0; i < alen; i++) {
			var col = allSearchCols[i];
			objID = document.getElementById(col);
			sf[col] = document.getElementById(col);
		}
		return sf;
	}
	function noBreak (str) {
		return str.replace(/ /g, "&nbsp;").replace(/-/g, "&#x2011;");
	}
	function someBreak (str) {
		return str.replace(/([+-])/g, "&#8203;$1");
	}
	function nameToFilename (name) {
		return name.replace(/\//g, '_')
	}
	function getAliases (row, field) {
		if (field === undefined) field = 'alias';
		var aliases = [];
		for (i = 0; i < row[field].length; i++) {
			if (typeof row[field][i] === 'string') {
				aliases.push(row[field][i]);
			} else {
				aliases.push(row[field][i].value);
			}
		}
		return aliases;
	}
	function getAliasesOnly (row, field) {
		if (field === undefined) field = 'alias';
		var aliases = [];
		for (i = 1; i < row[field].length; i++) {
			if (typeof row[field][i] === 'string') {
				aliases.push(row[field][i]);
			} else {
				aliases.push(row[field][i].value);
			}
		}
		return aliases;
	}
	function eventAliases ( row, type, val, meta, field ) {
		if (field === undefined) field = 'alias';
		if (!row[field]) return '';
		var aliases = getAliases(row, field);
		return aliases.join(', ');
	}
	function eventAliasesOnly ( row, type, val, meta, field ) {
		if (field === undefined) field = 'alias';
		if (!row[field]) return '';
		if (row[field].length > 1) {
			var aliases = getAliasesOnly(row, field);
			return aliases.join(', ');
		} else return '';
	}
	function goToEvent( id ){
		var ddl = document.getElementById( id );
		var selectedVal = ddl.options[ddl.selectedIndex].value;

		window.open(urlstem + encodeURIComponent(selectedVal) + '/', '_blank');
	}
	function nameLinkedName ( row, type, val, meta ) {
		return nameLinked ( row, type, val, meta, 'name', 'alias' );
	}
	function nameLinkedName1 ( row, type, val, meta ) {
		return nameLinked ( row, type, val, meta, 'name1', 'aliases1' );
	}
	function nameLinkedName2 ( row, type, val, meta ) {
		return nameLinked ( row, type, val, meta, 'name2', 'aliases2' );
	}
	function nameLinked ( row, type, val, meta, namefield, aliasfield ) {
		if (namefield === undefined) namefield = 'name';
		if (aliasfield === undefined) aliasfield = 'alias';
		if (row[aliasfield].length > 1) {
			var aliases = getAliasesOnly(row, aliasfield);
			return "<div class='tooltip'><a href='" + urlstem + nameToFilename(row[namefield]) +
				"/' target='_blank'>" + noBreak(row[namefield]) + "</a><span class='tooltiptext'> " + aliases.map(noBreak).join(', ') + "</span></div>";
		} else {
			return "<a href='" + urlstem + nameToFilename(row[namefield]) + "/' target='_blank'>" + noBreak(row[namefield]) + "</a>";
		}
	}
	function nameSwitcherName ( data, type, row, meta ) {
		return nameSwitcher ( data, type, row, meta, 'name', 'alias' );
	}
	function nameSwitcherName1 ( data, type, row, meta ) {
		return nameSwitcher ( data, type, row, meta, 'name1', 'aliases1' );
	}
	function nameSwitcherName2 ( data, type, row, meta ) {
		return nameSwitcher ( data, type, row, meta, 'name2', 'aliases2' );
	}
	function nameSwitcher ( data, type, row, meta, namefield, aliasfield ) {
		if (namefield === undefined) namefield = 'name';
		if (aliasfield === undefined) aliasfield = 'alias';
		if ( (type === 'display' || type === 'sort') ) {
			if ( row[aliasfield].length > 1 ) {
				var idObj = searchFields[namefield];
				var filterTxt = searchFields['search'].val().toUpperCase().replace(/"/g, '');
				var idObjTxt = (idObj === null) ? '' : idObj.value.toUpperCase().replace(/"/g, '');
				var txts = [filterTxt, idObjTxt];
				var tlen = txts.length;
				for (var t = 0; t < tlen; t++) {
					var txt = txts[t];
					if (txt !== "") {
						var aliases = getAliases(row, aliasfield);
						var primaryname = row[namefield];
						var alen = aliases.length;
						for (var a = 0; a < alen; a++) {
							if (aliases[a].toUpperCase().indexOf(txt) !== -1) {
								primaryname = aliases[a];
								break;
							}
						}
						if (type === 'sort') {
							return primaryname;
						}
						var otheraliases = [];
						for (var a = 0; a < alen; a++) {
							if (aliases[a].toUpperCase() === primaryname.toUpperCase()) {
								continue;
							}
							otheraliases.push(noBreak(aliases[a]));
						}
						return "<div class='tooltip'><a href='" + urlstem + nameToFilename(row[namefield]) +
							"/' target='_blank'>" + primaryname + "</a><span class='tooltiptext'> " + otheraliases.join(', ') + "</span></div>";
					}
				}
			}
			if (type === 'display') {
				return nameLinked(row, null, null, null, namefield, aliasfield);
			}
			return row[namefield];
		} else if (type === 'filter') {
			return eventAliases(row, null, null, null, aliasfield);
		}
		return row[namefield];
	}
	function hostLinked ( row, type, val, meta ) {
		var host = "<a class='" + (('kind' in row.host[0] && row.host[0]['kind'] == 'cluster') ? "hci" : "hhi") +
			"' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a>&nbsp;";
		var mainHost = "<a href='http://simbad.u-strasbg.fr/simbad/sim-basic?Ident=" + row.host[0]['value'] +
			"&submit=SIMBAD+search' target='_blank'>" + someBreak(row.host[0]['value']) + "</a>"; 
		var hostImg = (row.ra && row.dec) ? ("<div class='tooltipimg' " +
			"style='background-image:url(" + urlstem + nameToFilename(row.name) + "-host.jpg);'></div>") : "";
		var hlen = row.host.length;
		if (hlen > 1) {
			var hostAliases = '';
			for (var i = 1; i < hlen; i++) {
				if (i != 1) hostAliases += ', ';
				hostAliases += noBreak(row.host[i]['value']);
			}
			return "<div class='tooltip'>" + host + mainHost + "<span class='tooltiptext'> " +
				hostImg + 'AKA: ' + hostAliases + "</span></div>";
		} else {
			if (hostImg) {
				return "<div class='tooltip'>" + host + mainHost + "<span class='tooltiptext'> " +
					hostImg + "</span></div>";
			} else return host + mainHost;
		}
	}
	function hostSwitcher ( data, type, row, meta ) {
		if (!row.host) return '';
		//return row.host[0].value;
		var hlen = row.host.length;
		if ( (type === 'display' || type === 'sort') ) {
			if (hlen > 1) {
				var mainHost = "<a href='http://simbad.u-strasbg.fr/simbad/sim-basic?Ident=%s&submit=SIMBAD+search' target='_blank'>%s</a>"; 
				var hostImg = (row.ra && row.dec) ? ("<div class='tooltipimg' " +
					"style=background-image:url(" + urlstem + nameToFilename(row.name) + "-host.jpg);'></div>") : "";
				var idObj = searchFields['host'];
				var filterTxt = searchFields['search'].val().toUpperCase().replace(/"/g, '');
				var idObjTxt = (idObj === null) ? '' : idObj.value.toUpperCase().replace(/"/g, '');
				var txts = [filterTxt, idObjTxt];
				var tlen = txts.length;
				for (var t = 0; t < tlen; t++) {
					var txt = txts[t];
					if (txt !== "") {
						var aliases = [];
						for (i = 0; i < hlen; i++) {
							aliases.push(row.host[i]['value']);
						}
						var primaryname = aliases[0];
						var primarykind = ('kind' in row.host[0]) ? row.host[0]['kind'] : '';
						var alen = aliases.length;
						for (var a = 1; a < alen; a++) {
							if (aliases[a].toUpperCase().indexOf(txt) !== -1) {
								primaryname = aliases[a];
								primarykind = ('kind' in row.host[a]) ? row.host[a]['kind'] : '';
								break;
							}
						}
						if (type === 'sort') {
							return primaryname;
						}
						var otheraliases = [];
						for (var a = 0; a < alen; a++) {
							if (aliases[a].toUpperCase() === primaryname.toUpperCase()) {
								continue;
							}
							otheraliases.push(noBreak(aliases[a]));
						}
						var host = "<a class='" + ((primarykind == 'cluster') ? "hci" : "hhi") +
							"' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> ";
						return "<div class='tooltip'>" + host + mainHost.replace(/%s/g, primaryname) + "<span class='tooltiptext'> " +
							hostImg + 'AKA: ' + otheraliases.join(', ') + "</span></div>";
					}
				}
			}
			if (type === 'display') {
				return hostLinked(row);
			}
			if (!row.host[0]) return '';
			return row.host[0].value;
		} else if (type === 'filter') {
			var hostAliases = [];
			for (var a = 0; a < hlen; a++) {
				hostAliases.push(row.host[a].value);
			}
			return hostAliases.join(', ');
		}
		if (!row.host[0]) return '';
		return row.host[0].value;
	}
	function typeLinked ( row, type, val, meta ) {
		var clen = row.claimedtype.length;
		if (clen > 1) {
			var altTypes = '';
			for (var i = 1; i < clen; i++) {
				if (i != 1) altTypes += ', ';
				altTypes += noBreak(row.claimedtype[i]['value']);
			}
			return "<div class='tooltip'>" + noBreak(row.claimedtype[0]['value']) + "</a><span class='tooltiptext'> " + altTypes + "</span></div>";
		} else if (row.claimedtype[0]) {
			return row.claimedtype[0]['value'];
		}
		return '';
	}
	function typeSwitcher ( data, type, row, meta ) {
		if (!row.claimedtype) return '';
		var clen = row.claimedtype.length;
		if (clen === 0) return '';
		//return row.claimedtype[0]['value'];
		if ( (type === 'display' || type === 'sort') ) {
			if ( clen > 1 ) {
				var idObj = searchFields['claimedtype'];
				var filterTxt = searchFields['search'].val().toUpperCase().replace(/"/g, '');
				var idObjTxt = (idObj === null) ? '' : idObj.value.toUpperCase().replace(/"/g, '');
				var txts = [filterTxt, idObjTxt];
				var tlen = txts.length;
				for (var t = 0; t < tlen; t++) {
					var txt = txts[t];
					if (txt !== "") {
						var types = [];
						for (i = 0; i < clen; i++) {
							types.push(row.claimedtype[i]['value']);
						}
						var primarytype = types[0];
						var ylen = types.length;
						for (var a = 1; a < ylen; a++) {
							if (types[a].toUpperCase().indexOf(txt) !== -1) {
								primarytype = types[a];
								break;
							}
						}
						if (type === 'sort') {
							return primarytype;
						}
						var othertypes = [];
						for (var a = 0; a < ylen; a++) {
							if (types[a].toUpperCase() === primarytype.toUpperCase()) {
								continue;
							}
							othertypes.push(types[a]);
						}
						return "<div class='tooltip'>" + primarytype + "<span class='tooltiptext'> " + othertypes + "</span></div>";
					}
				}
			}
			if (type === 'display') {
				return typeLinked(row);
			}
			return row.claimedtype[0].value;
		} else if (type === 'filter') {
			var allTypes = [];
			for (var a = 0; a < clen; a++) {
				allTypes.push(row.claimedtype[a].value);
			}
			return allTypes.join(', ');
		}
		return row.claimedtype[0].value;
	}
	function noBlanksNumRender ( data, type, row ) {
		if ( type === 'sort' ) {
			if (data === '' || data === null || typeof data !== 'string') return NaN;
			return parseFloat(String(data).split(',')[0].replace(/<(?:.|\n)*?>/gm, '').trim());
		}
		return data;
	}
	function noBlanksStrRender ( data, type, row ) {
		if ( type === 'sort' ) {
			if (data === '' || data === null || typeof data !== 'string') return NaN;
			return String(data).split(',')[0].replace(/<(?:.|\n)*?>/gm, '').trim();
		}
		return data;
	}
	function raToDegrees ( data ) {
		var str = data.trim();
		var parts = str.split(':');
		var value = 0.0;
		if (parts.length >= 1) {
			value += 360./24.*Number(parts[0]);
			var sign = 1.0;
			if (parts[0][0] == '-') {
				var sign = -1.0;
			}
		}
		if (parts.length >= 2) {
			value += sign*Number(parts[1])*360./(24*60.);
		}
		if (parts.length >= 3) {
			value += sign*Number(parts[2])*360./(24*3600.);
		}
		return value;
	}
	function decToDegrees ( data ) {
		var str = data.trim();
		var parts = str.split(':');
		var value = 0.0;
		if (parts.length >= 1) {
			value += Number(parts[0]);
			var sign = 1.0;
			if (parts[0][0] == '-') {
				var sign = -1.0;
			}
		}
		if (parts.length >= 2) {
			value += sign*Number(parts[1])/60.;
		}
		if (parts.length >= 3) {
			value += sign*Number(parts[2])/3600.;
		}
		return value;
	}
	jQuery.extend( jQuery.fn.dataTableExt.oSort, {
		"non-empty-string-asc": function (str1, str2) {
			if(isNaN(str1) && isNaN(str2))
				return 0;
			if(isNaN(str1))
				return 1;
			if(isNaN(str2))
				return -1;
			return ((str1 < str2) ? -1 : ((str1 > str2) ? 1 : 0));
		},
		"non-empty-string-desc": function (str1, str2) {
			if(isNaN(str1) && isNaN(str2))
				return 0;
			if(isNaN(str1))
				return 1;
			if(isNaN(str2))
				return -1;
			return ((str1 < str2) ? 1 : ((str1 > str2) ? -1 : 0));
		},
		"non-empty-float-asc": function (v1, v2) {
			if(isNaN(v1) && isNaN(v2))
				return 0;
			if(isNaN(v1))
				return 1;
			if(isNaN(v2))
				return -1;
			return ((v1 < v2) ? -1 : ((v1 > v2) ? 1 : 0));
		},
		"non-empty-float-desc": function (v1, v2) {
			if(isNaN(v1) && isNaN(v2))
				return 0;
			if(isNaN(v1))
				return 1;
			if(isNaN(v2))
				return -1;
			return ((v1 < v2) ? 1 : ((v1 > v2) ? -1 : 0));
		}
	} );
	function compDates ( date1, date2, includeSame ) {
		var d1;
		var d1split = date1.split('/');
		var d1len = d1split.length;
		if (d1len == 1) {
			d1 = new Date(date1 + '/12/31');
		} else if (d1len == 2) {
			var daysInMonth = new Date(d1split[0], d1split[1], 0).getDate();
			d1 = new Date(date1 + '/' + String(daysInMonth));
		} else {
			d1 = new Date(date1);
		}
		var d2;
		var d2split = date2.split('/');
		var d2len = d2split.length;
		if (d2len == 1) {
			d2 = new Date(date2 + '/12/31');
		} else if (d2len == 2) {
			var daysInMonth = new Date(d2split[0], d2split[1], 0).getDate();
			d2 = new Date(date2 + '/' + String(daysInMonth));
		} else {
			d2 = new Date(date2);
		}

		if (includeSame) {
			return d1.getTime() <= d2.getTime();
		} else {
			return d1.getTime() < d2.getTime();
		}
	}
	function compRaDecs ( radec1inp, radec2inp, includeSame ) {
		var rd1, rd2;
		var sign1, sign2;
		if (radec1inp.length > 0) {
			if (radec1inp[0] == '+') {
				radec1 = radec1inp.slice(1,-1);
				sign1 = 1.0;
			} else if (radec1inp[0] == '-') {
				radec1 = radec1inp.slice(1,-1);
				sign1 = -1.0;
			} else {
				radec1 = radec1inp;
				sign1 = 1.0;
			}
		}
		if (radec2inp.length > 0) {
			if (radec2inp[0] == '+') {
				radec2 = radec2inp.slice(1,-1);
				sign2 = 1.0;
			} else if (radec2inp[0] == '-') {
				radec2 = radec2inp.slice(1,-1);
				sign2 = -1.0;
			} else {
				radec2 = radec2inp;
				sign2 = 1.0;
			}
		}
		var rd1split = radec1.split(':');
		var rd2split = radec2.split(':');
		var rd1len = rd1split.length;
		if (rd1len == 1) {
			rd1 = parseFloat(rd1split[0]);
		} else if (rd1len == 2) {
			rd1 = parseFloat(rd1split[0]) + parseFloat(rd1split[1])/60.;
		} else {
			rd1 = parseFloat(rd1split[0]) + parseFloat(rd1split[1])/60. + parseFloat(rd1split[2])/3600.;
		}
		var rd2len = rd2split.length;
		if (rd2len == 1) {
			rd2 = parseFloat(rd2split[0]);
		} else if (rd2len == 2) {
			rd2 = parseFloat(rd2split[0]) + parseFloat(rd2split[1])/60.;
		} else {
			rd2 = parseFloat(rd2split[0]) + parseFloat(rd2split[1])/60. + parseFloat(rd2split[2])/3600.;
		}

		if (includeSame) {
			return sign1*rd1 <= sign2*rd2;
		} else {
			return sign1*rd1 < sign2*rd2;
		}
	}
	function advancedDateFilter ( data, id ) {
		var idObj = document.getElementById(id);
		if ( idObj === null ) return true;
		var idString = idObj.value;
		if ( idString === '' ) return true;
		var isNot = (idString.indexOf('!') !== -1 || idString.indexOf('NOT') !== -1)
		idString = idString.replace(/!/g, '');
		var splitString = idString.split(/(?:,|OR)+/);
		var splitData = data.split(/(?:,|OR)+/);
		var sdlen = splitData.length;
		for ( var d = 0; d < sdlen; d++ ) {
			var cData = splitData[d].trim();
			var sslen = splitString.length;
			for ( var i = 0; i < sslen; i++ ) {
				if ( splitString[i].indexOf('-') !== -1 )
				{
					var splitRange = splitString[i].split('-');
					var minStr = splitRange[0].replace(/[<=>]/g, '').trim();
					var maxStr = splitRange[1].replace(/[<=>]/g, '').trim();
					if (minStr !== '') {
						if (!( (minStr !== '' && compDates(cData, minStr, true)) ||
							   (maxStr !== '' && compDates(maxStr, cData, true)) || cData === '' )) return !isNot;
					}
				}
				var idStr = splitString[i].replace(/[<=>]/g, '').trim();
				if ( idStr === "" || idStr === NaN || idStr === '-' ) {
					if (i === 0) return !isNot;
				}
				if ( idStr === "" || idStr === NaN ) {
					if (i === 0) return !isNot;
				}
				else {
					//if (cData === '') return false;
					if ( splitString[i].indexOf('<=') !== -1 )
					{
						if ( compDates(cData, idStr, true) ) return !isNot;
					}
					else if ( splitString[i].indexOf('<') !== -1 )
					{
						if ( compDates(cData, idStr, false) ) return !isNot;
					}
					else if ( splitString[i].indexOf('>=') !== -1 )
					{
						if ( compDates(idStr, cData, true) ) return !isNot;
					}
					else if ( splitString[i].indexOf('>') !== -1 )
					{
						if ( compDates(idStr, cData, false) ) return !isNot;
					}
					else
					{
						if ( idStr.indexOf('"') !== -1 ) {
							idStr = String(idStr.replace(/"/g, '').trim());
							if ( cData === idStr ) return !isNot;
						}
						else {
							if ( cData.indexOf(idStr) !== -1 ) return !isNot;
						}
					}
				}
			}
		}
		return isNot;
	}
	function advancedRaDecFilter ( data, id ) {
		var idObj = document.getElementById(id);
		if ( idObj === null ) return true;
		var idString = idObj.value;
		if ( idString === '' ) return true;
		var isNot = (idString.indexOf('!') !== -1 || idString.indexOf('NOT') !== -1)
		idString = idString.replace(/!/g, '');
		var splitString = idString.split(/(?:,|OR)+/);
		var splitData = data.split(/(?:,|OR)+/);
		var sdlen = splitData.length;
		for ( var d = 0; d < sdlen; d++ ) {
			var cData = splitData[d].trim();
			var sslen = splitString.length;
			for ( var i = 0; i < sslen; i++ ) {
				if ( splitString[i].indexOf('-') !== -1 )
				{
					var splitRange = splitString[i].split('-');
					var minStr = splitRange[0].replace(/[<=>]/g, '').trim();
					var maxStr = splitRange[1].replace(/[<=>]/g, '').trim();
					if (minStr !== '') {
						if (!( (minStr !== '' && compRaDecs(cData, minStr, true)) ||
							   (maxStr !== '' && compRaDecs(maxStr, cData, true)) || cData === '' )) return !isNot;
					}
				}
				var idStr = splitString[i].replace(/[<=>]/g, '').trim();
				if ( idStr === "" || idStr === NaN || idStr === '-' ) {
					if (i === 0) return !isNot;
				}
				if ( idStr === "" || idStr === NaN ) {
					if (i === 0) return !isNot;
				}
				else {
					//if (cData === '') return false;
					if ( splitString[i].indexOf('<=') !== -1 )
					{
						if ( compRaDecs(cData, idStr, true) ) return !isNot;
					}
					else if ( splitString[i].indexOf('<') !== -1 )
					{
						if ( compRaDecs(cData, idStr, false) ) return !isNot;
					}
					else if ( splitString[i].indexOf('>=') !== -1 )
					{
						if ( compRaDecs(idStr, cData, true) ) return !isNot;
					}
					else if ( splitString[i].indexOf('>') !== -1 )
					{
						if ( compRaDecs(idStr, cData, false) ) return !isNot;
					}
					else
					{
						if ( idStr.indexOf('"') !== -1 ) {
							idStr = String(idStr.replace(/"/g, '').trim());
							if ( cData === idStr ) return !isNot;
						}
						else {
							if ( cData.indexOf(idStr) !== -1 ) return !isNot;
						}
					}
				}
			}
		}
		return isNot;
	}
	function advancedStringFilter ( data, id ) {
		var idObj = document.getElementById(id);
		if ( idObj === null ) return true;
		var idString = idObj.value;
		if ( idString === '' ) return true;
		var splitString = idString.split(/(?:,|OR)+/);
		var splitData = data.split(',');
		var sdlen = splitData.length;
		for ( var d = 0; d < sdlen; d++ ) {
			var cData = splitData[d].trim();
			var sslen = splitString.length;
			for ( var i = 0; i < sslen; i++ ) {
				var idStr = splitString[i].trim().toUpperCase();
				var isNot = (idStr.indexOf('!') !== -1 || idStr.indexOf('NOT') !== -1)
				idStr = idStr.replace(/!/g, '');
				if ( idStr === "" || idStr === NaN ) {
					if (i === 0) return !isNot;
				}
				else {
					var lowData = cData.toUpperCase();
					if ( idStr.indexOf('"') !== -1 ) {
						idStr = idStr.replace(/"/g, '');
						if ( isNot ) {
							return ( lowData !== idStr );
						} else {
							if ( lowData === idStr ) return true;
						}
					}
					else {
						if ( isNot ) {
							return ( lowData.indexOf(idStr) === -1 );
						} else {
							if ( lowData.indexOf(idStr) !== -1 ) return true;
						}
					}
				}
			}
		}
		return false;
	}
	function advancedFloatFilter ( data, id ) {
		var idObj = document.getElementById(id);
		if ( idObj === null ) return true;
		var idString = idObj.value;
		if ( idString === '' ) return true;
		var splitString = idString.split(/(?:,|OR)+/);
		var splitData = data.split(/(?:,|OR)+/);
		var sdlen = splitData.length;
		for ( var d = 0; d < sdlen; d++ ) {
			var cData = splitData[d].trim();
			var cVal = cData*1.0;
			var sslen = splitString.length;
			for ( var i = 0; i < sslen; i++ ) {
				if ( splitString[i].indexOf('-') !== -1 )
				{
					var splitRange = splitString[i].split('-');
					var newSplitRange = [];
					var srlen = splitRange.length;
					for ( var j = 0; j < srlen; j++ ) {
						if ( j < srlen - 1 && splitRange[j].length == 0 ) {
							splitRange[j+1] = '-' + splitRange[j+1];
						} else {
							newSplitRange.push(splitRange[j]);
						}
					}
					splitRange = newSplitRange;
					var minStr = splitRange[0].replace(/[<=>]/g, '').trim();
					var maxStr = splitRange[1].replace(/[<=>]/g, '').trim();
					var minVal = parseFloat(minStr);
					var maxVal = parseFloat(maxStr);
					if (maxVal < minVal) {
						var temp = maxVal;
						maxVal = minVal;
						minVal = temp;
					}
					if (minStr !== '') {
						if (!( (minStr !== '' && cVal < minVal) || (maxStr !== '' && cVal > maxVal) )) return !isNot;
					}
				}
				var idStr = splitString[i].replace(/[<=>]/g, '').trim();
				var isNot = (idStr.indexOf('!') !== -1 || idStr.indexOf('NOT') !== -1)
				idStr = idStr.replace(/!/g, '');
				if ( idStr === "" || idStr === NaN || idStr === '-' ) {
					if (i === 0) return true;
				}
				else {
					idVal = idStr*1.0;
					if ( splitString[i].indexOf('<=') !== -1 )
					{
						if ( idVal >= cVal ) return true;
					}
					else if ( splitString[i].indexOf('<') !== -1 )
					{
						if ( idVal > cVal ) return true;
					}
					else if ( splitString[i].indexOf('>=') !== -1 )
					{
						if ( idVal <= cVal ) return true;
					}
					else if ( splitString[i].indexOf('>') !== -1 )
					{
						if ( idVal < cVal ) return true;
					}
					else
					{
						if ( idStr.indexOf('"') !== -1 ) {
							idStr = String(idStr.replace(/"/g, '').trim());
							if ( isNot ) {
								return ( cData !== idStr );
							} else {
								if ( cData === idStr ) return true;
							}
						}
						else {
							if ( isNot ) {
								return ( cData.indexOf(idStr) === -1 );
							} else {
								if ( cData.indexOf(idStr) !== -1 ) return true;
							}
						}
					}
				}
			}
		}
		return false;
	}
	function needAdvanced (str) {
		var advancedStrs = ['!', 'NOT', '-', 'OR', ',', '<', '>', '='];
		return (advancedStrs.some(function(v) { return str === v; }));
	}
	</script>
<?php
}

function transient_catalog($bones = False) {
	global $stem, $modu;
	readfile("/var/www/html/" . $stem . "/astrocats/astrocats/" . $modu . "/html/table-templates/catalog.html");
?>
	<script>
	var bones = <?php echo json_encode($bones); ?>;
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['redshift', 'ebv', 'photolink', 'spectralink', 'radiolink',
			'xraylink', 'maxappmag', 'maxabsmag', 'velocity', 'lumdist', 'hostoffsetang', 'hostoffsetdist'];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['name', 'alias', 'host', 'instruments', 'claimedtype'];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = ['ra', 'dec', 'hostra', 'hostdec'];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ 'discoverdate', 'maxdate' ];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
		function ebvValue ( row, type, val, meta ) {
			if (!row.ebv) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat(row.ebv[0]['value']);
		}
		function ebvLinked ( row, type, val, meta ) {
			if (!row.ebv) return '';
			return row.ebv[0]['value']; 
		}
		function photoLinked ( row, type, val, meta ) {
			if (!row.photolink) return '';
			if (row.photolink.indexOf(',') !== -1) {
				var photosplit = row.photolink.split(',');
				return "<div class='tooltip'><a class='lci' href='" + urlstem + nameToFilename(row.name) +
					"/' target='_blank'></a> " + photosplit[0] + "<span class='tooltiptext'> Detected epochs: " + photosplit[1] + " – " + photosplit[2] + "</span></div>"; 
			}
			return "<a class='lci' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> " + row.photolink; 
		}
		function photoSort ( row, type, val ) {
			if (!row.photolink) return NaN;
			return parseInt(row.photolink.split(',')[0]);
		}
		function photoValue ( row, type, val, meta ) {
			if (!row.photolink) return '';
			return parseInt(row.photolink.split(',')[0]);
		}
		function spectraLinked ( row, type, val, meta ) {
			if (!row.spectralink) return '';
			if (row.spectralink.indexOf(',') !== -1) {
				var spectrasplit = row.spectralink.split(',');
				return "<div class='tooltip'><a class='sci' href='" + urlstem + nameToFilename(row.name) +
					"/' target='_blank'></a> " + spectrasplit[0] + "<span class='tooltiptext'> Epochs: " + spectrasplit[1] + " – " + spectrasplit[2] + "</span></div>"; 
			}
			return "<a class='sci' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> " + row.spectralink; 
		}
		function spectraValue ( row, type, val, meta ) {
			if (!row.spectralink) {
				if (type === 'sort') return NaN;
				return '';
			}
			var spectrasplit = row.spectralink.split(',');
			var data = parseInt(spectrasplit[0]);
			return data;
		}
		function radioLinked ( row, type, val, meta ) {
			if (!row.radiolink) return '';
			return "<a class='rci' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> " + row.radiolink; 
		}
		function xrayLinked ( row, type, val, meta ) {
			if (!row.xraylink) return '';
			return "<a class='xci' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> " + row.xraylink; 
		}
		function hostoffsetangValue ( row, type, val, meta ) {
			if (!row.hostoffsetang) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.hostoffsetang[0]['value']);
			return data;
		}
		function hostoffsetdistValue ( row, type, val, meta ) {
			if (!row.hostoffsetdist) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.hostoffsetdist[0]['value']);
			return data;
		}
		function redshiftValue ( row, type, val, meta ) {
			if (!row.redshift) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.redshift[0]['value']);
			return data;
		}
		function velocityValue ( row, type, val, meta ) {
			if (!row.velocity) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.velocity[0]['value']);
			return data;
		}
		function lumdistValue ( row, type, val, meta ) {
			if (!row.lumdist) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.lumdist[0]['value']);
			return data;
		}
		function redshiftLinked ( row, type, val, meta ) {
			if (!row.redshift) return '';
			var data = row.redshift[0]['value'];
			var maxdiff = 0.0;
			var rlen = row.redshift.length;
			for (i = 1; i < rlen; i++) {
				maxdiff = Math.max(Math.abs(parseFloat(data) - row.redshift[i]['value']), maxdiff);
			}
			if (maxdiff / parseFloat(data) > 0.05) {
				data = '<em>' + data + '</em>';
			}
			if (row.redshift[0]['kind']) {
				var kind = row.redshift[0]['kind'];
				return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + kind + "</span></div>";
			}
			return data;
		}
		function velocityLinked ( row, type, val, meta ) {
			if (!row.velocity) return '';
			var data = row.velocity[0]['value'];
			if (row.velocity[0]['kind']) {
				var kind = row.velocity[0]['kind'];
				return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + kind + "</span></div>";
			}
			return data;
		}
		function lumdistLinked ( row, type, val, meta ) {
			if (!row.lumdist) return '';
			var data = row.lumdist[0]['value'];
			if (row.lumdist[0]['kind']) {
				var kind = row.lumdist[0]['kind'];
				return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + kind + "</span></div>";
			}
			return data;
		}
		function raValue ( row, type, val, meta ) {
			if (!row.ra) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.ra[0]['value'];
			return raToDegrees(data);
		}
		function decValue ( row, type, val, meta ) {
			if (!row.dec) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.dec[0]['value'];
			return decToDegrees(data);
		}
		function raLinked ( row, type, val, meta ) {
			if (!row.ra) return '';
			var data = row.ra[0]['value'];
			var degrees = raToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function decLinked ( row, type, val, meta ) {
			if (!row.dec) return '';
			var data = row.dec[0]['value'];
			var degrees = decToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function hostraValue ( row, type, val, meta ) {
			if (!row.hostra) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.hostra[0]['value'];
			return raToDegrees(data);
		}
		function hostdecValue ( row, type, val, meta ) {
			if (!row.hostdec) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.hostdec[0]['value'];
			return decToDegrees(data);
		}
		function hostraLinked ( row, type, val, meta ) {
			if (!row.hostra) return '';
			var data = row.hostra[0]['value'];
			var degrees = raToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function hostdecLinked ( row, type, val, meta ) {
			if (!row.hostdec) return '';
			var data = row.hostdec[0]['value'];
			var degrees = decToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		Date.prototype.getJulian = function() {
			return Math.round((this / 86400000) - (this.getTimezoneOffset()/1440) + 2440587.5, 0.1);
		}
		function maxDateValue ( row, type, val, meta ) {
			if (!row.maxdate) {
				if (type === 'sort') return NaN;
				return '';
			}
			var mydate = new Date(row.maxdate[0]['value']);
			return mydate.getTime();
		}
		function discoverDateValue ( row, type, val, meta ) {
			if (!row.discoverdate) {
				if (type === 'sort') return NaN;
				return '';
			}
			var mydate = new Date(row.discoverdate[0]['value']);
			return mydate.getTime();
		}
		function maxDateLinked ( row, type, val, meta ) {
			if (!row.maxdate) return '';
			var mydate = new Date(row.maxdate[0]['value']);
			var mjd = String(mydate.getJulian() - 2400000.5);
			return "<div class='tooltip'>" + row.maxdate[0]['value'] + "<span class='tooltiptext'> MJD: " + mjd + "</span></div>";
		}
		function discoverDateLinked ( row, type, val, meta ) {
			if (!row.discoverdate) return '';
			mydate = new Date(row.discoverdate[0]['value']);
			mjd = String(mydate.getJulian() - 2400000.5);
			return "<div class='tooltip'>" + row.discoverdate[0]['value'] + "<span class='tooltiptext'> MJD: " + mjd + "</span></div>";
		}
		function dataLinked ( row, type, val, meta ) {
			var fileeventname = nameToFilename(row.name);
			var datalink = "<a class='dci' title='Download Data' href='" + urlstem + fileeventname + ".json' download></a>"
			if (!row.download || row.download != 'e') {
				return (datalink + "<a class='eci' title='Edit Data' onclick='eSN(\"" + row.name + "\",\"" + fileeventname + "\")'></a>") 
			} else {
				return (datalink + "<a class='eci' title='Edit Data' href='https://github.com/astrocatalogs/" + stem + "-internal/edit/master/"
					+ fileeventname + ".json' target='_blank'></a>")
			}
		}
		function refLinked ( row, type, val, meta ) {
			if (!row.references) return '';
			var references = row.references.split(',');
			var refstr = '';
			var rlen = references.length;
			var rlen4 = Math.min(rlen, 4);
			for (var i = 0; i < rlen4; i++) {
				if (i != 0) refstr += "<br>";
				refstr += "<a href='http://adsabs.harvard.edu/abs/" + references[i] + "' target='_blank'>" + references[i] + "</a>";
			}
			if (rlen >= 5) {
				var fileeventname = nameToFilename(row.name);
				refstr += "<br><a href='" + stem + "/" + fileeventname + "/'>(See full list)</a>";
			}
			return refstr;
		}
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (classname == 'alias') {
				jQuery(this).remove();
			}
			if (['check', 'download', 'references', 'responsive'].indexOf(classname) >= 0) {
				jQuery(this).html( '' );
			}
			if (['check', 'alias', 'download', 'references', 'responsive'].indexOf(classname) >= 0) return;
			var fslen = floatSearchCols.length;
			for (i = 0; i < fslen; i++) {
				if (jQuery(this).hasClass(floatSearchCols[i])) {
					floatColValDict[index] = floatSearchCols[i];
					floatColInds.push(index);
					break;
				}
			}
			var sslen = stringSearchCols.length;
			for (i = 0; i < sslen; i++) {
				if (jQuery(this).hasClass(stringSearchCols[i])) {
					stringColValDict[index] = stringSearchCols[i];
					stringColInds.push(index);
					break;
				}
			}
			var dslen = dateSearchCols.length;
			for (i = 0; i < dslen; i++) {
				if (jQuery(this).hasClass(dateSearchCols[i])) {
					dateColValDict[index] = dateSearchCols[i];
					dateColInds.push(index);
					break;
				}
			}
			var rdlen = raDecSearchCols.length;
			for (i = 0; i < rdlen; i++) {
				if (jQuery(this).hasClass(raDecSearchCols[i])) {
					raDecColValDict[index] = raDecSearchCols[i];
					raDecColInds.push(index);
					break;
				}
			}
			if (classname == 'name') {
				jQuery(this).attr('colspan', 2);
			}
            jQuery(this).html( '<input class="colsearch" type="search" id="'+classname+'" placeholder="'+title+'" />' );
        } );
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../astrocats/astrocats/' + modu + '/output/' + ((bones) ? 'bones' : 'catalog') + '.min.json',
				dataSrc: ''
			},
			"language": {
				"loadingRecords": "Loading... (should take a few seconds)"
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": null, "type": "string", "responsivePriority": 1, "render": nameSwitcherName },
				{ "data": {
					"_": eventAliases,
					"display": eventAliasesOnly,
				  }, "type": "string" },
				{ "data": {
					"display": discoverDateLinked,
					"filter": "discoverdate.0.value",
					"sort": discoverDateValue,
					"_": "discoverdate[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 2 },
				{ "data": {
					"display": maxDateLinked,
					"filter": "maxdate.0.value",
					"sort": maxDateValue,
					"_": "maxdate[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "" },
				{ "data": "maxappmag.0.value", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": "maxabsmag.0.value", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": null, "type": "string", "width":"14%", "render": hostSwitcher },
				{ "data": {
					"display": raLinked,
					"filter": "ra.0.value",
					"sort": raValue,
					"_": "ra[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"display": decLinked,
					"filter": "dec.0.value",
					"sort": decValue,
					"_": "dec[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"display": hostraLinked,
					"filter": "hostra.0.value",
					"sort": hostraValue,
					"_": "hostra[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"display": hostdecLinked,
					"filter": "hostdec.0.value",
					"sort": hostdecValue,
					"_": "hostdec[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"filter": hostoffsetangValue,
					"sort": hostoffsetangValue,
					"_": "hostoffsetang.0.value"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"filter": hostoffsetdistValue,
					"sort": hostoffsetdistValue,
					"_": "hostoffsetdist.0.value"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": "instruments", "type": "string", "defaultContent": "" },
				{ "data": {
					"display": redshiftLinked,
					"filter": redshiftValue,
					"sort": redshiftValue,
					"_": "redshift[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "" },
				{ "data": {
					"display": velocityLinked,
					"filter": velocityValue,
					"sort": velocityValue,
					"_": "velocity[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "" },
				{ "data": {
					"display": lumdistLinked,
					"filter": lumdistValue,
					"sort": lumdistValue,
					"_": "lumdist[, ].value"
				  }, "type": "non-empty-float", "defaultContent": "" },
				{ "data": null, "type": "string", "responsivePriority": 3, "render": typeSwitcher },
				{ "data": {
					"display": ebvLinked,
					"_": ebvValue
				  }, "name": "ebv", "type": "non-empty-float", "defaultContent": "" },
				{ "data": {
					"display": photoLinked,
					"_": photoValue,
					"sort": photoSort
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 2, "width":"6%" },
				{ "data": {
					"display": spectraLinked,
					"_": spectraValue
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 2, "width":"5%" },
				{ "data": {
					"display": radioLinked,
					"_": "radiolink"
				  }, "type": "num", "defaultContent": "", "responsivePriority": 2, "width":"4%" },
				{ "data": {
					"display": xrayLinked,
					"_": "xraylink",
				  }, "type": "num", "defaultContent": "", "responsivePriority": 2 },
				{ "data": {
					"display": refLinked,
					"_": "references"
				  }, "type": "html", "searchable": false },
				{ "data": dataLinked, "responsivePriority": 4, "searchable": false },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 50,
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1
				}
			},
            select: true,
            lengthMenu: [ [10, 50, 250], [10, 50, 250] ],
            deferRender: true,
            autoWidth: false,
            buttons: [
                {
                    action: function ( e, dt, button, config ) {
                        table.rows( { filter: 'applied' } ).select();
                    },
                    text: 'Select all'
                },
                'selectNone',
                {
                    extend: 'colvis',
                    columns: ':not(:first-child):not(:last-child):not(:nth-last-child(2))'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':visible:not(:first-child):not(:last-child):not(:nth-last-child(2))',
						orthogonal: 'export'
                    }
				}
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
			}, {
                targets: invi,
				visible: false
			}, {
				targets: nwnm,
				className: 'nowrap not-mobile'
			}, {
				className: 'control',
				orderable: false,
				width: "2%",
				targets: -1
			}, {
				targets: [ 'download' ],
				orderable: false
			}, {
				targets: revo,
				orderSequence: [ 'desc', 'asc' ]
			}, {
				targets: nowr,
				className: 'nowrap'
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ ocol, "desc" ]]
		} );
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).on( 'input', function () {
				if (index == 2) return; //Ignore aliases column
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 ) &&
					( dateColInds.indexOf(index) === -1 ) &&
					( raDecColInds.indexOf(index) === -1 ) ) {
					if ( that.search() !== this.value ) {
						that.search( this.value )
					}
				}
				that.draw();
            } );
        } );
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				var alen = aData.length;
				for ( var i = 0; i < alen; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i] ) ) return false;
					} else if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i] ) ) return false;
					} else if ( dateColInds.indexOf(i) !== -1 ) {
						if ( !advancedDateFilter( aData[i], dateColValDict[i] ) ) return false;
					} else if ( raDecColInds.indexOf(i) !== -1 ) {
						if ( !advancedRaDecFilter( aData[i], raDecColValDict[i] ) ) return false;
					}
				}
				return true;
			}
		);
		table.on( 'search.dt', function () {
			searchFields = getSearchFields(allSearchCols);
			table.rows({page:'current'}).invalidate();
		} );
		searchFields = getSearchFields(allSearchCols);
	} );
	</script>
<?php
}

function duplicate_table() {
	global $modu;
	readfile("/root/astrocats/astrocats/" . $modu . "/html/table-templates/duplicates.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['distdeg', 'maxdiffyear', 'maxdiffyear'];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['name1', 'name2'];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = ['ra1', 'dec1', 'ra2', 'dec2'];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ ];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
		function distDegValue ( row, type, val, meta ) {
			if (!row.distdeg) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.distdeg)*3600.).toFixed(5));
		}
		function maxDiffYearValue ( row, type, val, meta ) {
			if (!row.maxdiffyear) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.maxdiffyear)*365.25).toFixed(3));
		}
		function discDiffYearValue ( row, type, val, meta ) {
			if (!row.discdiffyear) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.discdiffyear)*365.25).toFixed(3));
		}
		function performGoogleSearch ( row, type, val, meta ) {
			var namearr = row.aliases1.concat(row.aliases2);
			return "<button class='googleit' type='button' onclick='googleNames(\"" + namearr.join(',') + "\")'>Google all names</button>"
		}
		function markAsDuplicate ( row, type, val, meta ) {
			return "<button class='sameevent' type='button' onclick='markSame(\"" + row.name1 + "\",\"" + row.name2 + "\",\"" + row.edit + "\")'>These are the same</button>"
		}
		function markAsDistinct ( row, type, val, meta ) {
			return "<button class='diffevent' type='button' onclick='markDiff(\"" + row.name1 + "\",\"" + row.name2 + "\",\"" + row.edit + "\")'>These are different</button>"
		}
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (classname == 'aliases') {
				jQuery(this).remove();
			}
			if (['check', 'google', 'aredupes', 'notdupes', 'responsive'].indexOf(classname) >= 0) {
				jQuery(this).html( '' );
			}
			if (['check', 'google', 'aredupes', 'notdupes', 'responsive'].indexOf(classname) >= 0) return;
			var fslen = floatSearchCols.length;
			for (i = 0; i < fslen; i++) {
				if (jQuery(this).hasClass(floatSearchCols[i])) {
					floatColValDict[index] = floatSearchCols[i];
					floatColInds.push(index);
					break;
				}
			}
			var sslen = stringSearchCols.length;
			for (i = 0; i < sslen; i++) {
				if (jQuery(this).hasClass(stringSearchCols[i])) {
					stringColValDict[index] = stringSearchCols[i];
					stringColInds.push(index);
					break;
				}
			}
			var dslen = dateSearchCols.length;
			for (i = 0; i < dslen; i++) {
				if (jQuery(this).hasClass(dateSearchCols[i])) {
					dateColValDict[index] = dateSearchCols[i];
					dateColInds.push(index);
					break;
				}
			}
			var rdlen = raDecSearchCols.length;
			for (i = 0; i < rdlen; i++) {
				if (jQuery(this).hasClass(raDecSearchCols[i])) {
					raDecColValDict[index] = raDecSearchCols[i];
					raDecColInds.push(index);
					break;
				}
			}
            jQuery(this).html( '<input class="colsearch" type="search" id="'+classname+'" placeholder="'+title+'" />' );
        } );
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../astrocats/astrocats/' + modu + '/output/dupes.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": null, "type": "string", "responsivePriority": 1, "render": nameSwitcherName1 },
				{ "data": null, "type": "string", "responsivePriority": 1, "render": nameSwitcherName2 },
				{ "data": {
					"_": "ra1"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"_": "dec1"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"_": "ra2"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"_": "dec2"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"_": distDegValue,
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 5, "width": "5%" },
				{ "data": {
					"_": maxDiffYearValue,
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 5, "width": "5%" },
				{ "data": {
					"_": discDiffYearValue,
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 5, "width": "5%" },
				{ "data": performGoogleSearch, "responsivePriority": 4, "searchable": false },
				{ "data": markAsDuplicate, "responsivePriority": 4, "searchable": false },
				{ "data": markAsDistinct, "responsivePriority": 4, "searchable": false },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 50,
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1
				}
			},
            select: true,
            lengthMenu: [ [10, 50, 250], [10, 50, 250] ],
            deferRender: true,
            autoWidth: false,
            buttons: [
                {
                    action: function ( e, dt, button, config ) {
                        table.rows( { filter: 'applied' } ).select();
                    },
                    text: 'Select all'
                },
                'selectNone',
                {
                    extend: 'colvis',
                    columns: ':not(:first-child):not(:last-child):not(:nth-last-child(2)):not(:nth-last-child(3)):not(:nth-last-child(4))'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':visible:not(:first-child):not(:last-child):not(:nth-last-child(2)):not(:nth-last-child(3)):not(:nth-last-child(4))',
						orthogonal: 'export'
                    }
				}
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
			}, {
                targets: [ 'ra1', 'dec1', 'ra2', 'dec2' ],
				visible: false
			}, {
				className: 'control',
				orderable: false,
				width: "2%",
				targets: -1
			}, {
				targets: [ 'google', 'aredupes', 'notdupes' ],
				orderable: false
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 7, "asc" ], [8, "asc"], [9, "asc"]]
		} );
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).on( 'input', function () {
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 ) &&
					( dateColInds.indexOf(index) === -1 ) &&
					( raDecColInds.indexOf(index) === -1 ) ) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				var alen = aData.length;
				for ( var i = 0; i < alen; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i] ) ) return false;
					} else if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i] ) ) return false;
					} else if ( dateColInds.indexOf(i) !== -1 ) {
						if ( !advancedDateFilter( aData[i], dateColValDict[i] ) ) return false;
					} else if ( raDecColInds.indexOf(i) !== -1 ) {
						if ( !advancedRaDecFilter( aData[i], raDecColValDict[i] ) ) return false;
					}
				}
				return true;
			}
		);
		table.on( 'search.dt', function () {
			searchFields = getSearchFields(allSearchCols);
			table.rows({page:'current'}).invalidate();
		} );
		searchFields = getSearchFields(allSearchCols);
	} );
	</script>
<?php
}

function bibliography() {
	global $modu;
	readfile("/root/astrocats/astrocats/" . $modu . "/html/table-templates/biblio.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['photocount', 'spectracount', 'metacount'];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['bibcode', 'events', 'types'];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = [ ];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ ];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (['check', 'responsive'].indexOf(classname) >= 0) {
				jQuery(this).html( '' );
			}
			if (['check', 'responsive'].indexOf(classname) >= 0) return;
			var fslen = floatSearchCols.length;
			for (i = 0; i < fslen; i++) {
				if (jQuery(this).hasClass(floatSearchCols[i])) {
					floatColValDict[index] = floatSearchCols[i];
					floatColInds.push(index);
					break;
				}
			}
			var sslen = stringSearchCols.length;
			for (i = 0; i < sslen; i++) {
				if (jQuery(this).hasClass(stringSearchCols[i])) {
					stringColValDict[index] = stringSearchCols[i];
					stringColInds.push(index);
					break;
				}
			}
			var dslen = dateSearchCols.length;
			for (i = 0; i < dslen; i++) {
				if (jQuery(this).hasClass(dateSearchCols[i])) {
					dateColValDict[index] = dateSearchCols[i];
					dateColInds.push(index);
					break;
				}
			}
			var rdlen = raDecSearchCols.length;
			for (i = 0; i < rdlen; i++) {
				if (jQuery(this).hasClass(raDecSearchCols[i])) {
					raDecColValDict[index] = raDecSearchCols[i];
					raDecColInds.push(index);
					break;
				}
			}
            jQuery(this).html( '<input class="colsearch" type="search" id="'+classname+'" placeholder="'+title+'" />' );
        } );
		function bibcodeLinked ( row, type, val, meta ) {
			var html = '';
			if (row.authors) {
				html += row.authors + '<br>';
			}
			return html + "<a href='http://adsabs.harvard.edu/abs/" + row.bibcode + "'>" + row.bibcode + "</a>";
		}
		function eventsDropdown ( row, type, val, meta ) {
			var elen = row.events.length;
			var html = String(elen) + ' SNe: ';
			if (elen == 1) {
				html += "<a href='" + urlstem + row.events[0] + "/' target='_blank'>" + row.events[0] + "</a>";
			} else if (elen <= 25) {
				for (i = 0; i < elen; i++) {
					if (i != 0) html += ', ';
					html += "<a href='" + urlstem + row.events[i] + "/' target='_blank'>" + row.events[i] + "</a>";
				}
				html += '</select>';
				return html;
			} else {
				html += ('<br><select id="' + row.bibcode.replace(/\./g, '_') +
					'" size="3>"');
				for (i = 0; i < elen; i++) {
					html += '<option value="' + row.events[i] + '">' + row.events[i] + '</option>';
				}
				html += '</select><br><a class="dt-button" ';
				html += 'onclick="goToEvent(\'' + row.bibcode.replace(/\./g, '_') + '\');"><span>Go to selected SN</span></a>';
				return html;
			} 
			return html;
		}
		function allAuthors ( row, type, val, meta ) {
			var html = '';
			if (!row.allauthors) return '';
			var alen = row.allauthors.length;
			for (i = 0; i < alen; i++) {
				if (i > 0) html += ', ';
				html += row.allauthors[i];
			}
			return html;
		}
		function eventsDropdownType ( row, type, val, meta ) {
			if (type == "sort") {
				return "num";
			}
			return "string";
		}
		function eventsCount ( row, type, val, meta ) {
			return row.events.length;
		}
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../astrocats/astrocats/' + modu + '/output/biblio.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": {
					"display": bibcodeLinked,
					"_": "bibcode"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "data": {
					//"display": allAuthors,
					"_": "allauthors[; ]"
				  }, "type": "string", "defaultContent": "" },
				{ "data": {
					"display": eventsDropdown,
					"sort": eventsCount,
					"_": "events[, ]"
				  }, "type": eventsDropdownType, "defaultContent": "", "responsivePriority": 2 },
				{ "data": {
					"_": "types[, ]"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 2 },
				{ "data": {
					"_": "photocount"
				  }, "type": "num", "defaultContent": "", "responsivePriority": 2 },
				{ "data": {
					"_": "spectracount"
				  }, "type": "num", "defaultContent": "", "responsivePriority": 2 },
				{ "data": {
					"_": "metacount"
				  }, "type": "num", "defaultContent": "", "responsivePriority": 2 },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 50,
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1
				}
			},
            select: true,
            lengthMenu: [ [10, 50, 250], [10, 50, 250] ],
            deferRender: true,
            autoWidth: false,
            buttons: [
                {
                    action: function ( e, dt, button, config ) {
                        table.rows( { filter: 'applied' } ).select();
                    },
                    text: 'Select all'
                },
                'selectNone',
                {
                    extend: 'colvis',
                    columns: ':not(:first-child):not(:last-child)'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':visible:not(:first-child):not(:last-child)',
						orthogonal: 'export'
                    }
				}
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
			}, {
                targets: [ 'allauthors' ],
				visible: false
			}, {
				targets: [ 'events', 'photocount', 'spectracount', 'metacount' ],
				orderSequence: [ 'desc', 'asc' ]
			}, {
				className: 'control',
				orderable: false,
				width: "2%",
				targets: -1
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 6, "desc" ]]
		} );
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).on( 'input', function () {
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 ) &&
					( dateColInds.indexOf(index) === -1 ) &&
					( raDecColInds.indexOf(index) === -1 ) ) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				var alen = aData.length;
				for ( var i = 0; i < alen; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i] ) ) return false;
					} else if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i] ) ) return false;
					} else if ( dateColInds.indexOf(i) !== -1 ) {
						if ( !advancedDateFilter( aData[i], dateColValDict[i] ) ) return false;
					} else if ( raDecColInds.indexOf(i) !== -1 ) {
						if ( !advancedRaDecFilter( aData[i], raDecColValDict[i] ) ) return false;
					}
				}
				return true;
			}
		);
	} );
	</script>
<?php
}

function sentinel() {
	global $modu;
	readfile("/root/astrocats/astrocats/" . $modu . "/html/table-templates/sentinel.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = [];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['bibcode', 'events'];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = [ ];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ ];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (['check', 'responsive'].indexOf(classname) >= 0) {
				jQuery(this).html( '' );
			}
			if (['check', 'responsive'].indexOf(classname) >= 0) return;
			var fslen = floatSearchCols.length;
			for (i = 0; i < fslen; i++) {
				if (jQuery(this).hasClass(floatSearchCols[i])) {
					floatColValDict[index] = floatSearchCols[i];
					floatColInds.push(index);
					break;
				}
			}
			var sslen = stringSearchCols.length;
			for (i = 0; i < sslen; i++) {
				if (jQuery(this).hasClass(stringSearchCols[i])) {
					stringColValDict[index] = stringSearchCols[i];
					stringColInds.push(index);
					break;
				}
			}
			var dslen = dateSearchCols.length;
			for (i = 0; i < dslen; i++) {
				if (jQuery(this).hasClass(dateSearchCols[i])) {
					dateColValDict[index] = dateSearchCols[i];
					dateColInds.push(index);
					break;
				}
			}
			var rdlen = raDecSearchCols.length;
			for (i = 0; i < rdlen; i++) {
				if (jQuery(this).hasClass(raDecSearchCols[i])) {
					raDecColValDict[index] = raDecSearchCols[i];
					raDecColInds.push(index);
					break;
				}
			}
            jQuery(this).html( '<input class="colsearch" type="search" id="'+classname+'" placeholder="'+title+'" />' );
        } );
		function bibcodeLinked ( row, type, val, meta ) {
			var html = '';
			if (row.authors) {
				html += row.authors + '<br>';
			}
			return html + "<a href='http://adsabs.harvard.edu/abs/" + row.bibcode + "'>" + row.bibcode + "</a>";
		}
		function eventsDropdown ( row, type, val, meta ) {
			var elen = row.events.length;
			var html = String(elen) + ' SNe: ';
			if (elen == 1) {
				html += "<a href='" + urlstem + row.events[0] + "/' target='_blank'>" + row.events[0] + "</a>";
			} else if (elen <= 25) {
				for (i = 0; i < elen; i++) {
					if (i != 0) html += ', ';
					html += "<a href='" + urlstem + row.events[i] + "/' target='_blank'>" + row.events[i] + "</a>";
				}
				html += '</select>';
				return html;
			} else {
				html += ('<br><select id="' + row.bibcode.replace(/\./g, '_') +
					'" size="3>"');
				for (i = 0; i < elen; i++) {
					html += '<option value="' + row.events[i] + '">' + row.events[i] + '</option>';
				}
				html += '</select><br><a class="dt-button" ';
				html += 'onclick="goToEvent(\'' + row.bibcode.replace(/\./g, '_') + '\');"><span>Go to selected SN</span></a>';
				return html;
			} 
			return html;
		}
		function allAuthors ( row, type, val, meta ) {
			var html = '';
			if (!row.allauthors) return '';
			var alen = row.allauthors.length;
			for (i = 0; i < alen; i++) {
				if (i > 0) html += ', ';
				html += row.allauthors[i];
			}
			return html;
		}
		function eventsDropdownType ( row, type, val, meta ) {
			if (type == "sort") {
				return "num";
			}
			return "string";
		}
		function eventsCount ( row, type, val, meta ) {
			return row.events.length;
		}
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../astrocats/astrocats/' + modu + '/output/sentinel.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": {
					"display": bibcodeLinked,
					"_": "bibcode"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "data": {
					"name": "firstauthor",
					"_": "allauthors.0"
				  }, "type": "string", "defaultContent": "" },
				{ "data": {
					"_": "allauthors[; ]"
				  }, "type": "string", "width": "40%", "defaultContent": "" },
				{ "data": {
					"display": eventsDropdown,
					"sort": eventsCount,
					"_": "events[, ]"
				  }, "type": eventsDropdownType, "defaultContent": "", "responsivePriority": 2 },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 50,
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1
				}
			},
            select: true,
            lengthMenu: [ [10, 50, 250], [10, 50, 250] ],
            deferRender: true,
            autoWidth: false,
            buttons: [
                {
                    action: function ( e, dt, button, config ) {
                        table.rows( { filter: 'applied' } ).select();
                    },
                    text: 'Select all'
                },
                'selectNone',
                {
                    extend: 'colvis',
                    columns: ':not(:first-child):not(:last-child)'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':visible:not(:first-child):not(:last-child)',
						orthogonal: 'export'
                    }
				}
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
			}, {
                targets: [ ],
				visible: false
			}, {
				targets: [ 'events' ],
				orderSequence: [ 'desc', 'asc' ]
			}, {
				className: 'control',
				orderable: false,
				width: "2%",
				targets: -1
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 4, "desc" ]]
		} );
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).on( 'input', function () {
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 ) &&
					( dateColInds.indexOf(index) === -1 ) &&
					( raDecColInds.indexOf(index) === -1 ) ) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				var alen = aData.length;
				for ( var i = 0; i < alen; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i] ) ) return false;
					} else if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i] ) ) return false;
					} else if ( dateColInds.indexOf(i) !== -1 ) {
						if ( !advancedDateFilter( aData[i], dateColValDict[i] ) ) return false;
					} else if ( raDecColInds.indexOf(i) !== -1 ) {
						if ( !advancedRaDecFilter( aData[i], raDecColValDict[i] ) ) return false;
					}
				}
				return true;
			}
		);
	} );
	</script>
<?php
}

function hosts() {
	global $modu;
	readfile("/root/astrocats/astrocats/" . $modu . "/html/table-templates/hosts.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['photocount', 'spectracount', 'redshift', 'lumdist', 'rate'];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['name', 'events', 'types'];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = ['hostra', 'hostdec'];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ ];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (['check', 'responsive'].indexOf(classname) >= 0) {
				jQuery(this).html( '' );
			}
			if (['check', 'responsive'].indexOf(classname) >= 0) return;
			for (i = 0; i < floatSearchCols.length; i++) {
				if (jQuery(this).hasClass(floatSearchCols[i])) {
					floatColValDict[index] = floatSearchCols[i];
					floatColInds.push(index);
					break;
				}
			}
			for (i = 0; i < stringSearchCols.length; i++) {
				if (jQuery(this).hasClass(stringSearchCols[i])) {
					stringColValDict[index] = stringSearchCols[i];
					stringColInds.push(index);
					break;
				}
			}
			for (i = 0; i < dateSearchCols.length; i++) {
				if (jQuery(this).hasClass(dateSearchCols[i])) {
					dateColValDict[index] = dateSearchCols[i];
					dateColInds.push(index);
					break;
				}
			}
			for (i = 0; i < raDecSearchCols.length; i++) {
				if (jQuery(this).hasClass(raDecSearchCols[i])) {
					raDecColValDict[index] = raDecSearchCols[i];
					raDecColInds.push(index);
					break;
				}
			}
            jQuery(this).html( '<input class="colsearch" type="search" id="'+classname+'" placeholder="'+title+'" />' );
        } );
		function hostraValue ( row, type, val, meta ) {
			if (!row.hostra) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.hostra;
			return raToDegrees(data);
		}
		function hostdecValue ( row, type, val, meta ) {
			if (!row.hostdec) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.hostdec;
			return decToDegrees(data);
		}
		function hostraLinked ( row, type, val, meta ) {
			if (!row.hostra) return '';
			var data = row.hostra;
			var degrees = raToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function hostdecLinked ( row, type, val, meta ) {
			if (!row.hostdec) return '';
			var data = row.hostdec;
			var degrees = decToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function redshiftValue ( row, type, val, meta ) {
			if (!row.redshift) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat(row.redshift.replace('*',''));
		}
		function lumdistValue ( row, type, val, meta ) {
			if (!row.lumdist) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat(row.lumdist.replace('*',''));
		}
		function rateValue ( row, type, val, meta ) {
			if (!row.rate) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat(row.rate.split(',')[0]);
		}
		function rateDisplay ( row, type, val, meta ) {
			if (!row.rate) return '';
			return row.rate.split(',')[0] + ' ± ' + row.rate.split(',')[1];
		}
		function hostUnlinked ( row, type, val, meta ) {
			if (!row.host) return '';
			var host = "<a class='" + (row.kind == 'cluster' ? "hci" : "hhi") + "' href='http://simbad.u-strasbg.fr/simbad/sim-basic?Ident=" +
				"%s&submit=SIMBAD+search' target='_blank'></a> "; 
			var mainHost = "<a href='http://simbad.u-strasbg.fr/simbad/sim-basic?Ident=%s&submit=SIMBAD+search' target='_blank'>%s</a>"; 
			var text;
			if (row.host.length > 1) {
				var hostAliases = '';
				var primaryname = row.host[0];
				for (var i = 1; i < row.host.length; i++) {
					// Temporary until host object retains kind.
					if (row.host[i].toUpperCase().indexOf("ABELL") !== -1) primaryname = row.host[i];
					if (i != 1) hostAliases += ', ';
					hostAliases += noBreak(row.host[i]);
				}
				text = ("<div class='tooltip'>" + host + mainHost + "<span class='tooltiptext'> " +
					hostAliases + "</span></div>").replace(/%s/g, noBreak(primaryname));
			} else {
				text = (host + mainHost).replace(/%s/g, noBreak(row.host[0]));
			}
			var minwidth = 12;
			var totalwidth = 200;
			var padding = 1;
			var imgwidth = Math.max(Math.round(80.0/Math.sqrt(1.0*row.events.length)), minwidth);
			var mod = Math.max(Math.round(1.0*totalwidth/(1.0*(imgwidth + padding))), 1);
			text = text + "<div style='padding-top:5px; line-height:" + (10 /*imgwidth + padding - 2*/) + "px;'>";
			var cnt = 0;
			for (var i = 0; i < row.events.length; i++) {
				if (!row.events[i].img) continue;
				cnt++;
				text = (text + "<a href='" + urlstem + nameToFilename(row.events[i].name) + "/' target='_blank'>" +
					"<img class='hostimg' width='" + imgwidth + "' height='" + imgwidth + "' src='" + urlstem +
					nameToFilename(row.events[i].name) + "-host.jpg' style='margin-right:" +
					padding + "px;'></a>");
			}
			text = text + "</div>";
			return text;
		}
		function eventsDropdown ( row, type, val, meta ) {
			var html = String(row.events.length) + ' SNe: ';
			if (row.events.length == 1) {
				html += "<a href='" + urlstem + row.events[0].name + "/' target='_blank'>" + row.events[0].name + "</a>";
			} else if (row.events.length <= 20) {
				for (i = 0; i < row.events.length; i++) {
					if (i != 0) html += ', ';
					html += "<a href='" + urlstem + row.events[i].name + "/' target='_blank'>" + row.events[i].name + "</a>";
				}
				html += '</select>';
				return html;
			} else {
				html += ('<br><select id="' + row.host[0].replace(/\./g, '_') +
					'" size="' + Math.max(3, Math.ceil(row.events.length/20)) + '">');
				for (i = 0; i < row.events.length; i++) {
					html += '<option value="' + row.events[i].name + '">' + row.events[i].name + '</option>';
				}
				html += '</select><br><a class="dt-button" ';
				html += 'onclick="goToEvent(\'' + row.host[0].replace(/\./g, '_') + '\');"><span>Go to selected SN</span></a>';
				return html;
			} 
			return html;
		}
		function eventsDropdownType ( row, type, val, meta ) {
			if (type == "sort") {
				return "num";
			}
			return "string";
		}
		function eventsCount ( row, type, val, meta ) {
			return row.events.length;
		}
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../astrocats/astrocats/' + modu + '/output/hosts.min.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": {
					"display": hostUnlinked,
					"_": "host[, ]"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1, "width":"200px" },
				{ "data": {
					"display": eventsDropdown,
					"sort": eventsCount,
					"_": "events[, ]"
				  }, "type": eventsDropdownType, "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"display": rateDisplay,
					"filter": rateValue,
					"sort": rateValue,
					"_": "rate",
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 5 },
				{ "data": {
					"_": "hostra",
					"display": hostraLinked,
					"filter": hostraValue,
					"sort": hostraValue
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 4 },
				{ "data": {
					"_": "hostdec",
					"display": hostdecLinked,
					"filter": hostdecValue,
					"sort": hostdecValue
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 4 },
				{ "data": {
					"_": "redshift",
					"filter": redshiftValue,
					"sort": redshiftValue
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 4 },
				{ "data": {
					"_": "lumdist",
					"filter": lumdistValue,
					"sort": lumdistValue
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 4 },
				{ "data": {
					"_": "types[, ]"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 3 },
				{ "data": {
					"_": "photocount"
				  }, "type": "num", "defaultContent": "", "responsivePriority": 5 },
				{ "data": {
					"_": "spectracount"
				  }, "type": "num", "defaultContent": "", "responsivePriority": 5 },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 20,
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1
				}
			},
            select: true,
            lengthMenu: [ [10, 20, 50], [10, 20, 50] ],
            deferRender: true,
            autoWidth: false,
            buttons: [
                {
                    action: function ( e, dt, button, config ) {
                        table.rows( { filter: 'applied' } ).select();
                    },
                    text: 'Select all'
                },
                'selectNone',
                {
                    extend: 'colvis',
                    columns: ':not(:first-child):not(:last-child)'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':visible:not(:first-child):not(:last-child)',
						orthogonal: 'export'
                    }
				}
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
			}, {
				targets: [ 'events', 'photocount', 'spectracount', 'rate' ],
				orderSequence: [ 'desc', 'asc' ]
			}, {
                targets: [ 'lumdist', 'hostra', 'hostdec' ],
				visible: false
			}, {
				className: 'control',
				orderable: false,
				width: "2%",
				targets: -1
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 2, "desc" ]]
		} );
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).on( 'input', function () {
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 ) &&
					( dateColInds.indexOf(index) === -1 ) &&
					( raDecColInds.indexOf(index) === -1 ) ) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				for ( var i = 0; i < aData.length; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i] ) ) return false;
					} else if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i] ) ) return false;
					} else if ( dateColInds.indexOf(i) !== -1 ) {
						if ( !advancedDateFilter( aData[i], dateColValDict[i] ) ) return false;
					} else if ( raDecColInds.indexOf(i) !== -1 ) {
						if ( !advancedRaDecFilter( aData[i], raDecColValDict[i] ) ) return false;
					}
				}
				return true;
			}
		);
	} );
	</script>
<?php
}

function conflict_table() {
	global $modu;
	readfile("/root/astrocats/astrocats/" . $modu . "/html/table-templates/conflicts.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['difference'];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['name', 'quantity'];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = [];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
		function actionButtons ( row, type, val, meta ) {
			var html = '';
			var quantityStr = '';
			for (i = 0; i < row.values.length; i++) {
				if (i > 0) html += ' ';
				if (row.quantity === 'ra') {
					quantityStr = 'R.A.';
				} else if (row.quantity === 'dec') {
					quantityStr = 'Dec.';
				} else if (row.quantity === 'redshift') {
					quantityStr = '<i>z</i>';
				}
				html += "<div class='tooltip'><button class='markerror' type='button' onclick='markError(\"" +
					row.name + "\", \"" + row.quantity + "\", \"" + row.sources[i].idtype +
					"\", \"" + row.sources[i].id + "\", \"" + row.edit + "\")'>" + quantityStr + ((quantityStr !== "") ? " =<br>" : "") +
					String(row.values[i]) + "<br>is erroneous</button><span class='tooltiptextbot'>" + row.sources[i].id + "</span></div>";
			}
			var aliases = getAliases(row);
			for (i = 1; i < aliases.length; i++) {
				html += ' ';
				html += "<button class='diffevent' type='button' onclick='markDiff(\"" +
					row.name + "\", \"" + aliases[i] + "\", \"" + row.edit + "\")'>Alias<br>" +
					aliases[i] + "<br>is a different SN</button>";
			}
			return html;
		}
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (['check', 'actions', 'responsive'].indexOf(classname) >= 0) {
				jQuery(this).html( '' );
			}
			if (['check', 'actions', 'responsive'].indexOf(classname) >= 0) return;
			for (i = 0; i < floatSearchCols.length; i++) {
				if (jQuery(this).hasClass(floatSearchCols[i])) {
					floatColValDict[index] = floatSearchCols[i];
					floatColInds.push(index);
					break;
				}
			}
			for (i = 0; i < stringSearchCols.length; i++) {
				if (jQuery(this).hasClass(stringSearchCols[i])) {
					stringColValDict[index] = stringSearchCols[i];
					stringColInds.push(index);
					break;
				}
			}
			for (i = 0; i < dateSearchCols.length; i++) {
				if (jQuery(this).hasClass(dateSearchCols[i])) {
					dateColValDict[index] = dateSearchCols[i];
					dateColInds.push(index);
					break;
				}
			}
			for (i = 0; i < raDecSearchCols.length; i++) {
				if (jQuery(this).hasClass(raDecSearchCols[i])) {
					raDecColValDict[index] = raDecSearchCols[i];
					raDecColInds.push(index);
					break;
				}
			}
            jQuery(this).html( '<input class="colsearch" type="search" id="'+classname+'" placeholder="'+title+'" />' );
        } );
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../astrocats/astrocats/' + modu + '/output/conflicts.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": {
					"display": nameLinkedName,
					"filter": eventAliases,
					"_": "name"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "data": {
					"_": "quantity"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "data": {
					"_": "difference"
				  }, "type": "num", "defaultContent": "", "responsivePriority": 5 },
				{ "data": actionButtons, "responsivePriority": 4, "searchable": false },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 50,
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1
				}
			},
            select: true,
            lengthMenu: [ [10, 50, 250], [10, 50, 250] ],
            deferRender: true,
            autoWidth: false,
            buttons: [
                {
                    action: function ( e, dt, button, config ) {
                        table.rows( { filter: 'applied' } ).select();
                    },
                    text: 'Select all'
                },
                'selectNone',
                {
                    extend: 'colvis',
                    columns: ':not(:first-child):not(:last-child):not(:nth-last-child(2))'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':visible:not(:first-child):not(:last-child):not(:nth-last-child(2))',
						orthogonal: 'export'
                    }
				}
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
			}, {
				className: 'control',
				orderable: false,
				width: "2%",
				targets: -1
			}, {
				targets: [ 'actions' ],
				orderable: false
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 3, "desc" ]]
		} );
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).on( 'input', function () {
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 ) &&
					( dateColInds.indexOf(index) === -1 ) &&
					( raDecColInds.indexOf(index) === -1 ) ) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				for ( var i = 0; i < aData.length; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i] ) ) return false;
					} else if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i] ) ) return false;
					} else if ( dateColInds.indexOf(i) !== -1 ) {
						if ( !advancedDateFilter( aData[i], dateColValDict[i] ) ) return false;
					} else if ( raDecColInds.indexOf(i) !== -1 ) {
						if ( !advancedRaDecFilter( aData[i], raDecColValDict[i] ) ) return false;
					}
				}
				return true;
			}
		);
	} );
	</script>
<?php
}

function errata() {
	global $modu;
	readfile("/root/astrocats/astrocats/" . $modu . "/html/table-templates/errata.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = [];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = [];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = [ ];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ ];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (['check', 'responsive'].indexOf(classname) >= 0) {
				jQuery(this).html( '' );
			}
			if (['check', 'responsive'].indexOf(classname) >= 0) return;
			for (i = 0; i < floatSearchCols.length; i++) {
				if (jQuery(this).hasClass(floatSearchCols[i])) {
					floatColValDict[index] = floatSearchCols[i];
					floatColInds.push(index);
					break;
				}
			}
			for (i = 0; i < stringSearchCols.length; i++) {
				if (jQuery(this).hasClass(stringSearchCols[i])) {
					stringColValDict[index] = stringSearchCols[i];
					stringColInds.push(index);
					break;
				}
			}
			for (i = 0; i < dateSearchCols.length; i++) {
				if (jQuery(this).hasClass(dateSearchCols[i])) {
					dateColValDict[index] = dateSearchCols[i];
					dateColInds.push(index);
					break;
				}
			}
			for (i = 0; i < raDecSearchCols.length; i++) {
				if (jQuery(this).hasClass(raDecSearchCols[i])) {
					raDecColValDict[index] = raDecSearchCols[i];
					raDecColInds.push(index);
					break;
				}
			}
            jQuery(this).html( '<input class="colsearch" type="search" id="'+classname+'" placeholder="'+title+'" />' );
        } );
		function bibcodeLinked ( row, type, val, meta ) {
			var html = '';
			if (row.authors) {
				html += row.authors + '<br>';
			}
			return html + "<a href='http://adsabs.harvard.edu/abs/" + row.bibcode + "'>" + row.bibcode + "</a>";
		}
		function eventsDropdown ( row, type, val, meta ) {
			var html = String(row.events.length) + ' SNe: ';
			if (row.events.length == 1) {
				html += "<a href='" + urlstem + row.events[0] + "/' target='_blank'>" + row.events[0] + "</a>";
			} else if (row.events.length <= 30) {
				for (i = 0; i < row.events.length; i++) {
					if (i != 0) html += ', ';
					html += "<a href='" + urlstem + row.events[i] + "/' target='_blank'>" + row.events[i] + "</a>";
				}
				html += '</select>';
				return html;
			} else {
				html += ('<br><select id="' + row.bibcode.replace(/\./g, '_') +
					'" size="3">');
				for (i = 0; i < row.events.length; i++) {
					html += '<option value="' + row.events[i] + '">' + row.events[i] + '</option>';
				}
				html += '</select><br><a class="dt-button" ';
				html += 'onclick="goToEvent(\'' + row.bibcode.replace(/\./g, '_') + '\');"><span>Go to selected SN</span></a>';
				return html;
			} 
			return html;
		}
		function allAuthors ( row, type, val, meta ) {
			var html = '';
			if (!row.allauthors) return '';
			for (i = 0; i < row.allauthors.length; i++) {
				if (i > 0) html += ', ';
				html += row.allauthors[i];
			}
			return html;
		}
		function eventsDropdownType ( row, type, val, meta ) {
			if (type == "sort") {
				return "num";
			}
			return "string";
		}
		function eventsCount ( row, type, val, meta ) {
			return row.events.length;
		}
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../astrocats/astrocats/' + modu + '/output/errata.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": {
					"display": nameLinkedName,
					"filter": eventAliases,
					"_": "name"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "data": {
					"_": "ident"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "data": {
					"_": "kind"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "data": {
					"_": "quantity"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "data": {
					"_": "likelyvalue"
				  }, "type": "string", "defaultContent": "", "responsivePriority": 1 },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 50,
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1
				}
			},
            select: true,
            lengthMenu: [ [10, 50, 250], [10, 50, 250] ],
            deferRender: true,
            autoWidth: false,
            buttons: [
                {
                    action: function ( e, dt, button, config ) {
                        table.rows( { filter: 'applied' } ).select();
                    },
                    text: 'Select all'
                },
                'selectNone',
                {
                    extend: 'colvis',
                    columns: ':not(:first-child):not(:last-child)'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':visible:not(:first-child):not(:last-child)',
						orthogonal: 'export'
                    }
				}
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
			}, {
				className: 'control',
				orderable: false,
				width: "2%",
				targets: -1
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 1, "desc" ]]
		} );
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).on( 'input', function () {
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 ) &&
					( dateColInds.indexOf(index) === -1 ) &&
					( raDecColInds.indexOf(index) === -1 ) ) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				for ( var i = 0; i < aData.length; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i] ) ) return false;
					} else if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i] ) ) return false;
					}
				}
				return true;
			}
		);
	} );
	</script>
<?php
}

function transient_table_scripts() {
	global $stem, $modu, $subd;
	if (is_front_page() || is_page(array('find-duplicates', 'bibliography', 'sentinel', 'find-conflicts', 'errata', 'host-galaxies', 'supernova-graveyard')) || is_search()) {
		wp_enqueue_style( 'transient-table', plugins_url( 'transient-table.css', __FILE__), array() );
		wp_enqueue_style( 'transient-table.' . $stem, plugins_url( 'transient-table.' . $stem . '.css', __FILE__), array('transient-table') );
		wp_enqueue_style( 'datatables-css', plugins_url( "datatables.min.css", __FILE__), array('transient-table') );
		wp_enqueue_script( 'datatables-js', plugins_url( "datatables.min.js", __FILE__), array('jquery') );
		wp_enqueue_script( 'transient-table-js', plugins_url( "transient-table.js", __FILE__), array() );
		#wp_enqueue_script( 'datatables-js', "//cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.js", array('jquery') );
		#wp_enqueue_style( 'datatables-css', "https://cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.css", array('transient-table') );
		#wp_enqueue_script( 'datatables-js', "https://nightly.datatables.net/js/jquery.dataTables.min.js", array('jquery') );
		#wp_enqueue_style( 'datatables-css', "https://nightly.datatables.net/css/jquery.dataTables.min.css", array('transient-table') );
		#wp_enqueue_script( 'datatables-buttons-js', "https://nightly.datatables.net/buttons/js/dataTables.buttons.min.js", array('datatables-js') );
		#wp_enqueue_style( 'datatables-buttons-css', "https://nightly.datatables.net/buttons/css/buttons.dataTables.min.css", array('datatables-css') );
		#wp_enqueue_script( 'datatables-colvis-js', "https://nightly.datatables.net/buttons/js/buttons.colVis.min.js", array('datatables-js') );
		#wp_enqueue_script( 'datatables-html5-js', "https://nightly.datatables.net/buttons/js/buttons.html5.min.js", array('datatables-js') );
		#wp_enqueue_script( 'datatables-responsive-js', "https://nightly.datatables.net/responsive/js/dataTables.responsive.min.js", array('datatables-js') );
		#wp_enqueue_style( 'datatables-responsive-css', "https://nightly.datatables.net/responsive/css/responsive.dataTables.min.css", array('datatables-css') );
		#wp_enqueue_script( 'datatables-select-js', "https://nightly.datatables.net/select/js/dataTables.select.min.js", array('datatables-js') );
		#wp_enqueue_style( 'datatables-select-css', "https://nightly.datatables.net/select/css/select.dataTables.min.css", array('datatables-css') );
	}
}

add_action( 'wp_enqueue_scripts', 'transient_table_scripts' );
?>
