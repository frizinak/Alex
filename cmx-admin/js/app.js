"use strict";

var cmx = new Cmx();
function App() {
}

App.plugins = {};

App.add_plugin = function (type, plugin) {
  App.plugins[type] = plugin;
};

App.init = function () {
  $('#clearcache').html(text.ClearCache);
  $('#tabcontent').html(text.TabContent);
  $('#tabnew').html(text.TabNew);
  $('#tabupload').html(text.TabUpload);
  $('#tabdb').html(text.TabDb);

  $('#logout').html(text.Logout);

  $('#nojs').hide();
  $('#container').show();
  $('#clearcache').click(function () {
    if (cmx.clear_cache()) {
      notice(text.CacheClear, 0);
    } else {
      notice(text.CacheClearError, 1);
    }
    return false;
  });
  $('#labeltip').fadeTo(0, 0);

  /*$('#labeltip').click(function (e) {
   $(e.currentTarget).hide();
   });*/
};

function htmlentities(str) {
  var ret = String(str).replace(/&/g, '&amp;');
  ret = ret.replace(/</g, '&lt;');
  ret = ret.replace(/>/g, '&gt;');
  return ret.replace(/"/g, '&quot;');
}

function safe_page_name(str) {
  var regex = /\/|\\|&|\+|,|:|;|\=|\?|@| |'|"|<|>|#|%|\{|\}|\||\^|~|\[|\]/g;
  return String(str).replace(regex, '-');
}

if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
  };
}

function notice(str, critical, timeout) {
  var labelTip = $('#labeltip');
  if (str == '_HIDE') {
    labelTip.stop(true, true).fadeTo(250, 0);
    return;
  }
  timeout = timeout || 5000;
  for (var i = 3; i < arguments.length; i++) {
    str = str.replace('{' + (i - 2) + '}', arguments[i]);
  }
  labelTip.html(str);
  labelTip.stop(true, true).fadeTo(200, 1).delay(timeout).fadeTo(2000, 0);
  labelTip.attr('class', critical ? 'critical' : '');
}

function confirm(str, callback) {
  var box = $('#message'), layer = $('#layer');
  $(window).resize();
  layer.show().css({'position': 'fixed', 'top': 0, 'left': 0, 'z-index': 10000});
  layer.width($(window).width());
  layer.height($(window).height());

  box.html(str + '<div class="clear"></div><a href="#" id="confirmyes" class="confirmbtn" >' + text.YesBtn + '</a><a href="#" id="confirmno" class="confirmbtn" >' + text.NoBtn + '</a>');
  box.show().width('230').css({'position': 'fixed', 'top': ($(window).height() / 2 - box.height() / 2) + 'px', 'left': ($(window).width() / 2 - box.width() / 2) + 'px', 'z-index': 10001});
  $('.confirmbtn').click(function (e) {
    $('.confirmbtn').unbind();
    layer.hide();
    box.hide();
    callback($(e.currentTarget).attr('id') === 'confirmyes');
    $(window).resize();
    return false;
  });
  $(window).resize();

}

function message(str, callback) {
  var box = $('#message'), layer = $('#layer');
  $(window).resize();
  layer.show().css({'cursor': 'pointer', 'position': 'fixed', 'top': 0, 'left': 0, 'z-index': 10000});
  layer.width($(window).width());
  layer.height($(window).height());

  box.html(str);
  box.show().width('230').css({'position': 'fixed', 'top': ($(window).height() / 2 - box.height() / 2) + 'px', 'left': ($(window).width() / 2 - box.width() / 2) + 'px', 'z-index': 10001});
  layer.click(function (e) {
    layer.unbind();
    layer.hide();
    box.hide();
    if (callback !== undefined) {
      callback();
    }
    $(window).resize();
    return false;
  });
  $(window).resize();
}

$(document).ready(App.init);







