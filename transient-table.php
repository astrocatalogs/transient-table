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
$plen = trim($tt[8]);
$shrt = trim($tt[9]);
$sing = trim($tt[10]);
$outp = 'astrocats/astrocats/' . $modu . '/output/';

function datatables_functions() {
	global $tt, $stem, $modu, $subd, $invi, $nowr, $nwnm, $revo, $ocol, $plen, $shrt, $sing, $lochtml, $outp;
?>
	<script>
	var searchFields;
	var stem = '<?php echo $stem;?>';
	var modu = '<?php echo $modu;?>';
	var subd = '<?php echo $subd;?>';
	var invi = [<?php echo $invi;?>];
	var visi = [<?php echo (array_key_exists('visible', $_GET) ?
		('"' . implode('","', explode(",", $_GET['visible'])) . '"') : '');?>];
	invi = jQuery(invi).not(visi).get();
	var nowr = [<?php echo $nowr;?>];
	var nwnm = [<?php echo $nwnm;?>];
	var revo = [<?php echo $revo;?>];
	var ocol = <?php echo $ocol;?>;
	var plen = [<?php echo $plen;?>];
	var shrt = '<?php echo $shrt;?>';
	var sing = '<?php echo $sing;?>';
	var outp = '<?php echo $outp;?>';
	var urlbase = 'https://' + subd + '.space/';
	var urlstem = urlbase + stem + '/'; 
	var nameColumn;
	var raColumn;
	var decColumn;
	var altColumn;
	var aziColumn;
	var amColumn;
	var sbColumn;
	var lst = 0.0;
	var longitude = 0.0;
	var latitude = 0.0;
	var moonAlt = 0.0;
	var moonAzi = 0.0;
	var sunAlt = 0.0;
	var sunAzi = 0.0;
	var moonPhaseAlpha = 0.0;
	var moonPhaseIcon = '';
	function updateLocation() {
		var sunmoontxt = document.getElementById("suninfo");
		var lat = document.getElementById("inplat");
		var lon = document.getElementById("inplon");
		latitude = parseFloat((lat.value === '') ? latitude : lat.value);
		longitude = parseFloat((lon.value === '') ? longitude : lon.value);
		if (lat.value === '' && latitude != 0.0) lat.value = latitude;
		if (lon.value === '' && longitude != 0.0) lon.value = longitude;
		var j2000 = new Date(Date.UTC(2000, 0, 1, 12));
		var nowon = document.getElementById("nowon");
		if ( nowon.value === "on" ) {
			var year = parseInt(document.getElementById("inpyear").value);
			var month = parseInt(document.getElementById("inpmon").value);
			var day = parseInt(document.getElementById("inpday").value);
			var time = document.getElementById("inptime").value.split(":");
			var hour = (time.length > 0 && time[0] !== '') ? parseInt(time[0]) : 0;
			var minute = (time.length > 1 && time[1] !== '') ? parseInt(time[1]) : 0;
			var second = (time.length > 2 && time[2] !== '') ? parseInt(time[2]) : 0;
			var testdate = new Date();
			var seldate = new Date(Date.UTC(year, month, day, hour, minute, second));
		} else {
			var seldate = new Date();
		}
		var ut = new Date(seldate.getTime());
		var j2000d = (ut.getTime() - j2000.getTime())/86400000.0;
		var dechours = ut.getUTCHours() + ut.getUTCMinutes()/60.0 + ut.getUTCSeconds()/3600.0;

		lst = (100.46 + 0.985647 * j2000d + longitude + 15.0*dechours) % 360.0;
		if ( lst < 0 ) lst += 360;

		var sunpos = SunCalc.getPosition(seldate, latitude, longitude);
		var moonpos = SunCalc.getMoonPosition(seldate, latitude, longitude);
		var moonill = SunCalc.getMoonIllumination(seldate);
		var moonphase = moonill.phase;
		moonPhaseAlpha = moonill.angle;
		var times = SunCalc.getTimes(seldate, latitude, longitude);
		var start = seldate;

		moonAlt = moonpos.altitude;
		// sunCalc uses SW convention, convert to NE astronomy convention.
		moonAzi = (moonpos.azimuth < Math.PI) ? Math.PI + moonpos.azimuth : moonpos.azimuth - Math.PI;

		sunAlt = sunpos.altitude;
		// sunCalc uses SW convention, convert to NE astronomy convention.
		sunAzi = (sunpos.azimuth < Math.PI) ? Math.PI + sunpos.azimuth : sunpos.azimuth - Math.PI;

		var timesofday = [
			[times.nightEnd.getTime(), ' 🌃 Nighttime'],
			[times.sunrise.getTime(), ' 🌄 Dawn twilight'],
			[times.sunset.getTime(), ' ☀️ Daytime'],
			[times.night.getTime(), ' 🌆 Dusk twilight'],
		];
		var sunriseStr = ' 🌃 Nighttime';
		for ( var i = timesofday.length - 1; i >= 0; i-- ) {
			if ( seldate.getTime() < timesofday[i][0] ) {
				continue;
			}
			if ( i < timesofday.length - 1) {
				sunriseStr = timesofday[i+1][1];
			}
			break;
		}
		moonPhaseIcon = '◌';
		moonPhaseDesc = 'No Moon';
		if (moonAlt > 0.0) {
			var moonphases = [
				[0.035, '🌑', 'New Moon'],
				[0.2, '🌒', 'Waxing crescent'],
				[0.3, '🌓', 'First quarter'],
				[0.465, '🌔', 'Waxing gibbous'],
				[0.535, '🌕', 'Full Moon'],
				[0.7, '🌖', 'Waning gibbous'],
				[0.8, '🌗', 'Last quarter'],
				[0.965, '🌘', 'Waning crescent']
			];
			var moonStr = '🌑 New Moon';
			for ( var i = moonphases.length - 1; i >= 0; i-- ) {
				if ( moonphase < moonphases[i][0] ) {
					continue;
				}
				if ( i < moonphases.length - 1) {
					moonPhaseIcon = moonphases[i+1][1];
					moonPhaseDesc = moonphases[i+1][2];
				}
				break;
			}
		}
		sunmoontxt.innerHTML = sunriseStr + ', ' + moonPhaseIcon + ' ' + moonPhaseDesc;
	}
	function angDist(lon1, lat1, lon2, lat2) {
		// All angles in rads
		var dlon = Math.abs(lon2 - lon1);
		var dlat = Math.abs(lat2 - lat1);
		// var a = Math.pow((Math.sin(0.5*dlat)), 2) + (Math.cos(lat1) * Math.cos(lat2) * Math.pow(Math.sin(0.5*dlon), 2));
		// var dist = 2.0 * Math.asin(Math.min(1.0, Math.sqrt(a)));
		var dist = Math.abs(Math.atan2(Math.pow(Math.pow(Math.cos(lat2)*Math.sin(dlon), 2) + Math.pow(Math.cos(lat1)*Math.sin(lat2) -
			Math.sin(lat1)*Math.cos(lat2)*Math.cos(dlon), 2), 0.5),
			Math.sin(lat1)*Math.sin(lat2)+Math.cos(lat1)*Math.cos(lat2)*Math.cos(dlon)))
		return dist;
	}
	function getAlt(ra, dec) {
		var ha = lst - ra;
		if ( ha < 0 ) ha += 360;
		var lat = latitude*Math.PI/180.0;
		ha *= Math.PI/180.0;
		dec *= Math.PI/180.0;
		return (180.0/Math.PI)*Math.asin(Math.sin(dec)*Math.sin(lat)+Math.cos(dec)*Math.cos(lat)*Math.cos(ha));
	}
	function getAzi(ra, dec) {
		var ha = lst - ra;
		var lat = latitude*Math.PI/180.0;
		ha *= Math.PI/180.0;
		dec *= Math.PI/180.0;
		var alt = Math.asin(Math.sin(dec)*Math.sin(lat)+Math.cos(dec)*Math.cos(lat)*Math.cos(ha));
		var azi = (180.0/Math.PI)*Math.acos((Math.sin(dec) - Math.sin(alt)*Math.sin(lat))/(Math.cos(alt)*Math.cos(lat)));
		if (Math.sin(ha) > 0.0) azi = 360 - azi;
		return azi;
	}
	function geoFindMe() {
		var lat = document.getElementById("inplat");
		var lon = document.getElementById("inplon");
		var message = document.getElementById("inpmessage");
		var locbutt = document.getElementById("locbutt");

		if (!navigator.geolocation){
			message.innerHTML = "Geolocation is not supported by your browser";
			return;
		}

		function success(position) {
			latitude  = position.coords.latitude;
			longitude = position.coords.longitude;

			lat.value = latitude;
			lon.value = longitude;

			jQuery('#inplon').trigger('change');

			message.innerHTML = "🌎  Use my location";
			locbutt.disabled = false;

			updateLocation();
		}

		function error() {
			message.innerHTML = "Unable to retrieve your location";
		}

		message.innerHTML = "<img style='vertical-align:-26%; padding-right:3px; width:16px' src='wp-content/plugins/transient-table/loading.gif'>Finding your location...";
		locbutt.disabled = true;

		navigator.geolocation.getCurrentPosition(success, error);
	}
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
	function getQueryParams(qs) {
		qs = qs.split("+").join(" ");
		var params = {},
			tokens,
			re = /[?&]?([^=]+)=([^&]*)/g;

		while (tokens = re.exec(qs)) {
			params[decodeURIComponent(tokens[1])]
				= decodeURIComponent(tokens[2]);
		}

		return params;
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
	function dateToMJD ( da ) {
		var mydate = new Date(da);
		return mydate.getJulian() - 2400000.5;
	}
	function getAliases (row, field) {
		if (field === undefined) field = 'alias';
		var aliases = [];
		if (!(field in row)) {
			return aliases;
		}
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
		if (!(field in row)) {
			return aliases;
		}
		for (i = 1; i < row[field].length; i++) {
			if (typeof row[field][i] === 'string') {
				aliases.push(row[field][i]);
			} else {
				aliases.push(row[field][i].value);
			}
		}
		return aliases;
	}
	function eventAliases ( row, type, full, meta, field ) {
		if (field === undefined) field = 'alias';
		if (!(field in row)) return '';
		if (!row[field]) return '';
		var aliases = getAliases(row, field);
		return aliases.join(', ');
	}
	function eventAliasesOnly ( row, type, full, meta, field ) {
		if (field === undefined) field = 'alias';
		if (!(field in row)) return '';
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
	function nameLinkedName ( row, type, full, meta ) {
		return nameLinked ( row, type, full, meta, 'name', 'alias' );
	}
	function nameLinkedName1 ( row, type, full, meta ) {
		return nameLinked ( row, type, full, meta, 'name1', 'aliases1' );
	}
	function nameLinkedName2 ( row, type, full, meta ) {
		return nameLinked ( row, type, full, meta, 'name2', 'aliases2' );
	}
	function nameLinked ( row, type, full, meta, namefield, aliasfield ) {
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
		var html = '';
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
							html = primaryname;
							break;
						}
						var otheraliases = [];
						for (var a = 0; a < alen; a++) {
							if (aliases[a].toUpperCase() === primaryname.toUpperCase()) {
								continue;
							}
							otheraliases.push(noBreak(aliases[a]));
						}
						html = "<div class='tooltip'><a href='" + urlstem + nameToFilename(row[namefield]) +
							"/' target='_blank'>" + primaryname + "</a><span class='tooltiptext'> " + otheraliases.join(', ') + "</span></div>";
						break;
					}
				}
			}
			if (html === '') {
				if (type === 'display') {
					html = nameLinked(row, null, null, null, namefield, aliasfield);
				} else html = row[namefield];
			}
		} else if (type === 'filter') {
			html = eventAliases(row, null, null, null, aliasfield);
		}
		if (html === '') html = row[namefield];
		return html;
	}
	function hostLinked ( row, type, full, meta ) {
		var host = "<a class='" + (('kind' in row.host[0] && row.host[0]['kind'] == 'cluster') ? "hci" : "hhi") +
			"' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a>&nbsp;";
		var mainHost = "<a href='http://simbad.u-strasbg.fr/simbad/sim-basic?Ident=" + 
			encodeURIComponent(row.host[0]['value']) +
			"&submit=SIMBAD+search' target='_blank'>" + someBreak(row.host[0]['value']) + "</a>"; 
		var hostImg = (row.ra && row.dec) ? ("<div class='tooltipimg' " +
			"style='background-image:url(" + urlbase + outp + 'html/' + nameToFilename(row.name) + "-host.jpg);'></div>") : "";
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
					"style=background-image:url(" + urlbase + outp + 'html/' + nameToFilename(row.name) + "-host.jpg);'></div>") : "";
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
	function typeLinked ( row, type, full, meta ) {
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
	function convertRaDec ( radec, ra ) {
		if (ra !== null && ra) {
			return raToDegrees(radec);
		}
		return decToDegrees(radec);
	}
	function compRaDecs ( radec1inp, radec2inp, includeSame ) {
		var val1 = convertRaDec (radec1inp);
		var val2 = convertRaDec (radec2inp);
		if (includeSame) {
			return val1 <= val2;
		} else {
			return val1 < val2;
		}
	}
	function advancedDateFilter ( data, id, pmid ) {
		var idObj = document.getElementById(id);
		var pmidString = '';
		if ( typeof pmid !== 'undefined' ) {
			var pmidObj = document.getElementById(pmid);
			if ( pmidObj !== null ) {
				pmidString = pmidObj.value;
			}
		}
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
				else if ( pmidString !== '' ) {
					minMJD = dateToMJD(splitString[i]) - parseFloat(pmidString);
					maxMJD = dateToMJD(splitString[i]) + parseFloat(pmidString);
					cMJD = dateToMJD(cData);
					if (cMJD >= minMJD && cMJD <= maxMJD) {
						return !isNot;
					} else {
						return isNot;
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
	function advancedRaDecFilter ( data, id, pmid ) {
		var ra = (id.indexOf('ra') !== -1);
		var idObj = document.getElementById(id);
		var pmidString = '';
		if ( typeof pmid !== 'undefined' ) {
			var pmidObj = document.getElementById(pmid);
			if ( pmidObj !== null ) {
				pmidString = pmidObj.value;
			}
		}
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
				var cleanString = splitString[i].trim().replace(/\s+/g, ':').replace(/[hm]/g, ':');
				if ( !ra ) cleanString = cleanString.replace(/[dm]/g, ':').replace(/s$/, '');
				cleanString = cleanString.replace(/s$/, '');
				if ( cleanString.indexOf('-') !== -1 )
				{
					var splitRange = cleanString.split('-');
					var minStr = splitRange[0].replace(/[<=>]/g, '').trim();
					var maxStr = splitRange[1].replace(/[<=>]/g, '').trim();
					if (minStr !== '') {
						if (!( (minStr !== '' && compRaDecs(cData, minStr, true)) ||
							   (maxStr !== '' && compRaDecs(maxStr, cData, true)) || cData === '' )) return !isNot;
					}
				}
				else if ( pmidString !== '' ) {
					var coorVal = convertRaDec(cleanString, ra);
					minCoord = coorVal - parseFloat(pmidString);
					maxCoord = coorVal + parseFloat(pmidString);
					cCoord = convertRaDec(cData, ra);
					if (cCoord == 0.0) {
						return isNot;
					}
					if (ra && minCoord < 0.0) {
						if ((cCoord >= minCoord && cCoord <= maxCoord) || (cCoord >= 360.0 + minCoord)) {
							return !isNot;
						} else {
							return isNot;
						}
					} else if (ra && maxCoord > 360.0) {
						if ((cCoord >= minCoord && cCoord <= maxCoord) || (cCoord <= maxCoord - 360.0)) {
							return !isNot;
						} else {
							return isNot;
						}
					} else {
						if (cCoord >= minCoord && cCoord <= maxCoord) {
							return !isNot;
						} else {
							return isNot;
						}
					}
				}
				var idStr = cleanString.replace(/[<=>]/g, '').trim();
				if ( idStr === "" || idStr === NaN || idStr === '-' ) {
					if (i === 0) return !isNot;
				}
				if ( idStr === "" || idStr === NaN ) {
					if (i === 0) return !isNot;
				}
				else {
					//if (cData === '') return false;
					if ( cleanString.indexOf('<=') !== -1 )
					{
						if ( compRaDecs(cData, idStr, true) ) return !isNot;
					}
					else if ( cleanString.indexOf('<') !== -1 )
					{
						if ( compRaDecs(cData, idStr, false) ) return !isNot;
					}
					else if ( cleanString.indexOf('>=') !== -1 )
					{
						if ( compRaDecs(idStr, cData, true) ) return !isNot;
					}
					else if ( cleanString.indexOf('>') !== -1 )
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
	function advancedStringFilter ( data, id, prid ) {
		var idObj = document.getElementById(id);
		var pridString = '';
		if ( typeof prid !== 'undefined' ) {
			var pridObj = document.getElementById(prid);
			if ( pridObj !== null ) {
				pridString = pridObj.value.trim().toUpperCase();
			}
		}
		if ( idObj === null ) return true;
		var idString = idObj.value;
		if ( idString === '' && pridString === '' ) return true;
		var splitString = idString.split(/(?:,|OR)+/);
		var splitData = data.split(',');
		var sdlen = splitData.length;
		for ( var d = 0; d < sdlen; d++ ) {
			var cData = splitData[d].trim();
			var uData = cData.toUpperCase();
			var sslen = splitString.length;
			for ( var i = 0; i < sslen; i++ ) {
				var idStr = splitString[i].trim().toUpperCase();
				var isNot = (idStr.indexOf('!') !== -1 || idStr.indexOf('NOT') !== -1)
				idStr = idStr.replace(/!/g, '');
				if ( pridString !== '' ) {
					if (uData.substring(0, pridString.length) !== pridString) {
						continue;
					}
				}
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
	function advancedFloatFilter ( data, id, pmid ) {
		var idObj = document.getElementById(id);
		var pmidString = '';
		if ( typeof pmid !== 'undefined' ) {
			var pmidObj = document.getElementById(pmid);
			if ( pmidObj !== null ) {;
				pmidString = pmidObj.value;
			}
		}
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
				var dashindex = splitString[i].indexOf('-');
				if ( dashindex !== -1 && dashindex !== 0 )
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
				else if ( pmidString !== '' ) {
					minVal = parseFloat(splitString[i]) - parseFloat(pmidString);
					maxVal = parseFloat(splitString[i]) + parseFloat(pmidString);
					cVal = parseFloat(cData);
					if (cVal >= minVal && cVal <= maxVal) {
						return !isNot;
					} else {
						return isNot;
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
						if ( cData === "" ) return false;
						if ( idVal <= cVal ) return true;
					}
					else if ( splitString[i].indexOf('>') !== -1 )
					{
						if ( cData === "" ) return false;
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

function transient_catalog($bones = false) {
	global $stem, $modu;
	readfile("/var/www/html/" . $stem . "/astrocats/astrocats/" . $modu .
		"/output/html/table-templates/" . ($bones ? "bones" : "catalog"). ".html");
?>
	<script>
	var bones = <?php echo json_encode($bones); ?>;
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColValPMDict = {};
		var floatColInds = [];
		var floatSearchCols = ['redshift', 'ebv', 'photolink', 'spectralink', 'radiolink',
			'xraylink', 'maxappmag', 'maxabsmag', 'velocity', 'lumdist', 'hostoffsetang',
			'hostoffsetdist', 'altitude', 'azimuth', 'airmass', 'skybrightness', 'masses'];
		var stringColValDict = {};
		var stringColValPMDict = {};
		var stringColInds = [];
		var stringSearchCols = ['name', 'alias', 'host', 'instruments', 'claimedtype'];
		var raDecColValDict = {};
		var raDecColValPMDict = {};
		var raDecColInds = [];
		var raDecSearchCols = ['ra', 'dec', 'hostra', 'hostdec'];
		var dateColValDict = {};
		var dateColValPMDict = {};
		var dateColInds = [];
		var dateSearchCols = [ 'discoverdate', 'maxdate' ];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
		function ebvValue ( row, type, full, meta ) {
			if (!row.ebv) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat(row.ebv[0]['value']);
		}
		function ebvLinked ( row, type, full, meta ) {
			if (!row.ebv) return '';
			return row.ebv[0]['value']; 
		}
		function photoLinked ( row, type, full, meta ) {
			if (!row.photolink) return '';
			if (row.photolink.indexOf(',') !== -1) {
				var photosplit = row.photolink.split(',');
				var retstr = "<div class='tooltip'><a class='lci' href='" + urlstem + nameToFilename(row.name) +
					"/' target='_blank'></a> " + photosplit[0] + "<span class='tooltiptext'> Detected epochs: " + photosplit[1];
				if (photosplit.length > 2) retstr += " – " + photosplit[2];
				retstr += "</span></div>"; 
				return retstr;
			}
			return "<a class='lci' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> " + row.photolink; 
		}
		function photoSort ( row, type, val ) {
			if (!row.photolink) return NaN;
			return parseInt(row.photolink.split(',')[0]);
		}
		function photoValue ( row, type, full, meta ) {
			if (!row.photolink) return '';
			return parseInt(row.photolink.split(',')[0]);
		}
		function spectraLinked ( row, type, full, meta ) {
			if (!row.spectralink) return '';
			if (row.spectralink.indexOf(',') !== -1) {
				var spectrasplit = row.spectralink.split(',');
				var retstr = "<div class='tooltip'><a class='sci' href='" + urlstem + nameToFilename(row.name) +
					"/' target='_blank'></a> " + spectrasplit[0] + "<span class='tooltiptext'> Epochs: " + spectrasplit[1];
				if (spectrasplit.length > 2) retstr += " – " + spectrasplit[2];
				retstr += "</span></div>"; 
				return retstr;
			}
			return "<a class='sci' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> " + row.spectralink; 
		}
		function spectraSort ( row, type, val ) {
			if (!row.spectralink) return NaN;
			return parseInt(row.spectralink.split(',')[0]);
		}
		function spectraValue ( row, type, full, meta ) {
			if (!row.spectralink) return '';
			return parseInt(row.spectralink.split(',')[0]);
		}
		function radioLinked ( row, type, full, meta ) {
			if (!row.radiolink) return '';
			return "<a class='rci' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> " + row.radiolink; 
		}
		function xrayLinked ( row, type, full, meta ) {
			if (!row.xraylink) return '';
			return "<a class='xci' href='" + urlstem + nameToFilename(row.name) + "/' target='_blank'></a> " + row.xraylink; 
		}
		function hostoffsetangValue ( row, type, full, meta ) {
			if (!row.hostoffsetang) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.hostoffsetang[0]['value']);
			return data;
		}
		function hostoffsetdistValue ( row, type, full, meta ) {
			if (!row.hostoffsetdist) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.hostoffsetdist[0]['value']);
			return data;
		}
		function getSunMoonStr ( alt, azi ) {
			var moonsunstr = '';
			if (moonAlt != 0.0 && moonAzi != 0.0 ) {
				moondist = angDist(Math.PI/180.0*azi, Math.PI/180.0*alt, moonAzi, moonAlt);
				if (moondist < 5.0*Math.PI/180.0) moonsunstr += '&nbsp;<span title="Object is &lt;5&deg; from the Moon">' + moonPhaseIcon + '</span>';
			}
			if (sunAlt != 0.0 && sunAzi != 0.0 ) {
				sundist = angDist(Math.PI/180.0*azi, Math.PI/180.0*alt, sunAzi, sunAlt);
				if (sundist < 5.0*Math.PI/180.0) moonsunstr += '&nbsp;<span title="Object is &lt;5&deg; from the Sun">☀️</span>';
			}
			return moonsunstr;
		}
		function renderObsValue ( data, type, row ) {
			if (data === '') {
				if (type === 'sort') return NaN;
				return '';
			}
			if (type === 'display') {
				var moonsunstr = getSunMoonStr(row.altitude, row.azimuth);
				return String(data.toFixed(3)) + moonsunstr;
			}
			return data;
		}
		function setObsRaDec ( row ) {
			if ('obs_ra' in row && 'obs_dec' in row) return;
			if (row.ra && row.dec) {
				row.obs_ra = raToDegrees(row.ra[0]['value']);
				row.obs_dec = decToDegrees(row.dec[0]['value']);
			} else if (row.hostra && row.hostdec) {
				row.obs_ra = raToDegrees(row.hostra[0]['value']);
				row.obs_dec = decToDegrees(row.hostdec[0]['value']);
			} else {
				row.obs_ra = '';
				row.obs_dec = '';
			}
		}
		function altitudeValue ( row, type, set, meta ) {
			if ('altitude' in row) return row.altitude;
			setObsRaDec(row);
			if (row.obs_ra === '' || row.obs_dec === '') {
				row.altitude = '';
				return row.altitude;
			}
			row.altitude = getAlt(row.obs_ra, row.obs_dec);
			return row.altitude;
		}
		function azimuthValue ( row, type, set, meta ) {
			if ('azimuth' in row) return row.azimuth;
			setObsRaDec(row);
			if (row.obs_ra === '' || row.obs_dec === '') {
				row.azimuth = '';
				return row.azimuth;
			}
			row.azimuth = getAzi(row.obs_ra, row.obs_dec);
			return row.azimuth;
		}
		function airmassValue ( row, type, set, meta ) {
			if ('airmass' in row) return row.airmass;
			setObsRaDec(row);
			if (row.obs_ra === '' || row.obs_dec === '') {
				row.airmass = '';
				return row.airmass;
			}
			var alt = altitudeValue(row, type, set, meta);
			var airmass = 1.0 / Math.sin(Math.PI / 180.0 * (alt + 244.0/(165.0 + 47.0*Math.pow(alt, 1.1))));
			if (isNaN(airmass)) {
				row.airmass = '';
				return row.airmass;
			}
			row.airmass = airmass;
			return row.airmass;
		}
		function skyBrightnessValue ( row, type, set, meta ) {
			if ('skybrightness' in row) return row.skybrightness;
			setObsRaDec(row);
			if (row.obs_ra === '' || row.obs_dec === '') {
				row.skybrightness = '';
				return row.skybrightness;
			}
			var alt = altitudeValue(row, type, set, meta);
			var azi = azimuthValue(row, type, set, meta);
			if ( alt <= 0.0 ) return (type === 'sort') ? NaN : '';
			var zen = Math.PI/180.0*(90.0 - alt);
			// From Krisciunas and Schaefer 1991
			// Background sky
			var bzen = 79.0;
			var aX = Math.pow(1.0 - 0.96*Math.pow(Math.sin(zen), 2), -0.5);
			var k = 0.172;
			var b = bzen*Math.pow(10.0, -0.4*k*(aX-1.0))*aX;
			// Moon
			var bmoon = 0.0;
			if ( moonAlt > 0.0 ) {
				var zenm = Math.PI/2.0 - moonAlt;
				var aXm = Math.pow(1.0 - 0.96*Math.pow(Math.sin(Math.min(zenm, Math.PI/2.0)), 2), -0.5);
				var rhom = angDist(Math.PI/180.0*azi, Math.PI/180.0*alt, moonAzi, moonAlt);
				var istarm = Math.pow(10.0, -0.4*(3.84 + 0.026*moonPhaseAlpha + 4.0e-9*Math.pow(moonPhaseAlpha, 4.0)));
				var frhom = Math.pow(10.0, 5.36) * (1.06 + Math.pow(Math.cos(rhom), 2)) + Math.pow(10.0, 6.15 - 180.0/Math.PI*rhom/40.0);
				bmoon = frhom*istarm*Math.pow(10.0, -0.4*k*aXm) * (1.0 - Math.pow(10.0, -0.4*k*aX));
			}
			// Sun (same as moon but with no phase and much larger app. mag.)
			var bsun = 0.0;
			if ( sunAlt > 0.0 ) {
				var zens = Math.PI/2.0 - sunAlt;
				var aXs = Math.pow(1.0 - 0.96*Math.pow(Math.sin(Math.min(zens, Math.PI/2.0)), 2), -0.5);
				var rhos = angDist(Math.PI/180.0*azi, Math.PI/180.0*alt, sunAzi, sunAlt);
				var istars = Math.pow(10.0, -0.4*(3.84 + 12.6 - 26.7));
				var frhos = Math.pow(10.0, 5.36) * (1.06 + Math.pow(Math.cos(rhos), 2)) + Math.pow(10.0, 6.15 - 180.0/Math.PI*rhos/40.0);
				bsun = frhos*istars*Math.pow(10.0, -0.4*k*aXs) * (1.0 - Math.pow(10.0, -0.4*k*aX));
			}
			// Total mags
			var sb = 22.49989 - 1.08573*Math.log(0.02934*(b + bmoon + bsun));
			row.skybrightness = sb;
			return row.skybrightness;
		}
		function redshiftValue ( row, type, full, meta ) {
			if (!row.redshift) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.redshift[0]['value']);
			return data;
		}
		function velocityValue ( row, type, full, meta ) {
			if (!row.velocity) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.velocity[0]['value']);
			return data;
		}
		function lumdistValue ( row, type, full, meta ) {
			if (!row.lumdist) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = parseFloat(row.lumdist[0]['value']);
			return data;
		}
		function redshiftLinked ( row, type, full, meta ) {
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
		function velocityLinked ( row, type, full, meta ) {
			if (!row.velocity) return '';
			var data = row.velocity[0]['value'];
			if (row.velocity[0]['kind']) {
				var kind = row.velocity[0]['kind'];
				return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + kind + "</span></div>";
			}
			return data;
		}
		function lumdistLinked ( row, type, full, meta ) {
			if (!row.lumdist) return '';
			var data = row.lumdist[0]['value'];
			if (row.lumdist[0]['kind']) {
				var kind = row.lumdist[0]['kind'];
				return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + kind + "</span></div>";
			}
			return data;
		}
		function raValue ( row, type, full, meta ) {
			if (!row.ra) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.ra[0]['value'];
			return raToDegrees(data);
		}
		function decValue ( row, type, full, meta ) {
			if (!row.dec) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.dec[0]['value'];
			return decToDegrees(data);
		}
		function raLinked ( row, type, full, meta ) {
			if (!row.ra) return '';
			var data = row.ra[0]['value'];
			var degrees = raToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function decLinked ( row, type, full, meta ) {
			if (!row.dec) return '';
			var data = row.dec[0]['value'];
			var degrees = decToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function hostraValue ( row, type, full, meta ) {
			if (!row.hostra) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.hostra[0]['value'];
			return raToDegrees(data);
		}
		function hostdecValue ( row, type, full, meta ) {
			if (!row.hostdec) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.hostdec[0]['value'];
			return decToDegrees(data);
		}
		function hostraLinked ( row, type, full, meta ) {
			if (!row.hostra) return '';
			var data = row.hostra[0]['value'];
			var degrees = raToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function hostdecLinked ( row, type, full, meta ) {
			if (!row.hostdec) return '';
			var data = row.hostdec[0]['value'];
			var degrees = decToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		Date.prototype.getJulian = function() {
			return Math.round((this / 86400000) - (this.getTimezoneOffset()/1440) + 2440587.5, 0.1);
		}
		function maxDateValue ( row, type, full, meta ) {
			if (!row.maxdate) {
				if (type === 'sort') return NaN;
				return '';
			}
			var mydate = new Date(row.maxdate[0]['value']);
			return mydate.getTime();
		}
		function discoverDateValue ( row, type, full, meta ) {
			if (!row.discoverdate) {
				if (type === 'sort') return NaN;
				return '';
			}
			var mydate = new Date(row.discoverdate[0]['value']);
			return mydate.getTime();
		}
		function maxDateLinked ( row, type, full, meta ) {
			if (!row.maxdate) return '';
			var mjd = String(dateToMJD(row.maxdate[0]['value']));
			return "<div class='tooltip'>" + row.maxdate[0]['value'] + "<span class='tooltiptext'> MJD: " + mjd + "</span></div>";
		}
		function discoverDateLinked ( row, type, full, meta ) {
			if (!row.discoverdate) return '';
			var mjd = String(dateToMJD(row.discoverdate[0]['value']));
			return "<div class='tooltip'>" + row.discoverdate[0]['value'] + "<span class='tooltiptext'> MJD: " + mjd + "</span></div>";
		}
		function dataLinked ( row, type, full, meta ) {
			var fileeventname = nameToFilename(row.name);
			var datalink = "<a class='dci' title='Download Data' href='" + stem + '/' + fileeventname + ".json' download></a>"
			if (!row.download || row.download != 'e') {
				return (datalink + "<a class='eci' title='Edit Data' onclick='eSN(\"" + row.name + "\",\"" + fileeventname + "\",\"" + stem + "\")'></a>") 
			} else {
				return (datalink + "<a class='eci' title='Edit Data' href='https://github.com/astrocatalogs/" + stem + "-internal/edit/master/"
					+ fileeventname + ".json' target='_blank'></a>")
			}
		}
		function refLinked ( row, type, full, meta ) {
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
		var $_GET = getQueryParams(document.location.search);
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
				// The "hasClass" call here should be removed so that invisible columns can still filter.
				if (jQuery(this).hasClass(floatSearchCols[i])) {
					floatColValDict[index] = floatSearchCols[i];
					floatColValPMDict[index] = floatSearchCols[i] + '-pm';
					floatColInds.push(index);
					break;
				}
			}
			var sslen = stringSearchCols.length;
			for (i = 0; i < sslen; i++) {
				if (jQuery(this).hasClass(stringSearchCols[i])) {
					stringColValDict[index] = stringSearchCols[i];
					stringColValPMDict[index] = stringSearchCols[i] + '-pm';
					stringColInds.push(index);
					break;
				}
			}
			var dslen = dateSearchCols.length;
			for (i = 0; i < dslen; i++) {
				if (jQuery(this).hasClass(dateSearchCols[i])) {
					dateColValDict[index] = dateSearchCols[i];
					dateColValPMDict[index] = dateSearchCols[i] + '-pm';
					dateColInds.push(index);
					break;
				}
			}
			var rdlen = raDecSearchCols.length;
			for (i = 0; i < rdlen; i++) {
				if (jQuery(this).hasClass(raDecSearchCols[i])) {
					raDecColValDict[index] = raDecSearchCols[i];
					raDecColValPMDict[index] = raDecSearchCols[i] + '-pm';
					raDecColInds.push(index);
					break;
				}
			}
			if (classname == 'name') {
				jQuery(this).attr('colspan', 2);
				gclassname = 'event';
			} else gclassname = classname;
			var getval = (gclassname in $_GET) ? $_GET[gclassname] : '';
			var classnamepm = classname + '-pm'
			var getpmval = ((classnamepm) in $_GET) ? $_GET[gclassnamepm] : '';
			var inputstr = '<input class="colsearch" type="search" incremental="incremental" id="'+classname+'" placeholder="'+title+'" value="' + getval + '" />';
			if (['ra', 'dec', 'hostra', 'hostdec'].indexOf(classname) >= 0) {
				inputstr += '<br><input class="colsearch" type="search" incremental="incremental" id="'+classnamepm+'" placeholder="± degs" value="' + getpmval + '" />';
			} else if (['maxdate', 'discoverdate'].indexOf(classname) >= 0) {
				inputstr += '<br><input class="colsearch" type="search" incremental="incremental" id="'+classnamepm+'" placeholder="± days" value="' + getpmval + '" />';
			} else if (['maxabsmag', 'maxappmag'].indexOf(classname) >= 0) {
				inputstr += '<br><input class="colsearch" type="search" incremental="incremental" id="'+classnamepm+'" placeholder="± mags" value="' + getpmval + '" />';
			} else if (['redshift'].indexOf(classname) >= 0) {
				inputstr += '<br><input class="colsearch" type="search" incremental="incremental" id="'+classnamepm+'" placeholder="±" value="' + getpmval + '" />';
			} else if (['name', 'host', 'claimedtype'].indexOf(classname) >= 0) {
				inputstr += '<br><input class="colsearch" type="search" incremental="incremental" id="'+classnamepm+'" placeholder="w/ prefix" value="' + getpmval + '" />';
			}
            jQuery(this).html( inputstr );
        } );
		var ajaxURL = '/../../astrocats/astrocats/' + modu + '/output/' + ((bones) ? 'bones' : 'catalog') + '.min.json';
		jQuery.fn.redraw = function(){
		  jQuery(this).each(function(){
			  this.style.display='none';
			  this.offsetHeight; // no need to store this anywhere, the reference is enough
			  this.style.display='block';
		  });
		};
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: ajaxURL,
				dataSrc: function ( json ) {
					jQuery('#loadingMessage').html('Generating table...');
					jQuery('#loadingMessage').toggleClass('force-redraw');
					return json;
				}
			},
			"language": {
				"loadingRecords": "<img style='vertical-align:-43%; padding-right:3px' src='wp-content/plugins/transient-table/loading.gif' title='Please wait!'><span id='loadingMessage'>Loading... (should take a few seconds)</span>"
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": null, "name": "name", "type": "string", "responsivePriority": 1, "render": nameSwitcherName },
				{ "data": {
					"_": eventAliases,
					"display": eventAliasesOnly,
				  }, "name": "aliases", "type": "string" },
				{ "data": {
					"display": discoverDateLinked,
					"filter": "discoverdate.0.value",
					"sort": discoverDateValue,
					"_": "discoverdate[,].value"
				  }, "name": "discoverdate", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 2 },
				{ "data": {
					"display": maxDateLinked,
					"filter": "maxdate.0.value",
					"sort": maxDateValue,
					"_": "maxdate[,].value"
				  }, "type": "non-empty-float", "defaultContent": "" },
				{ "data": "maxappmag.0.value", "name": "maxappmag", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": "maxabsmag.0.value", "name": "maxabsmag", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": "masses", "name": "masses", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": null, "name": "host", "type": "string", "width":"14%", "render": hostSwitcher },
				{ "data": {
					"display": raLinked,
					"filter": "ra.0.value",
					"sort": raValue,
					"_": "ra[,].value"
				  }, "name": "ra", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"display": decLinked,
					"filter": "dec.0.value",
					"sort": decValue,
					"_": "dec[,].value"
				  }, "name": "dec", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"display": hostraLinked,
					"filter": "hostra.0.value",
					"sort": hostraValue,
					"_": "hostra[,].value"
				  }, "name": "hostra", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"display": hostdecLinked,
					"filter": "hostdec.0.value",
					"sort": hostdecValue,
					"_": "hostdec[,].value"
				  }, "name": "hostdec", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"filter": hostoffsetangValue,
					"sort": hostoffsetangValue,
					"_": "hostoffsetang.0.value"
				  }, "name": "hostoffsetang", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"filter": hostoffsetdistValue,
					"sort": hostoffsetdistValue,
					"_": "hostoffsetdist.0.value"
				  }, "name": "hostoffsetdist", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": altitudeValue, "name": "altitude", "type": "non-empty-float", "render": renderObsValue, "defaultContent": "" },
				{ "data": azimuthValue, "name": "azimuth", "type": "non-empty-float", "render": renderObsValue, "defaultContent": "" },
				{ "data": airmassValue, "name": "airmass", "type": "non-empty-float", "render": renderObsValue, "defaultContent": "" },
				{ "data": skyBrightnessValue, "name": "skybrightness", "type": "non-empty-float", "render": renderObsValue, "defaultContent": "" },
				{ "data": "instruments", "name": "instruments", "type": "string", "defaultContent": "" },
				{ "data": {
					"display": redshiftLinked,
					"filter": redshiftValue,
					"sort": redshiftValue,
					"_": "redshift[,].value"
				  }, "name": "redshift", "type": "non-empty-float", "defaultContent": "" },
				{ "data": {
					"display": velocityLinked,
					"filter": velocityValue,
					"sort": velocityValue,
					"_": "velocity[,].value"
				  }, "name": "velocity", "type": "non-empty-float", "defaultContent": "" },
				{ "data": {
					"display": lumdistLinked,
					"filter": lumdistValue,
					"sort": lumdistValue,
					"_": "lumdist[,].value"
				  }, "name": "lumdist", "type": "non-empty-float", "defaultContent": "" },
				{ "data": null, "name": "claimedtype", "type": "string", "responsivePriority": 3, "render": typeSwitcher },
				{ "data": {
					"display": ebvLinked,
					"_": ebvValue
				  }, "name": "ebv", "type": "non-empty-float", "defaultContent": "" },
				{ "data": {
					"display": photoLinked,
					"_": photoValue,
					"sort": photoSort
				  }, "name": "photolink", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 2, "width":"6%" },
				{ "data": {
					"display": spectraLinked,
					"_": spectraValue,
					"sort": spectraSort
				  }, "name": "spectralink", "type": "non-empty-float", "defaultContent": "", "responsivePriority": 2, "width":"5%" },
				{ "data": {
					"display": radioLinked,
					"_": "radiolink"
				  }, "name": "radiolink", "type": "num", "defaultContent": "", "responsivePriority": 2, "width":"4%" },
				{ "data": {
					"display": xrayLinked,
					"_": "xraylink",
				  }, "name": "xraylink", "type": "num", "defaultContent": "", "responsivePriority": 2 },
				{ "data": {
					"display": refLinked,
					"_": "references"
				  }, "name": "references", "type": "html", "searchable": false },
				{ "data": dataLinked, "name": "data", "responsivePriority": 4, "searchable": false },
				{ "defaultContent": "" },
			],
            dom: '<"addmodal">Bflprt<"coordfoot">ip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: plen[1],
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1
				}
			},
            select: true,
            lengthMenu: [ plen, plen ],
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
				},
				{
					action: function ( e, dt, button, config ) {
						var colsearches = document.getElementsByClassName('colsearch');
						var querystring = '';
						for ( var i = 0; i < colsearches.length; i++ ) {
							var cs = colsearches[i];
							if (cs.value !== '') {
								qpref = (querystring === '') ? '?' : '&';
								var csid = (cs.id === 'name') ? 'event' : cs.id;
								querystring += qpref + csid + '=' + encodeURIComponent(cs.value.replace(/"/g, '&quot;'));
							}
						}
						var visiblestring = '';
						for ( var i = 0; i < colsearches.length; i++ ) {
							var cs = colsearches[i];
							vpref = (visiblestring === '') ? '' : ',';
							if (!cs.id.endsWith('-pm')) {
								visiblestring += vpref + cs.id;
							}
						}
						visiblestring = 'visible=' + encodeURIComponent(visiblestring);
						querystring = (querystring === '') ? '?' + visiblestring : querystring + '&' + visiblestring;
						querystring = 'https://' + subd + '.space/' + querystring; 
						window.prompt("Permanent link to this table query:", querystring);
					},
					text: 'Copy Permalink'
				},
				{
					text: '<span id="addicon">+</span> Add ' + sing,
					action: function ( e, dt, node, conf ) {
						document.getElementById('addmodalwindow').style.display = 'block';
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

		// Set up observable filter widget.
		<?php 
			// File from http://www.minorplanetcenter.net/iau/lists/ObsCodes.html
			$lochtml = json_encode(file_get_contents(__DIR__ . '/ObsCodes.html'));
		?>
		var lochtml = <?php echo $lochtml;?>;
		var htmlsplit = lochtml.split("\n");
		var observatories = Array();
		for ( var i = 2; i < htmlsplit.length - 1; i++ ) {
			obsname = htmlsplit[i].slice(30) + ' [' + htmlsplit[i].slice(0, 3) + ']';
			var obslong = jQuery.trim(htmlsplit[i].slice(4, 13));
			var cosp = jQuery.trim(htmlsplit[i].slice(13, 21));
			var sinp = jQuery.trim(htmlsplit[i].slice(21, 30));
			if ( cosp === '' || sinp === '' ) {
				continue;
			}
			cosp = parseFloat(cosp);
			sinp = parseFloat(sinp);
			if ( cosp == 0.0 && sinp == 0.0 ) {
				continue;
			}
			var p = Math.sqrt(cosp*cosp + sinp*sinp);
			var obslat = (180.0/Math.PI*Math.atan2(sinp/p, cosp/p)).toFixed(5);
			observatories.push( Array(obsname, obslong + ',' + obslat) );
		}
		observatories = observatories.sort( function(a, b) { 
			return a[0].localeCompare(b[0]);
		} );
		var obsstr = '<select class="obssel" id="inpobs" style="width:110px">';
		obsstr += '<option value="select">Observatories</option>';
		for ( var i = 0; i < observatories.length; i++ ) {
			obsstr += '<option value="' + observatories[i][1] + '"';
			if ( observatories[i][0].indexOf('Keck') != -1 ) {
				var obscoords = observatories[i][1];
				var obslong = obscoords.split(',')[0];
				var obslat = obscoords.split(',')[1];
				longitude = parseFloat(obslong);
				latitude = parseFloat(obslat);
				obsstr += ' selected';
			}
			obsstr += '>' + observatories[i][0] + '</option>';
		}
		obsstr += '</select>';
		var monshort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		var dayslist = Array.apply(null, Array(31)).map(function (_, i) {return i + 1;});
		var curdate = new Date();
		var curyear = curdate.getFullYear();
		var curmonth = curdate.getMonth();
		var curday = curdate.getDate();
		var yearslist = Array.apply(null, Array(1000)).map(function (_, i) {return curyear - i + 5;});
		var yearsstr = '<select class="obssel" id="inpyear">';
		for ( var i = 0; i < yearslist.length; i++ ) {
			yearsstr += '<option value="' + yearslist[i] + '"';
			if ( curyear - i + 5 == curyear ) yearsstr += ' selected'
			yearsstr += '>' + yearslist[i] + '</option>';
		}
		yearsstr += '</select>';
		var monsstr = '<select class="obssel" id="inpmon">';
		for ( var i = 0; i < monshort.length; i++ ) {
			monsstr += '<option value="' + i + '"';
			if ( i == curmonth ) monsstr += ' selected'
			monsstr += '>' + monshort[i] + '</option>';
		}
		monsstr += '</select>';
		var daysstr = '<select class="obssel" id="inpday">';
		for ( var i = 0; i < dayslist.length; i++ ) {
			daysstr += '<option value="' + dayslist[i] + '"';
			if ( i + 1 == curday ) daysstr += ' selected'
			daysstr += '>' + dayslist[i] + '</option>';
		}
		daysstr += '</select>';

		var footstring = 
			'<table id="advancedtab"><tr><td><div id="obsfrom"><label><input type="checkbox" id="coordobservable">' +
			'Observable from</label> </div><span id="lonlat">' + 
			'<input class="coordfield" id="inplon" incremental="incremental" title="Longitude (deg.)" placeholder="Longitude">, ' +
			'<input class="coordfield" id="inplat" incremental="incremental" title="Latitude (deg.)" placeholder="Latitude"><br>' +
			'<button type="button" id="locbutt" onclick="geoFindMe()"><span id="inpmessage">🌎 Use my location</span></button>' +
			obsstr + '</span>' +
			'<span id="obstime"><select id="nowon" class="obssel"><option value="now">now</option><option value="on">on</option></select>' +
			'<span id="ondate" style="display:none">' + yearsstr + monsstr + daysstr +
			' at <input class="coordfield" id="inptime" title="24-hour time (hh:mm:ss)" value="00:00:00" placeholder="hh:mm:ss"> [UTC]</span>' +
			'<br><span id="suninfo"></span></span><br><span title="Exclude objects closer than 5&deg; from the Moon">' +
			'<label><input type="checkbox" id="farfrommoon"> Far from the Moon</label></span> ' +
			'<span title="Exclude objects closer than 5&deg; from the Sun">' +
			'<label><input type="checkbox" id="farfromsun"> Far from the Sun</label></span>' +
			'</td><td>Has <span id="prepost"><label><input type="checkbox" id="premaxphoto"> pre-</label> ' +
			'<label><input type="checkbox" id="postmaxphoto"> post-max</label> photometry' +
			'<br><label><input type="checkbox" id="premaxspectra"> pre-</label> ' +
			'<label><input type="checkbox" id="postmaxspectra"> post-max</label> spectroscopy</span>' +
			'</td></tr></table>';
		jQuery("div.coordfoot").html(footstring);
		var addmodalstring = '<div id="addmodalwindow" class="addmodal-bg">' +
		    '<div class="addmodal-content">' +
		    '<span class="addmodal-close">&times;</span>' +
			'<p>Specify object details below:</p>' +
		    '<table id="addtable"><tr><th>' + sing + ' name*</th><th>Bibcode*</th></tr>' +
			'<tr><td><input type="text" id="objectname"></td>' +
			'<td><input type="text" id="objectbibcode"></td></tr>' +
			'</table>' +
			'<p>* = Required</p>' +
			'<a class="dt-button" id="addgithub"><span>Submit to GitHub</span></a>' +
			'</div>' +
			'</div>';
		jQuery("div.addmodal").html(addmodalstring);
        table.columns().every( function ( index ) {
            var that = this;

			var isFirefox = typeof InstallTrigger !== 'undefined';
			if (isFirefox) {
				// Needed for FireFox
				jQuery( 'input', that.footer() ).change( function () {
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
			} else {
				jQuery( 'input', that.footer() ).on( 'search', function () {
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
			}
        } );

		// Set up search functions.
		nameColumn = table.column('name:name').index();
		raColumn = table.column('ra:name').index();
		decColumn = table.column('dec:name').index();
		altColumn = table.column('altitude:name').index();
		aziColumn = table.column('azimuth:name').index();
		amColumn = table.column('airmass:name').index();
		sbColumn = table.column('skybrightness:name').index();

		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex, rowData ) {
				var alen = aData.length;

				for ( var i = 0; i < alen; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i], floatColValPMDict[i] ) ) return false;
					} else if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i], stringColValPMDict[i] ) ) return false;
					} else if ( dateColInds.indexOf(i) !== -1 ) {
						if ( !advancedDateFilter( aData[i], dateColValDict[i], dateColValPMDict[i] ) ) return false;
					} else if ( raDecColInds.indexOf(i) !== -1 ) {
						if ( !advancedRaDecFilter( aData[i], raDecColValDict[i], raDecColValPMDict[i] ) ) return false;
					}
				}
				if ( document.getElementById('coordobservable').checked ) {
					if ( aData[raColumn] === null || aData[decColumn] === null ) return false;
					if ( aData[altColumn] !== '' ) {
						if ( aData[altColumn] < 0.0 ) return false;
					}
					alt = getAlt(aData[raColumn], aData[decColumn]);
					if ( alt < 0.0 ) return false;
				}
				if ( document.getElementById('farfrommoon').checked ) {
					alt = aData[altColumn];
					azi = aData[aziColumn];
					if (moonAlt != 0.0 && moonAzi != 0.0 ) {
						moondist = angDist(Math.PI/180.0*azi, Math.PI/180.0*alt, moonAzi, moonAlt);
						if (moondist < 5.0*Math.PI/180.0) return false;
					}
				}
				if ( document.getElementById('farfromsun').checked ) {
					alt = aData[altColumn];
					azi = aData[aziColumn];
					if (sunAlt != 0.0 && sunAzi != 0.0 ) {
						sundist = angDist(Math.PI/180.0*azi, Math.PI/180.0*alt, sunAzi, sunAlt);
						if (sundist < 5.0*Math.PI/180.0) return false;
					}
				}
				if ( document.getElementById('premaxphoto').checked ) {
					if ( !rowData.photolink ) return false;
					var photosplit = rowData.photolink.split(',');
					if ( photosplit.length < 2 ) return false;
					var premaxep = parseFloat(photosplit[1]);
					if ( premaxep >= 0.0 ) return false;
				}
				if ( document.getElementById('postmaxphoto').checked ) {
					if ( !rowData.photolink ) return false;
					var photosplit = rowData.photolink.split(',');
					if ( photosplit.length < 2 ) return false;
					var postmaxep = parseFloat(photosplit[photosplit.length == 3 ? 2 : 1]);
					if ( postmaxep <= 0.0 ) return false;
				}
				if ( document.getElementById('premaxspectra').checked ) {
					if ( !rowData.spectralink ) return false;
					var spectrasplit = rowData.spectralink.split(',');
					if ( spectrasplit.length < 2 ) return false;
					var premaxep = parseFloat(spectrasplit[1]);
					if ( premaxep >= 0.0 ) return false;
				}
				if ( document.getElementById('postmaxspectra').checked ) {
					if ( !rowData.spectralink ) return false;
					var spectrasplit = rowData.spectralink.split(',');
					if ( spectrasplit.length < 2 ) return false;
					var postmaxep = parseFloat(spectrasplit[spectrasplit.length == 3 ? 2 : 1]);
					if ( postmaxep <= 0.0 ) return false;
				}
				return true;
			}
		);
		function locTableUpdate () {
			updateLocation();
			altVisible = table.column(altColumn).visible();
			aziVisible = table.column(aziColumn).visible();
			amVisible = table.column(amColumn).visible();
			sbVisible = table.column(sbColumn).visible();
			if ( document.getElementById('coordobservable').checked || altVisible ||
					aziVisible || amVisible || sbVisible ) {
				//table.rows().invalidate('data').draw();
				table.rows().invalidate('data').every( function () {
					var d = this.data();
					delete d.altitude;
					delete d.azimuth;
					delete d.airmass;
					delete d.skybrightness;
				} ).draw(false);
				//table.cells(null, altColumn).invalidate();
				//table.cells(null, aziColumn).invalidate();
				//table.cells(null, amColumn).invalidate();
				//table.cells(null, sbColumn).invalidate();
				//table.draw(false);
			}
		}
		table.on( 'search.dt', function () {
			searchFields = getSearchFields(allSearchCols);
			table.rows({page:'current'}).invalidate();
		} );
		table.on( 'column-visibility.dt', function (e, settings, column, state) {
			//if ( column == altColumn || column == aziColumn || column == amColumn || column == sbColumn ) {
			//	if ( state ) table.cells(null, column).invalidate();
			//}
		} );
		jQuery('#premaxphoto, #postmaxphoto, #premaxspectra, #postmaxspectra').change( function () {
			table.draw();
		} );
		jQuery('#coordobservable, #farfrommoon, #farfromsun').change( function () {
			table.draw();
		} );
		jQuery('#inplon, #inplat, #inptime, #inpyear, #inpmon, #inpday, #nowon').change( function () {
			locTableUpdate();
		} );
		jQuery('#inplon, #inplat').change( function () {
			jQuery('#inpobs').find('option:eq(0)').prop('selected', true);
		} );
		jQuery('#inpobs').change( function () {
			var obscoords = jQuery('#inpobs').val();
			var obslong = obscoords.split(',')[0];
			var obslat = obscoords.split(',')[1];
			jQuery('#inplon').val(obslong);
			jQuery('#inplat').val(obslat);
			locTableUpdate();
		} );
		jQuery('#nowon').change( function () {
			if ( jQuery(this).val() == 'on' ) {
				jQuery('#ondate').show();
			} else {
				jQuery('#ondate').hide();
			}
		} );
		searchFields = getSearchFields(allSearchCols);

		var modal = document.getElementById('addmodalwindow');
		var span = document.getElementsByClassName("addmodal-close")[0];
		var addgithub = document.getElementById("addgithub");
		span.onclick = function() {
			modal.style.display = "none";
		}
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
		addgithub.onclick = function () {
			var addname = document.getElementById('objectname').value;
			var addnamel = addname.toLowerCase();
			var bibcode = document.getElementById('objectbibcode').value;
			if (addname === '') {
				alert('Please provide name.');
				return;
			}
			var oldnames = '';
			table.data().each(function(val, ind) {
				oldnames += ',' + val["name"].toLowerCase();
				oldnames += ',' + getAliases(val).join(',');
			});
			oldnames = oldnames.toLowerCase().split(',');
			if (oldnames.indexOf(addnamel) > -1) {
				alert(sing + ' entry already exists.');
				return;
			}
			if (bibcode === '' || bibcode.length != 19) {
				alert('19 character bibcode required.');
				return;
			}
			eSN(addname, addname, stem, bibcode);
		}

		setInterval( function () {
			table.ajax.reload(null, false);
			table.draw(false);
		}, 14400000 );
		//setInterval( function () {
		//	if ( jQuery('#nowon').val() === 'now' ) {
		//		updateLocation();
		//	}
		//}, 5000 );
		setInterval( function () {
			if ( document.getElementById('coordobservable').checked && jQuery('#nowon').val() === 'now' ) {
				table.draw(false);
			}
		}, 60000 );
		updateLocation();
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
		var floatSearchCols = ['distdeg', 'maxdiffyear', 'discdiffyear'];
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
		function distDegValue ( row, type, full, meta ) {
			if (!row.distdeg) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.distdeg)).toFixed(5));
		}
		function maxDiffYearValue ( row, type, full, meta ) {
			if (!row.maxdiffyear) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.maxdiffyear)*365.25).toFixed(3));
		}
		function discDiffYearValue ( row, type, full, meta ) {
			if (!row.discdiffyear) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.discdiffyear)*365.25).toFixed(3));
		}
		function performGoogleSearch ( row, type, full, meta ) {
			var namearr = row.aliases1.concat(row.aliases2);
			return "<button class='googleit' type='button' onclick='googleNames(\"" + namearr.join(',') + "\")'>Google all names</button>"
		}
		function markAsDuplicate ( row, type, full, meta ) {
			return "<button class='sameevent' type='button' onclick='markSame(\"" + row.name1 + "\",\"" + row.name2 + "\",\"" + row.edit + "\",\"" + stem + "\")'>These are the same</button>"
		}
		function markAsDistinct ( row, type, full, meta ) {
			return "<button class='diffevent' type='button' onclick='markDiff(\"" + row.name1 + "\",\"" + row.name2 + "\",\"" + row.edit + "\",\"" + stem + "\")'>These are different</button>"
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

function frb_table() {
	global $modu;
	readfile("/root/astrocats/astrocats/" . $modu . "/html/table-templates/frbs.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['distdeg', 'discdiffyear', 'poserror'];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['name1', 'name2', 'claimedtype', 'host'];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = ['ra1', 'dec1', 'ra2', 'dec2'];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ ];
		var allSearchCols = floatSearchCols.concat(stringSearchCols, raDecSearchCols, dateSearchCols);
		function distDegValue ( row, type, full, meta ) {
			if (!row.distdeg) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.distdeg)*60.).toFixed(2));
		}
		function discDiffYearValue ( row, type, full, meta ) {
			if (!row.discdiffyear) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.discdiffyear)*365.25).toFixed(2));
		}
		function posErrorValue ( row, type, full, meta ) {
			if (!row.poserror) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat((parseFloat(row.poserror)/60.).toFixed(2));
		}
		function performGoogleSearch ( row, type, full, meta ) {
			var namearr = row.aliases1.concat(row.aliases2);
			return "<button class='googleit' type='button' onclick='googleNames(\"" + namearr.join(',') + "\")'>Google all names</button>"
		}
		function markAsDuplicate ( row, type, full, meta ) {
			return "<button class='sameevent' type='button' onclick='markSame(\"" + row.name1 + "\",\"" + row.name2 + "\",\"" + row.edit + "\",\"" + stem + "\")'>These are the same</button>"
		}
		function markAsDistinct ( row, type, full, meta ) {
			return "<button class='diffevent' type='button' onclick='markDiff(\"" + row.name1 + "\",\"" + row.name2 + "\",\"" + row.edit + "\",\"" + stem + "\")'>These are different</button>"
		}
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (classname == 'aliases') {
				jQuery(this).remove();
			}
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
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../astrocats/astrocats/' + modu + '/output/frbs.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": "name1", "type": "string", "responsivePriority": 1 },
				{ "data": {
					"_": "ra1"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": {
					"_": "dec1"
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 10 },
				{ "data": null, "type": "string", "responsivePriority": 1, "render": nameSwitcherName2 },
				{ "data": "claimedtype", "type": "string", "responsivePriority": 5, "width": "3%" },
				{ "data": "host.0.value", "defaultContent": "", "type": "string", "width":"14%" },
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
					"_": discDiffYearValue,
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 5, "width": "5%" },
				{ "data": {
					"_": posErrorValue,
				  }, "type": "non-empty-float", "defaultContent": "", "responsivePriority": 5, "width": "5%" },
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
                targets: [ 'ra1', 'dec1' ],
				visible: false
			}, {
				className: 'control',
				orderable: false,
				width: "2%",
				targets: -1
			}, {
				targets: [ ],
				orderable: false
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[9, "asc"]]
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
		function bibcodeLinked ( row, type, full, meta ) {
			var html = '';
			if (row.authors) {
				html += row.authors + '<br>';
			}
			return html + "<a href='http://adsabs.harvard.edu/abs/" + row.bibcode + "'>" + row.bibcode + "</a>";
		}
		function eventsDropdown ( row, type, full, meta ) {
			var elen = row.events.length;
			var html = String(elen) + ' ' + shrt + ': ';
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
		function allAuthors ( row, type, full, meta ) {
			var html = '';
			if (!row.allauthors) return '';
			var alen = row.allauthors.length;
			for (i = 0; i < alen; i++) {
				if (i > 0) html += ', ';
				html += row.allauthors[i];
			}
			return html;
		}
		function eventsDropdownType ( row, type, full, meta ) {
			if (type == "sort") {
				return "num";
			}
			return "string";
		}
		function eventsCount ( row, type, full, meta ) {
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

function atels() {
	global $modu;
	readfile("/root/better-atel/atels.html");
?>
	<script>
	var regPrefixes;
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['num'];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['body'];
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
		function numLinked ( row, type, full, meta ) {
			return '<a href="http://astronomerstelegram.org/?read=' + String(row['num']) + '" target="_blank">' + String(row['num']) + '</a>';
		}
		function escapeRegExp(str) {
		    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		}
		function replacer (match) {
			var name = match;
			return ['<a href="https://sne.space/sne/', name, '" target="_blank">', name, '</a>'].join('');
		}
		function nameMatcher ( txt ) {
			return txt.replace(regPrefixes, replacer);
		}
		function bodyRender ( row, type, full, meta ) {
			var bodyTxt = row['body'];
			return nameMatcher ( bodyTxt );
		}
		function titleRender ( row, type, full, meta ) {
			var titleTxt = row['title'];
			return nameMatcher ( titleTxt );
		}
		function dateLinked ( row, type, full, meta ) {
			var atelDate = new Date(row['date']);
			if (!isFinite(atelDate)) return 'Unknown';
			return String(atelDate.getFullYear()) + '/' + ("0" + (atelDate.getMonth() + 1)).slice(-2) + '/' + ("0" + atelDate.getDate()).slice(-2) +
				'<br>' + ("0" + (atelDate.getHours() + 1)).slice(-2) + ':' + ("0" + (atelDate.getMinutes() + 1)).slice(-2);
		}
		namesObj = jQuery.parseJSON(
			jQuery.ajax(
				{
				   url: '/../../astrocats/astrocats/' + modu + '/output/names.min.json', 
				   async: false, 
				   dataType: 'json'
				}
			).responseText
		);
		var names = [];
		for (var name in namesObj) {
			Array.prototype.push.apply(names, namesObj[name]);
		}
		var nameLen = names.length;
		var prefixes = new Set();
		for (var i = 0; i < nameLen; i++) {
			var name = names[i];
			var index = name.search(/\d/);
			if (index > 0) {
				var newPrefix = jQuery.trim(name.slice(0, index));
				newPrefix = newPrefix.replace(/^-+|-+$/g, '');
				if (newPrefix.length < 2) continue;
				prefixes.add(newPrefix);
			}
		}
		regexes = [];
		for (let prefix of prefixes.values()) {
			regexes.push('\\b(?!http|https)' + escapeRegExp(prefix) + '[\\s\\-]?\\d+[A-Za-z]+[A-Za-z0-9]*\\b');
		}
		regPrefixes = new RegExp('(' + regexes.join(')|(') + ')', 'g');
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '/../../better-atel/atels.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "" },
				{ "data": {
					"_": "num",
					"display": numLinked
				  }, "type": "num" },
			    { "data": {
					"_": "date",
					"display": dateLinked
			      }, "type": "date" },
				{ "data": {
					"_": "title",
					"display": titleRender
				  }, "type": "string", "width": "30%" },
				{ "data": {
					"_": "authors[; ]"
				  }, "type": "string", "defaultContent": "" },
				{ "data": {
					"_": "subjects[; ]"
				  }, "type": "string", "defaultContent": "" },
				{ "data": {
					"_": "body",
					"display": bodyRender
				  }, "type": "string", "defaultContent": "", "className": "none" },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 10,
			searchDelay: 400,
			responsive: {
				details: {
					type: 'column',
					target: -1,
					display: jQuery.fn.dataTable.Responsive.display.childRowImmediate
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
					action: function () {
						jQuery('[id$="example"] tbody td:last-child').trigger('click');
						if (this.text() == 'Collapse all') {
							this.text('Expand all');
						} else {
							this.text('Collapse all');
						}
					},
					text: 'Collapse all'
				},
                {
                    extend: 'colvis',
                    columns: ':not(:first-child):not(:last-child):not(:nth-last-child(2))'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':not(:first-child):not(:last-child)',
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
		jQuery('#example').on( 'draw.dt', function () {
			if (table.button(2).text() == 'Expand all') {
				jQuery('[id$="example"] tbody td:last-child').trigger('click');
			}
		} );
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
		function bibcodeLinked ( row, type, full, meta ) {
			var html = '';
			if (row.authors) {
				html += row.authors + '<br>';
			}
			return html + "<a href='http://adsabs.harvard.edu/abs/" + row.bibcode + "'>" + row.bibcode + "</a>";
		}
		function eventsDropdown ( row, type, full, meta ) {
			var elen = row.events.length;
			var html = String(elen) + ' ' + shrt + ': ';
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
		function allAuthors ( row, type, full, meta ) {
			var html = '';
			if (!row.allauthors) return '';
			var alen = row.allauthors.length;
			for (i = 0; i < alen; i++) {
				if (i > 0) html += ', ';
				html += row.allauthors[i];
			}
			return html;
		}
		function eventsDropdownType ( row, type, full, meta ) {
			if (type == "sort") {
				return "num";
			}
			return "string";
		}
		function eventsCount ( row, type, full, meta ) {
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
		function hostraValue ( row, type, full, meta ) {
			if (!row.hostra) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.hostra;
			return raToDegrees(data);
		}
		function hostdecValue ( row, type, full, meta ) {
			if (!row.hostdec) {
				if (type === 'sort') return NaN;
				return '';
			}
			var data = row.hostdec;
			return decToDegrees(data);
		}
		function hostraLinked ( row, type, full, meta ) {
			if (!row.hostra) return '';
			var data = row.hostra;
			var degrees = raToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function hostdecLinked ( row, type, full, meta ) {
			if (!row.hostdec) return '';
			var data = row.hostdec;
			var degrees = decToDegrees(data).toFixed(5);
			return "<div class='tooltip'>" + data + "<span class='tooltiptext'> " + degrees + "&deg;</span></div>";
		}
		function redshiftValue ( row, type, full, meta ) {
			if (!row.redshift) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat(row.redshift.replace('*',''));
		}
		function lumdistValue ( row, type, full, meta ) {
			if (!row.lumdist) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat(row.lumdist.replace('*',''));
		}
		function rateValue ( row, type, full, meta ) {
			if (!row.rate) {
				if (type === 'sort') return NaN;
				return '';
			}
			return parseFloat(row.rate.split(',')[0]);
		}
		function rateDisplay ( row, type, full, meta ) {
			if (!row.rate) return '';
			return row.rate.split(',')[0] + ' ± ' + row.rate.split(',')[1];
		}
		function hostNameFormat ( str, name ) {
			return str.replace(/%name/g, noBreak(name)).replace(/%link/g, encodeURIComponent(name));
		}
		function hostUnlinked ( row, type, full, meta ) {
			if (!row.host) return '';
			var host = "<a class='" + (row.kind == 'cluster' ? "hci" : "hhi") + "' href='http://simbad.u-strasbg.fr/simbad/sim-basic?Ident=" +
				"%link&submit=SIMBAD+search' target='_blank'></a> "; 
			var mainHost = "<a href='http://simbad.u-strasbg.fr/simbad/sim-basic?Ident=%link&submit=SIMBAD+search' target='_blank'>%name</a>"; 
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
				text = hostNameFormat("<div class='tooltip'>" + host + mainHost + "<span class='tooltiptext'> " +
					hostAliases + "</span></div>", primaryname);
			} else {
				text = hostNameFormat(host + mainHost, row.host[0]);
			}
			var minwidth = 12;
			var totalwidth = 200;
			var padding = 1;
			var imgwidth = Math.max(Math.round(80.0/Math.sqrt(1.0*row.events.length)), minwidth);
			var usethumbs = (imgwidth < 25);
			var mod = Math.max(Math.round(1.0*totalwidth/(1.0*(imgwidth + padding))), 1);
			text = text + "<div style='padding-top:5px; line-height:" + (10 /*imgwidth + padding - 2*/) + "px;'>";
			var cnt = 0;
			for (var i = 0; i < row.events.length; i++) {
				if (!row.events[i].img) continue;
				cnt++;
				text = (text + "<a href='" + urlstem + nameToFilename(row.events[i].name) + "/' target='_blank'>" +
					"<img class='hostimg' width='" + imgwidth + "' height='" + imgwidth + "' src='" + urlstem +
					(usethumbs ? 'thumbs/' : '') + nameToFilename(row.events[i].name) + "-host.jpg' style='margin-right:" +
					padding + "px;'></a>");
			}
			text = text + "</div>";
			return text;
		}
		function eventsDropdown ( row, type, full, meta ) {
			var html = String(row.events.length) + ' ' + shrt + ': ';
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
		function eventsDropdownType ( row, type, full, meta ) {
			if (type == "sort") {
				return "num";
			}
			return "string";
		}
		function eventsCount ( row, type, full, meta ) {
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
					"_": "events[, ].name"
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
		function actionButtons ( row, type, full, meta ) {
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
					"\", \"" + row.sources[i].id + "\", \"" + row.edit + "\",\"" + stem + "\")'>" + quantityStr + ((quantityStr !== "") ? " =<br>" : "") +
					String(row.values[i]) + "<br>is erroneous</button><span class='tooltiptextbot'>" + row.sources[i].id + "</span></div>";
			}
			var aliases = getAliases(row);
			for (i = 1; i < aliases.length; i++) {
				html += ' ';
				html += "<button class='diffevent' type='button' onclick='markDiff(\"" +
					row.name + "\", \"" + aliases[i] + "\", \"" + row.edit + "\",\"" + stem + "\")'>Alias<br>" +
					aliases[i] + "<br>is a different SN</button>";
			}
			return html;
		}
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
				{ "data": actionButtons, "responsivePriority": 4 },
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
		function bibcodeLinked ( row, type, full, meta ) {
			var html = '';
			if (row.authors) {
				html += row.authors + '<br>';
			}
			return html + "<a href='http://adsabs.harvard.edu/abs/" + row.bibcode + "'>" + row.bibcode + "</a>";
		}
		function eventsDropdown ( row, type, full, meta ) {
			var html = String(row.events.length) + ' ' + shrt + ': ';
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
		function allAuthors ( row, type, full, meta ) {
			var html = '';
			if (!row.allauthors) return '';
			for (i = 0; i < row.allauthors.length; i++) {
				if (i > 0) html += ', ';
				html += row.allauthors[i];
			}
			return html;
		}
		function eventsDropdownType ( row, type, full, meta ) {
			if (type == "sort") {
				return "num";
			}
			return "string";
		}
		function eventsCount ( row, type, full, meta ) {
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
	if (is_front_page() || is_page(array('find-duplicates', 'bibliography', 'sentinel', 'find-conflicts', 'errata', 'host-galaxies', 'graveyard', 'atel', 'frbs', 'mosfit')) || is_search()) {
		wp_enqueue_style( 'transient-table.' . $stem, plugins_url( 'transient-table.' . $stem . '.css', __FILE__), array('datatables-css'));
		wp_enqueue_style( 'datatables-css', plugins_url( "datatables.min.css", __FILE__), array('parent-style'));
		wp_enqueue_script( 'datatables-js', plugins_url( "datatables.min.js", __FILE__), array('jquery') );
		wp_enqueue_script( 'transient-table-js', plugins_url( "transient-table.js", __FILE__), array() );
		wp_enqueue_script( 'suncalc-js', plugins_url( "suncalc.js", __FILE__), array() );
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
