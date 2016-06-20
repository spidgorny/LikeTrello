/// <reference path="../typings/index.d.ts" />
var LikeTrello = (function () {
    function LikeTrello() {
    }
    LikeTrello.prototype.initSortable = function () {
        var _this = this;
        $(".column .inside").sortable({
            connectWith: ".column .inside",
            handle: ".portlet-header",
            cancel: ".portlet-toggle",
            start: function (event, ui) {
                ui.item.addClass('tilt');
                _this.tilt_direction(ui.item);
            },
            stop: function (event, ui) {
                ui.item.removeClass("tilt");
                $("html").unbind('mousemove', ui.item.data("move_handler"));
                ui.item.removeData("move_handler");
                // console.log(event, ui, $(event.target).prop('id'),
                // 	ui.item.prop('id'),
                // 	ui.item.closest('.inside').prop('id'));
                var $reloadTarget = $('#reloadTarget');
                $reloadTarget.load('plugin.php', {
                    page: $reloadTarget.attr('data-href'),
                    action: 'move',
                    issue: ui.item.prop('id'),
                    from: $(event.target).prop('id'),
                    to: ui.item.closest('.inside').prop('id')
                }, function () {
                    _this.initSortable();
                });
            }
        });
    };
    LikeTrello.prototype.tilt_direction = function (item) {
        var left_pos = item.position().left, move_handler = function (e) {
            if (e.pageX >= left_pos) {
                item.addClass("rightTilt");
                item.removeClass("leftTilt");
            }
            else {
                item.addClass("leftTilt");
                item.removeClass("rightTilt");
            }
            left_pos = e.pageX;
        };
        $("html").bind("mousemove", move_handler);
        item.data("move_handler", move_handler);
    };
    LikeTrello.prototype.initAddIssue = function () {
        var _this = this;
        $('.btn-floating').click(function () {
            $("#add-issue").dialog({
                title: 'Adding new issue quickly',
                autoOpen: true,
                modal: true,
                width: '90%',
                buttons: {
                    "Add Issue": _this.addIssueOK
                }
            });
        });
    };
    LikeTrello.prototype.addIssueOK = function () {
        console.log('addIssueOK');
        $("#add-issue").submit();
    };
    return LikeTrello;
}());
/*
$( ".portlet" )
    .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
    .find( ".portlet-header" )
    //.addClass( "ui-widget-header ui-corner-all" )
    .prepend( "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");

$( ".portlet-toggle" ).click(function() {
    var icon = $( this );
    icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
    icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
});
*/
var lt = new LikeTrello();
lt.initSortable();
lt.initAddIssue();
