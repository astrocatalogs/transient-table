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
	readfile("/var/www/html/tde/tde/catalog.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['redshift', 'photolink', 'spectralink', 'maxappmag', 'maxabsmag', 'hvel', 'lumdist' ];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['name', 'aliases', 'host', 'instruments', 'claimedtype' ];
		var dateColValDict = {};
		var dateColInds = [];
		var dateSearchCols = [ 'discoverdate', 'maxdate' ];
        jQuery('#example tfoot th').each( function ( index ) {
			var title = jQuery(this).text();
			var classname = jQuery(this).attr('class').split(' ')[0];
			if (classname == 'check' || classname == 'download') return;
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
            jQuery(this).html( '<input class="colsearch" type="text" id="'+classname+'" placeholder="'+title+'" />' );
        } );
		jQuery.extend( jQuery.fn.dataTableExt.oSort, {
			'nullable-pre': function(a) {
				var str = String(a).split(',')[0];
				if (str === '' || str === null) return NaN;
				return parseFloat(str);
			},
			'nullable-str-pre': function(a) {
				var str = String(a).split(',')[0];
				if (str === '' || str === null) return NaN;
				return str;
			},
			'nullable-str-asc': function(a,b) {
				if (a == '' || a == null)
					return 1;
				else if (b == '' || b == null)
					return -1;
				else
					return a.localeCompare(b);
			},
			'nullable-str-desc': function(a,b) {
				if (a == '' || a == null)
					return 1;
				else if (b == '' || b == null)
					return -1;
				else
					return b.localeCompare(a);
			}
		} );
		var table = jQuery('#example').DataTable( {
			ajax: {
				url: '../../tde/catalog.min.json',
				dataSrc: ''
			},
			columns: [
				{ "defaultContent": "", "responsivePriority": 6 },
				{ "data": "name", "type": "string", "responsivePriority": 1 },
				{ "data": "aliases[, ]", "type": "string" },
				{ "data": "discoverdate[0].value", "type": "date" },
				{ "data": "maxdate[0].value", "type": "date" },
				{ "data": "maxappmag[0].value", "type": "nullable" },
				{ "data": "maxabsmag[0].value", "type": "nullable" },
				{ "data": "host[, ].value", "type": "string" },
				{ "data": "snra[, ].value", "type": "string" },
				{ "data": "sndec[, ].value", "type": "string" },
				{ "data": "instruments", "type": "string" },
				{ "data": "redshift[, ].value", "type": "nullable", "responsivePriority": 5 },
				{ "data": "hvel[, ].value", "type": "nullable" },
				{ "data": "lumdist[, ].value", "type": "nullable" },
				{ "data": "claimedtype[, ].value", "type": "string", "responsivePriority": 3 },
				{ "data": "photolink", "responsivePriority": 2 },
				{ "data": "spectralink", "responsivePriority": 2 },
				{ "data": "citations[0].value", "responsivePriority": 2 },
				{ "data": "download", "responsivePriority": 4 },
				{ "defaultContent": "" },
			],
            dom: 'Bfrti',
            //colReorder: true,
			orderMulti: false,
            pagingType: 'simple_numbers',
            pageLength: 250,
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
                targets: [ 'aliases', 'maxdate', 'hvel', 'maxabsmag', 'lumdist', 'snra', 'sndec', 'spectralink' ],
				visible: false,
			}, {
				className: 'control',
				orderable: false,
				targets: -1
			}, {
				targets: [ 'download' ],
				orderable: false
			}, {
				targets: [ 'photolink', 'spectralink' ],
				orderSequence: [ 'desc', 'asc' ],
			}, {
				targets: [ 'photolink', 'spectralink', 'maxdate', 'discoverdate' ],
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

            jQuery( 'input', that.footer() ).keyup( function () {
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 ) &&
					( dateColInds.indexOf(index) === -1 ) ) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		function compDates ( date1, date2, includeSame ) {
			var d1 = new Date(date1);
			var d2 = new Date(date2);
			if (includeSame) {
				return d1.getTime() <= d2.getTime();
			} else {
				return d1.getTime() < d2.getTime();
			}
		}
		function advancedDateFilter ( data, id ) {
			var idObj = document.getElementById(id);
			if ( idObj === null ) return true;
			var idString = idObj.value;
			var splitString = idString.split(/(,|OR)+/);
			var splitData = data.split(/(,|OR)+/);
			for ( var d = 0; d < splitData.length; d++ ) {
				var cData = splitData[d].trim();
				for ( var i = 0; i < splitString.length; i++ ) {
					var idStr = splitString[i].replace(/[<=>]/g, '').trim();
					if ( idStr === "" || idStr === NaN ) {
						if (i === 0) return true;
					}
					else {
						if ( splitString[i].indexOf('<=') !== -1 )
						{
							if ( compDates(cData, idStr, true) ) return true;
						}
						else if ( splitString[i].indexOf('<') !== -1 )
						{
							if ( compDates(cData, idStr, false) ) return true;
						}
						else if ( splitString[i].indexOf('>=') !== -1 )
						{
							if ( compDates(idStr, cData, true) ) return true;
						}
						else if ( splitString[i].indexOf('>') !== -1 )
						{
							if ( compDates(idStr, cData, false) ) return true;
						}
						else
						{
							if ( idStr.indexOf('"') !== -1 ) {
								idStr = String(idStr.replace(/"/g, '').trim());
								if ( cData === idStr || (idStr === "" && i === 0) ) return true;
							}
							else {
								if ( cData.indexOf(idStr) !== -1 ) return true;
							}
						}
					}
				}
			}
			return false;
		}
		function advancedStringFilter ( data, id ) {
			var idObj = document.getElementById(id);
			if ( idObj === null ) return true;
			var idString = idObj.value;
			var splitString = idString.split(/(,|OR)+/);
			var splitData = data.split(/(,|OR)+/);
			for ( var d = 0; d < splitData.length; d++ ) {
				var cData = splitData[d].trim();
				for ( var i = 0; i < splitString.length; i++ ) {
					var idStr = splitString[i].trim().toUpperCase();
					if ( idStr === "" || idStr === NaN ) {
						if (i === 0) return true;
					}
					else {
						var lowData = String(cData).toUpperCase();
						if ( idStr.indexOf('"') !== -1 ) {
							idStr = idStr.replace(/"/g, '');
							if ( lowData === idStr || (idStr === "" && i === 0) ) return true;
						}
						else {
							if ( lowData.indexOf(idStr) !== -1 ) return true;
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
			var splitString = idString.split(/(,|OR)+/);
			var splitData = data.split(/(,|OR)+/);
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
							if (!( (minStr !== '' && cVal < minVal) || (maxStr !== '' && cVal > maxVal) )) return true;
						}
					}
					var idStr = splitString[i].replace(/[<=>]/g, '').trim();
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
								if ( cData === idStr || (idStr === "" && i === 0) ) return true;
							}
							else {
								if ( cData.indexOf(idStr) !== -1 ) return true;
							}
						}
					}
				}
			}
			return false;
		}
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				for ( var i = 0; i < aData.length; i++ )
				{
					if ( floatColInds.indexOf(i) !== -1 ) {
						if ( !advancedFloatFilter( aData[i], floatColValDict[i] ) ) return false;
					}
					if ( stringColInds.indexOf(i) !== -1 ) {
						if ( !advancedStringFilter( aData[i], stringColValDict[i] ) ) return false;
					}
					if ( dateColInds.indexOf(i) !== -1 ) {
						if ( !advancedDateFilter( aData[i], dateColValDict[i] ) ) return false;
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
	wp_enqueue_style( 'transient-table', plugins_url( 'transient-table.css', __FILE__) );
	//wp_enqueue_style( 'datatables-css', 'https://cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.css', array('transient-table') );
    wp_enqueue_script( 'datatables-js', plugins_url( "datatables.min.js", __FILE__), array('jquery') );
	wp_enqueue_style( 'datatables-css', plugins_url( "datatables.min.css", __FILE__), array('transient-table') );
}

add_action( 'wp_enqueue_scripts', 'transient_table_scripts' );
?>
