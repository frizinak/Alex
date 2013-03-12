//plugin
function Agenda() {
}
Agenda.prototype.id = null;
Agenda.prototype.generate_form = function (tpl, page, id) {
  this.id = id;
  var days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  var intervals = 20;
  var intervalSize = 30;
  var start = new Date(2011, 2, 2, 7, 30, 0, 0);

  var ret = '<style type="text/css"> ' +
    '.agenda-slot{' +
    'background-color:#ccc;' +
    'cursor:pointer;' +
    'width:30px;' +
    'min-height:10pc;' +
    'padding:1px;' +
    '}' +
    '.agenda-slot:hover{' +
    '/*background-color:#4ab4d2;*/' +
    'border:1px solid #d24a4a;' +
    'padding:0;' +
    '}' +
    '.agenda-slot.active{' +
    'background-color:#d24a4a;' +
    '}' +
    '.agenda-none, .agenda-all{' +
    'color:#4ab4d2;' +
    'text-decoration: none;' +
    '}' +
    '.agenda-tools{' +
    'margin-left:260px' +
    '}' +
    '</style>';

  ret += '<label data-title="' + tpl.description + '">' + tpl.label + '</label><table cellspacing="5" cellpadding="0"><br/><span class="agenda-tools">select: <a href="#" class="agenda-none">none</a> / <a href="#" class="agenda-all">all</a></span>';
  var begin, end;
  var counter = 0;
  for (var h = 0; h < intervals; h++) {
    ret += '<tr>';

    for (var d = 0; d < days.length + 1; d++) {
      if (h === 0) {
        ret += '<th>' + (d > 0 ? days[d - 1] : '') + '</th>';
      } else if (d === 0) {
        begin = new Date(start.getTime() + (intervalSize * 60 * 1000 * h));
        end = new Date(begin.getTime() + (intervalSize * 60 * 1000));
        ret += '<th>' + add_zero(begin.getHours()) + ':' + add_zero(begin.getMinutes()) + ' - ' + add_zero(end.getHours()) + ':' + add_zero(end.getMinutes()) + '</th>';
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
  var that = this;
  $('#' + this.id + ' .agenda-all').unbind().click(function (e) {
    $('#' + that.id + ' .agenda-slot').not('.active').addClass('active');
    return false;
  });

  $('#' + this.id + ' .agenda-none').unbind().click(function (e) {
    $('#' + that.id + ' .agenda-slot.active').removeClass('active');
    return false;
  });

  $('#' + this.id + ' .agenda-slot').unbind().click(function (e) {
    $(e.currentTarget).toggleClass('active');
  });
}

Agenda.prototype.submit_form = function () {
  var ret = '';
  $('#' + this.id + ' .agenda-slot').each(function (i, e) {
    ret += $(e).hasClass('active') ? '1' : '0';
  })
  return ret;
};

App.add_plugin('testAg', Agenda);

//utils
function add_zero(a) {
  if (a < 10) {
    return '0' + a;
  }
  return a;
}
