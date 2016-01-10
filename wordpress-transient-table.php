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
		var colValDict = {};
		var colInds = [];
		var advSearchCols = ['z', 'Data'];
        jQuery('#example tfoot th').each( function ( index ) {
            if (index == 0) return;
            var title = jQuery(this).text();
            if (title == "Plots" || title == "vHelio") return;
			if (advSearchCols.indexOf(title) !== -1)
			{
				colValDict[index] = title;
				colInds.push(index);
			}
            jQuery(this).html( '<input class="colsearch" type="text" id="'+title+'" placeholder="'+title+'" />' );
        } );
		var table = jQuery('#example').DataTable( {
			ajax: '../../sne/sne-catalog.json',
            dom: 'Bfrtlip',
            //colReorder: true,
            pagingType: 'simple_numbers',
            pageLength: 50,
            responsive: true,
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
                    columns: ':not(:first-child)'
                },
                {
                    extend: 'csv',
                    text: 'Export selected to CSV',
                    exportOptions: {
                        modifier: { selected: true },
                        columns: ':visible:not(:first-child)'
                    }
                }
            ],
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            }, {
                visible: false,
                targets: [ 'aliases', 'maxdate', 'hvel' ]
            } ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 10, "desc" ]]
		} );
		function needAdvanced (str) {
			return (str.indexOf('-') !== -1 || str.indexOf(',') !== -1 || str.indexOf('<') !== -1 || str.indexOf('>') !== -1);
		}
        table.columns().every( function ( index ) {
            var that = this;

            jQuery( 'input', that.footer() ).keyup( function () {
				if ( colInds.indexOf(index) === -1 ) {
					if ( that.search() !== this.value ) {
						that.search( this.value );
					}
				}
				that.draw();
            } );
        } );
		function advancedFloatFilter ( data, id ) {
			var idObj = document.getElementById(id);
			if ( idObj === null ) return true;
			var idString = idObj.value;
			var splitString = idString.split(',');
			for ( var i = 0; i < splitString.length; i++ ) {
				if ( splitString[i].indexOf('-') !== -1 )
				{
					var splitRange = splitString[i].split('-');
					var minStr = splitRange[0].trim();
					var maxStr = splitRange[1].trim();
					var minVal = minStr * 1.0;
					var maxVal = maxStr * 1.0;
					if ( (minStr !== '' && data < minVal) || (maxStr !== '' && data > maxVal) ) return false;
				}
				else
				{
					var idStr = splitString[i].replace(/[<>]/g, '').trim();
					if ( idStr === "" || idStr === NaN ) continue;
					idVal = idStr * 1.0;
					if ( splitString[i].indexOf('<') !== -1 )
					{
						if ( idVal <= data ) return false;
					}
					else if ( splitString[i].indexOf('>') !== -1 )
					{
						if ( idVal >= data ) return false;
					}
					else
					{
						if ( data.indexOf(idStr) === -1 ) return false;
					}
				}
			}
			return true;
		}
		jQuery.fn.dataTable.ext.search.push(
			function( oSettings, aData, iDataIndex ) {
				for ( var i = 0; i < aData.length; i++ )
				{
					if ( colInds.indexOf(i) === -1 ) continue;
					if ( !advancedFloatFilter( aData[i], colValDict[i] ) ) return false;
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
