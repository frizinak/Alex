function Agenda() {
}
Agenda.prototype.id = null;
Agenda.prototype.generate_form = function (tpl, page, id) {
    this.id = id;
    var days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    var hours = 10;
    var start = new Date(2011, 2, 2, 14, 0, 0, 0);

    var ret = '<style type="text/css"> ' +
        '.agenda-slot{' +
        'background-color:#ccc;' +
        'cursor:pointer;' +
        'width:10px;' +
        'height:10px;'+
        '}' +
        '.agenda-slot:hover{' +
        'background-color:#4ab4d2;' +
        '}' +
        '' +
        '.agenda-slot.active{' +
        'background-color:#d24a4a;' +
        '}' +
        '' +
        '</style>';

    ret += '<label data-title="' + tpl.description + '">' + tpl.label + '</label><table cellspacing="10" cellpadding="10">';

    var counter = 0;
    for (var h = 0; h < hours; h++) {
        ret += '<tr>';

        for (var d = 0; d < days.length + 1; d++) {
            if (h === 0) {
                ret += '<th>' + (d > 0 ? days[d - 1] : '') + '</th>';
            } else if (d === 0) {
                var t = new Date(start.getTime() + (30 * 60 * 1000 * h));
                ret += '<th>' + t.getHours() + ':' + (t.getMinutes() < 10 ? '0' + t.getMinutes() : t.getMinutes()) + '</th>';
            } else {
                var active = page.substr(counter, 1) === '1';
                ret += '<td class="agenda-slot' + (active ? ' active' : '') + '"></td>';
                counter++;

            }

        }

        ret += '</tr>';
    }
    ret += '</table>';

    return ret;

};

Agenda.prototype.generated_form = function () {
    $('#' + this.id + ' .agenda-slot').unbind().click(function (e) {
        $(e.currentTarget).toggleClass('active');
    });
}

Agenda.prototype.submit_form = function () {
    ret = '';
    $('#' + this.id + ' .agenda-slot').each(function (i, e) {
        ret += $(e).hasClass('active') ? '1' : '0';
    })
    return ret;
};

App.add_plugin('testAg', Agenda);

