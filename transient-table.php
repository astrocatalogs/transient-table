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

function transient_catalog() {
	readfile("/var/www/html/sne/sne/catalog.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['redshift', 'photolink', 'spectralink', 'maxappmag', 'maxabsmag', 'velocity', 'lumdist'];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['name', 'aliases', 'host', 'instruments', 'claimedtype'];
		var raDecColValDict = {};
		var raDecColInds = [];
		var raDecSearchCols = ['ra', 'dec'];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ 'discoverdate', 'maxdate' ];
		function dateRender ( data, type, row ) {
			if ( type === 'sort' ) {
				if (data === '' || data === null || typeof data !== 'string') return NaN;
				var d = new Date(data)
				return d.getTime();
			}
			return data;
		}
		function raDecRender ( data, type, row ) {
			if ( type === 'sort' ) {
				if (data === '' || data === null || typeof data !== 'string') return NaN;
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
			return data;
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
		function myIsNaN ( val ) {
			return (val != val);
		}
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (classname == 'check' || classname == 'download' || classname == 'references') return;
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
            jQuery(this).html( '<input class="colsearch" type="text" id="'+classname+'" placeholder="'+title+'" />' );
        } );
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
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '../../sne/catalog.min.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": "name", "type": "string", "responsivePriority": 1 },
				{ "data": "aliases[, ]", "type": "string" },
				{ "data": "discoverdate.0.value", "type": "non-empty-float", "defaultContent": "", "render": dateRender, "responsivePriority": 2 },
				{ "data": "maxdate.0.value", "type": "non-empty-float", "defaultContent": "", "render": dateRender },
				{ "data": "maxappmag.0.value", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": "maxabsmag.0.value", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": "host[, ].value", "type": "html", "width": "20%" },
				{ "data": "ra.0.value", "type": "non-empty-float", "defaultContent": "", "render": raDecRender },
				{ "data": "dec.0.value", "type": "non-empty-float", "defaultContent": "", "render": raDecRender },
				{ "data": "instruments", "type": "string", "defaultContent": "" },
				{ "data": "redshift.0.value", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender, "responsivePriority": 5 },
				{ "data": "velocity.0.value", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": "lumdist.0.value", "type": "non-empty-float", "defaultContent": "", "render": noBlanksNumRender },
				{ "data": "claimedtype[, ].value", "type": "string", "responsivePriority": 3 },
				{ "data": "photolink", "responsivePriority": 2 },
				{ "data": "spectralink", "responsivePriority": 2 },
				{ "data": "references", "type": "html", "searchable": false },
				{ "data": "download", "responsivePriority": 4 },
				{ "defaultContent": "" },
			],
            dom: 'Bflprtip',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 50,
			searchDelay: 300,
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
                        columns: ':visible:not(:first-child):not(:last-child):not(:nth-last-child(2))'
                    }
                }
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
			}, {
                targets: [ 'aliases', 'maxdate', 'velocity', 'maxabsmag', 'references', 'instruments', 'lumdist' ],
				visible: false
			}, {
				targets: [ 'download', 'spectralink', 'photolink' ],
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
				targets: [ 'photolink', 'spectralink' ],
				orderSequence: [ 'desc', 'asc' ]
			}, {
				targets: [ 'maxdate', 'discoverdate' ],
				className: 'nowrap'
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 15, "desc" ]]
		} );
		function needAdvanced (str) {
			var advancedStrs = ['-', 'OR', ',', '<', '>', '='];
			return (advancedStrs.some(function(v) { return str === v; }));
		}
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).keyup( function (e) {
				var code = (e.keyCode || e.which);

				// do nothing if it's an arrow key
				if(code == 37 || code == 38 || code == 39 || code == 40) {
					return;
				}
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
		function compDates ( date1, date2, includeSame ) {
			var d1;
			var d1split = date1.split('/');
			if (d1split.length == 1) {
				d1 = new Date(date1 + '/12/31');
			} else if (d1split.length == 2) {
				var daysInMonth = new Date(d1split[0], d1split[1], 0).getDate();
				d1 = new Date(date1 + '/' + String(daysInMonth));
			} else {
				d1 = new Date(date1);
			}
			var d2;
			var d2split = date2.split('/');
			if (d2split.length == 1) {
				d2 = new Date(date2 + '/12/31');
			} else if (d2split.length == 2) {
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
			if (rd1split.length == 1) {
				rd1 = parseFloat(rd1split[0]);
			} else if (rd1split.length == 2) {
				rd1 = parseFloat(rd1split[0]) + parseFloat(rd1split[1])/60.;
			} else {
				rd1 = parseFloat(rd1split[0]) + parseFloat(rd1split[1])/60. + parseFloat(rd1split[2])/3600.;
			}
			if (rd2split.length == 1) {
				rd2 = parseFloat(rd2split[0]);
			} else if (rd2split.length == 2) {
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
			for ( var d = 0; d < splitData.length; d++ ) {
				var cData = splitData[d].trim();
				for ( var i = 0; i < splitString.length; i++ ) {
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
			for ( var d = 0; d < splitData.length; d++ ) {
				var cData = splitData[d].trim();
				for ( var i = 0; i < splitString.length; i++ ) {
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
			var splitData = data.split(/(?:,|OR)+/);
			for ( var d = 0; d < splitData.length; d++ ) {
				var cData = String(splitData[d]).trim();
				for ( var i = 0; i < splitString.length; i++ ) {
					var idStr = splitString[i].trim().toUpperCase();
					var isNot = (idStr.indexOf('!') !== -1 || idStr.indexOf('NOT') !== -1)
					idStr = idStr.replace(/!/g, '');
					if ( idStr === "" || idStr === NaN ) {
						if (i === 0) return true;
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
			var isNot = (idString.indexOf('!') !== -1 || idString.indexOf('NOT') !== -1)
			idString = idString.replace(/!/g, '');
			var splitString = idString.split(/(?:,|OR)+/);
			var splitData = data.split(/(?:,|OR)+/);
			for ( var d = 0; d < splitData.length; d++ ) {
				var cData = splitData[d].trim();
				var cVal = cData*1.0;
				for ( var i = 0; i < splitString.length; i++ ) {
					if ( splitString[i].indexOf('-') !== -1 )
					{
						var splitRange = splitString[i].split('-');
						var minStr = splitRange[0].replace(/[<=>]/g, '').trim();
						var maxStr = splitRange[1].replace(/[<=>]/g, '').trim();
						var minVal = minStr*1.0;
						var maxVal = maxStr*1.0;
						if (minStr !== '') {
							if (!( (minStr !== '' && cVal < minVal) || (maxStr !== '' && cVal > maxVal) )) return !isNot;
						}
					}
					var idStr = splitString[i].replace(/[<=>]/g, '').trim();
					if ( idStr === "" || idStr === NaN || idStr === '-' ) {
						if (i === 0) return !isNot;
					}
					else {
						idVal = idStr*1.0;
						if ( splitString[i].indexOf('<=') !== -1 )
						{
							if ( idVal >= cVal ) return !isNot;
						}
						else if ( splitString[i].indexOf('<') !== -1 )
						{
							if ( idVal > cVal ) return !isNot;
						}
						else if ( splitString[i].indexOf('>=') !== -1 )
						{
							if ( idVal <= cVal ) return !isNot;
						}
						else if ( splitString[i].indexOf('>') !== -1 )
						{
							if ( idVal < cVal ) return !isNot;
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

function transient_table_scripts() {
    //wp_enqueue_script( 'datatables-js', "//cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.js", array('jquery') );
	if (is_front_page()) {
		wp_enqueue_script( 'transient-table-js', plugins_url( "transient-table.js", __FILE__) );
		wp_enqueue_style( 'transient-table', plugins_url( 'transient-table.css', __FILE__) );
		wp_enqueue_script( 'datatables-js', plugins_url( "datatables.min.js", __FILE__), array('jquery') );
		wp_enqueue_style( 'datatables-css', plugins_url( "datatables.min.css", __FILE__), array('transient-table') );
	}
	//wp_enqueue_style( 'datatables-css', 'https://cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.css', array('transient-table') );
}

add_action( 'wp_enqueue_scripts', 'transient_table_scripts' );
?>
