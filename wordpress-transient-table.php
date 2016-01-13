<?php
/**
 * Plugin Name: Wordpress Transient Table
 * Plugin URI: http://astrocrash.net
 * Description: DataTables implementation
 * Version: 1.0.0
 * Author: James Guillochon
 * Author URI: http://astrocrash.net
 * License: GPL2
 */

function sne_catalog() {
	readfile("/var/www/html/sne/sne/catalog.html");
?>
	<script>
	jQuery(document).ready(function() {
		var floatColValDict = {};
		var floatColInds = [];
		var floatSearchCols = ['z', 'Data', 'mmax', 'Mmax', 'v☉ (km/s)', 'dL (Mpc)' ];
		var stringColValDict = {};
		var stringColInds = [];
		var stringSearchCols = ['Name', 'Aliases', 'Host Name', 'Instruments/Bands', 'Claimed Type' ];
        jQuery('#example tfoot th').each( function ( index ) {
            if (index == 0) return;
            var title = jQuery(this).text();
			if (floatSearchCols.indexOf(title) !== -1)
			{
				floatColValDict[index] = title;
				floatColInds.push(index);
			}
			if (stringSearchCols.indexOf(title) !== -1)
			{
				stringColValDict[index] = title;
				stringColInds.push(index);
			}
            jQuery(this).html( '<input class="colsearch" type="text" id="'+title+'" placeholder="'+title+'" />' );
        } );
		jQuery.fn.dataTableExt.oSort['nullable-asc'] = function(a,b) {
			if (a == '')
				return 1;
			else if (b == '')
				return -1;
			else
			{
				var ia = parseFloat(a);
				var ib = parseFloat(b);
				return (ia<ib) ? -1 : ((ia > ib) ? 1 : 0);
			}
		}
		jQuery.fn.dataTableExt.oSort['nullable-desc'] = function(a,b) {
			if (a == '')
				return 1;
			else if (b == '')
				return -1;
			else
			{
				var ia = parseFloat(a);
				var ib = parseFloat(b);
				return (ia>ib) ? -1 : ((ia < ib) ? 1 : 0);
			}
		}
		var table = jQuery('#example').DataTable( {
			ajax: '../../sne/sne-catalog.json',
			columns: [
				{ "defaultContent": "", "responsivePriority": 5 },
				{ "data": "name", "type": "string", "responsivePriority": 1 },
				{ "data": "aliases[, ]", "type": "string" },
				{ "data": "discoverdate", "type": "date" },
				{ "data": "maxdate", "type": "date" },
				{ "data": "maxappmag", "type": "nullable" },
				{ "data": "maxabsmag", "type": "nullable" },
				{ "data": "host", "type": "string" },
				{ "data": "instruments", "type": "string" },
				{ "data": "redshift", "type": "nullable", "responsivePriority": 4 },
				{ "data": "hvel", "type": "nullable" },
				{ "data": "lumdist", "type": "nullable" },
				{ "data": "claimedtype", "type": "string", "responsivePriority": 3 },
				{ "data": "data", "type": "html-num", "responsivePriority": 2 },
				{ "defaultContent": "" },
			],
            dom: 'Bflprti',
            //colReorder: true,
			orderMulti: true,
            pagingType: 'simple_numbers',
            pageLength: 50,
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
                        columns: ':visible:not(:first-child):not(:last-child)'
                    }
                }
            ],
            columnDefs: [ {
                targets: 0,
                orderable: false,
                className: 'select-checkbox'
            }, {
                targets: [ 'aliases', 'maxdate', 'hvel', 'maxabsmag', 'lumdist' ],
				visible: false,
			}, {
				className: 'control',
				orderable: false,
				targets: -1
			} ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 13, "desc" ]]
		} );
		function needAdvanced (str) {
			return (str.indexOf('-') !== -1 || str.indexOf(',') !== -1 || str.indexOf('<') !== -1 || str.indexOf('>') !== -1);
		}
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).keyup( function () {
				if (( floatColInds.indexOf(index) === -1 ) &&
				    ( stringColInds.indexOf(index) === -1 )) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		function advancedStringFilter ( data, id ) {
			var idObj = document.getElementById(id);
			if ( idObj === null ) return true;
			var idString = idObj.value;
			var splitString = idString.split(',');
			for ( var i = 0; i < splitString.length; i++ ) {
				var idStr = splitString[i].trim().toUpperCase();
				if ( idStr === "" || idStr === NaN ) {
					if (i === 0) return true;
				}
				else {
					var lowData = String(data).toUpperCase();
					if ( idStr.indexOf('"') !== -1 ) {
						idStr = idStr.replace(/"/g, '');
						if ( lowData === idStr || (idStr === "" && i === 0) ) return true;
					}
					else {
						if ( lowData.indexOf(idStr) !== -1 ) return true;
					}
				}
			}
			return false;
		}
		function advancedFloatFilter ( data, id ) {
			var idObj = document.getElementById(id);
			if ( idObj === null ) return true;
			var idString = idObj.value;
			var splitString = idString.split(',');
			for ( var i = 0; i < splitString.length; i++ ) {
				if ( splitString[i].indexOf('-') !== -1 )
				{
					var splitRange = splitString[i].split('-');
					var minStr = splitRange[0].replace(/[<=>]/g, '').trim();
					var maxStr = splitRange[1].replace(/[<=>]/g, '').trim();
					var minVal = minStr * 1.0;
					var maxVal = maxStr * 1.0;
					if (minStr !== '') {
						if (!( (minStr !== '' && data < minVal) || (maxStr !== '' && data > maxVal) )) return true;
					}
				}
				var idStr = splitString[i].replace(/[<=>]/g, '').trim();
				if ( idStr === "" || idStr === NaN || idStr === '-' ) {
					if (i === 0) return true;
				}
				else {
					idVal = idStr * 1.0;
					if ( splitString[i].indexOf('<=') !== -1 )
					{
						if ( idVal >= data ) return true;
					}
					else if ( splitString[i].indexOf('<') !== -1 )
					{
						if ( idVal > data ) return true;
					}
					else if ( splitString[i].indexOf('>=') !== -1 )
					{
						if ( idVal <= data ) return true;
					}
					else if ( splitString[i].indexOf('>') !== -1 )
					{
						if ( idVal < data ) return true;
					}
					else
					{
						if ( idStr.indexOf('"') !== -1 ) {
							idStr = String(idStr.replace(/"/g, '').trim());
							if ( data === idStr || (idStr === "" && i === 0) ) return true;
						}
						else {
							if ( data.indexOf(idStr) !== -1 ) return true;
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
				}
				return true;
			}
		);
	} );
	</script>
<?php
}

function transient_table_scripts() {
    wp_enqueue_script( 'datatables-js', "//cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.js", array('jquery') );
	wp_enqueue_style( 'transient-table', plugins_url( 'transient-table.css', __FILE__) );
	wp_enqueue_style( 'datatables-css', 'https://cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.css', array('transient-table') );
}

add_action( 'wp_enqueue_scripts', 'transient_table_scripts' );
?>
