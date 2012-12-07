function Image() {
}

Image.prototype.id = null;
Image.prototype.imgList = null;
Image.prototype.multi = false;
Image.prototype.fixed = 0;

Image.prototype.generate_form = function (tpl, page, id) {
    this.id = id;

    var that = this;
    $.ajax({
        type   : 'GET',
        url    : 'imgs.php',
        data   : {'non-mce': 1},
        async  : false,
        success: function (a, b) {
            if (b === "success") {
                that.imgList = [['None','../None']].concat(a);
            } else {
                notice('An error has occurred in Image plugin.');
            }
        },
        error  : function (a, b, c) {
            notice('An error has occurred in Image plugin.');
        }
    });

    this.multi = tpl.multiple;

    var ret = '<label style="display:block; clear:both;" data-title="' + tpl.description + '">' + tpl.label + '</label>';
    if (typeof page === 'undefined' || typeof page === 'string' || page.length < 1) {
        page = [
            ['', '', '']
        ];
    }

    if (this.multi !== 'dynamic') {
        while (page.length < parseInt(this.multi)) {
            page.push(['', '', '']);
        }
    }

    for (var i = 0; i < page.length; i++) {
        ret += this.generate_fields(page[i]);

    }

    ret += '<div style="clear:both;"></div>';
    if (this.multi === 'dynamic') {
        ret += '<a href="#" class="addImage" style="display:block; margin-bottom: 40px;">add more images</a>';
    }
    return ret;

};

Image.prototype.generate_fields = function (page) {
    var ret = '<div class="img" style="background-color:#d6d5c2; padding:20px; margin:10px 20px; width:250px; float:left;"><label>image</label>';
    if (this.multi === 'dynamic') {
        ret += '<a href="#" class="removeImage" style="float:right;">remove</a>';
    }
    ret += '<select>';
    for (var i = 0; i < this.imgList.length; i++) {
        var selected = this.imgList[i][0] == page[0];
        ret += '<option value="' + this.imgList[i][0] + '"' + (selected ? ' selected="selected"' : '') + '>' + this.imgList[i][0] + '</option>';
    }
    ret += '</select>';
    ret += '<label>img title</label><input type="text" value="' + page[1] + '"/>'
    ret += '<label>alternate text</label><input type="text" value="' + page[2] + '"/></div>'
    return ret;
};

Image.prototype.generated_form = function () {

    var that = this;
    /*$('#' + this.id + ' .img').unbind().hover(function (e) {
        var url = '../'+$(e.currentTarget).find('select').val();
        $(e.currentTarget).css({'background-image': 'url("' + url + '")'});
    }, function (e) {
        $(e.currentTarget).css({'background-image': 'none'});
    });*/
    $('#' + this.id + ' .removeImage').unbind().click(function (e) {
        if ($('#' + that.id + ' .removeImage').length > 1) {
            $(e.currentTarget).parent().remove();
        }
        that.generated_form();
        return false;
    });

    $('#' + this.id + ' .addImage').unbind().click(function (e) {
        $('#' + that.id + ' .img').last().after(that.generate_fields(['', '', '']));
        that.generated_form();
        return false;
    });
}

Image.prototype.submit_form = function () {
    var fields = $('#' + this.id + ' .img'), ret = []
    fields.each(function (i, e) {
        ret.push([$(e).find('select').val(), $(e).find('input:eq(0)').val(), $(e).find('input:eq(1)').val()])
    });
    return ret;
};

App.add_plugin('image', Image);
