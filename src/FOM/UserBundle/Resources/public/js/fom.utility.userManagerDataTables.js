/**
 *
 * @author David Patzke>
 * @copyright 11.12.17 by WhereGroup GmbH & Co. KG
 */


$(document).ready(function($) {

    $.fn.dataTable.ext.errMode = 'throw';
        $('#listFilterGroups').DataTable( {
            "serverSide": true,
            "ajax": "user/search",
            "ordering": false,
            "info":     true,
            "pagingType" : "full_numbers",
            "language": {
            "decimal":        "",
            "emptyTable":     Mapbender.trans('fom.user.user.index.datatable.emptytable'),
            "info":           Mapbender.trans('fom.user.user.index.datatable.info'),
            "infoEmpty":      Mapbender.trans('fom.user.user.index.datatable.infoempty'),
            "infoFiltered":   "",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":      Mapbender.trans('fom.user.user.index.datatable.lenghtmenu'),
            "loadingRecords":  Mapbender.trans('fom.user.user.index.datatable.loadingrecords'),
            "processing":      Mapbender.trans('fom.user.user.index.datatable.processing'),
            "search":          Mapbender.trans('fom.user.user.index.datatable.search'),
            "zeroRecords":     Mapbender.trans('fom.user.user.index.datatable.zerorecords')

    },
        "columns": [
            { "data": "username" },
            { "data": "email" },
            { "data": "groups", render: "[, ].title" },
            {
                "data":      null,
                "className": "tdsmall",
                render:      function(data, type, row) {
                    var href = data.editPath;
                    var span = $('<a />').addClass('iconEdit iconSmall"').attr('href',href).attr('title', '').wrap('<div></div>').parent().html();
                    return (data.editPath !== '') ? span : '';
                }
            },
            {
                "data": null,
                "className":"tdsmall",
                render: function ( data, type, row ) {
                    var url =  data.deletePath;
                    var title  = 'Delete ' + data.username;
                    var span = $('<span />').addClass('iconRemove iconSmall"').attr('title', title).attr('data-url', url).attr('data-id',data.id).wrap('<div></div>').parent().html();;

                    return data.deletePath !== '' ? span : '';

                }
            }
        ]
    });

});