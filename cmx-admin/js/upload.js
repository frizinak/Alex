"use strict";

function Upload() {

}

Upload.imgExt = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];
Upload.ctrlDown = false;
Upload.init = function () {
    var err = $('.error');
    if (err.length > 0 && err.html().length > 0) {
        notice(err.html(), 1);
        err.html('');
    }
    $('#uploadform input[type=submit]').val(text.UploadSubmit);
    $('#uploadform label').html(text.UploadLabel);
    $('#newfolderform input[type=submit]').val(text.NewDirSubmit);
    $('#newfolderform label').html(text.NewDirLabel);
    $('#filecrumbs span').html(text.FileCrumbs);

    $(window).keydown(function (e) {
        if (e.keyCode === 17) {
            Upload.ctrlDown = true;
        }
    });
    $(window).keyup(function (e) {
        if (e.keyCode === 17) {
            var selected = $('#filesList a[data-url].active');
            if (selected.length > 0) {
                var str = '';
                selected.each(function (i, e) {
                    str += '<p>' + $(e).attr('data-url').substr(3) + '<p>';
                });
                message(str, function () {
                    selected.removeClass('active');
                });
            }
            Upload.ctrlDown = false;
        }
    });

    $('#filesList a[data-url]').hover(function (e) {
            var str, url, ext, img = '', w, h;
            url = $(e.currentTarget).attr('data-url');
            w = $(e.currentTarget).attr('data-width');
            h = $(e.currentTarget).attr('data-height');

            ext = url.split('.');
            ext = ext[ext.length - 1];
            if (Upload.imgExt.indexOf(ext.toLowerCase()) > -1) {
                img = '<img src="' + url + '" /><span>' + (w + 'x' + h) + '</span>';
            }
            $('#imageHover').html(img);

            $('#imageHover').css({'position': 'fixed', 'top': e.clientY + 50 + 'px', 'left': e.clientX + 50 + 'px', 'z-index': 100});
            $('#imageHover img').css({'max-width': ($(window).width() - e.clientX) / 2 + 'px'});
            $('#imageHover span').css({
                'position'        : 'absolute',
                'top'             : 10 + 'px',
                'right'           : 10 + 'px',
                'background-color': 'rgba(50,80,80,0.9)',
                'border-radius'   : '5px',
                'border'          : '1px solid #677',
                'padding'         : '10px',
                'color'           : '#899'
            });

        },
        function (e) {
            $('#imageHover').html('');
        });

    $('#filesList a[data-url]').click(function (e) {
        var str, url, ext, img = '';
        url = $(e.currentTarget).attr('data-url');
        ext = url.split('.');
        ext = ext[ext.length - 1];
        if (Upload.ctrlDown) {
            $(e.currentTarget).toggleClass('active');
        } else {
            if (Upload.imgExt.indexOf(ext.toLowerCase()) > -1) {
                img = '<img src="' + url + '" />';
            }
            str = '<p>' + $(e.currentTarget).attr('data-url').substr(3) + '<p>';
            message(str);
        }
        return false;
    });

    $('.deletefile').click(function (e) {
        var fn = $(e.currentTarget).siblings('a').html()
        confirm('Are you sure you wish to delete' + fn, function (m) {
            if (m === true) {
                if (cmx.delete_file($(e.currentTarget).attr('data-del'))) {
                    window.location.reload()
                } else {
                    notice(text.DeleteFileError, 1);
                }
            }
        });
    })
};

$(document).ready(Upload.init);
