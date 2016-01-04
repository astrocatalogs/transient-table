<?php
/**
 * Plugin Name: JFG Catalog
 * Plugin URI: http://astrocrash.net
 * Description: DataTables implementation
 * Version: 1.0.0
 * Author: James Guillochon
 * Author URI: http://astrocrash.net
 * License: GPL2
 */

function sne_catalog() {
?>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.css"/>
    <script type="text/javascript" src="https://cdn.datatables.net/s/dt/dt-1.10.10,b-1.1.0,b-colvis-1.1.0,b-html5-1.1.0,cr-1.3.0,fh-3.1.0,r-2.0.0,se-1.1.0/datatables.min.js"></script>
<?php
	readfile("/var/www/html/sne/sne/catalog.html");
?>
	<script>
	jQuery(document).ready(function() {
        jQuery('#example tfoot th').each( function ( index ) {
            if (index == 0) return;
            var title = jQuery(this).text();
            if (title == "Plots" || title == "Data" || title == "z" || title == "vHelio") return;
            jQuery(this).html( '<input class="colsearch" type="text" placeholder="'+title+'" />' );
        } );
		var table = jQuery('#example').DataTable( {
			ajax: '../../sne/sne-catalog.json',
            dom: 'Bfrtlip',
            colReorder: true,
            pagingType: 'simple_numbers',
            pageLength: 50,
            responsive: true,
            select: true,
            lengthMenu: [ [10, 50, 250], [10, 50, 250] ],
            deferRender: true,
            autoWidth: false,
            buttons: [
                'selectAll',
                {
                    action: function ( e, dt, button, config ) {
                        table.rows( { filter: 'applied' } ).select();
                    },
                    text: 'Select filtered'
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
                targets: [ 'maxdate' ]
            } ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            order: [[ 2, "desc" ]]
		} );
        table.columns().every( function () {
            var that = this;
     
            jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                if ( that.search() !== this.value ) {
                    that
                        .search( this.value )
                        .draw();
                }
            } );
        } );
	} );
	</script>
<?php
}

?>
