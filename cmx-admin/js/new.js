"use strict";
String.prototype.rpad = function (padString, length) {
  var str = this;
  while (str.length < length) {
    str += padString;
  }
  return str;
};
function AddRem() {

}

AddRem.allTpls = null;
AddRem.allPages = null;
AddRem.pagesSelect = null;

AddRem.init = function () {
  $('#pageDescr').html(text.PageDescrNew);

  AddRem.reload();
};

AddRem.reload = function () {
  var key;
  AddRem.allTpls = cmx.get_all_tpls_by_parent("_root");
  cmx.pageTree = cmx.get_page_tree();
  if (cmx.pageTree === false || AddRem.allTpls === false) {
    notice(text.PageTreeError, 1);
    return;
  }
  AddRem.allPages = [];
  AddRem.pagesSelect = [];
  (function flatten(parent, indent) {
    for (key in parent.subpages) {
      if (parent.subpages.hasOwnProperty(key)) {
        AddRem.allPages.push(parent.subpages[key].page);
        AddRem.pagesSelect.push([indent + parent.subpages[key].page, parent.subpages[key].page]);
        if (parent.subpages[key].hasOwnProperty('subpages')) {
          var newindent = "".rpad('&nbsp;', indent.length + 12);
          flatten(parent.subpages[key], newindent + '|_');
        }
      }
    }
  }(cmx.pageTree.pages, ''));
  $('#page-form').empty();
  AddRem.generate_form();
};

AddRem.generate_form = function () {
  var html, key;
  html = '<label data-title="' + text.LblPageNameHelp + '">' + text.LabelPageName + '</label><br/>';
  html += '<input id="pagename" type="text" /><br/>';
  html += '<label data-title="' + text.LblPageParentHelp + '">' + text.LabelParent + '</label>';
  html += '<select id="parentname"><option value="_root">' + text.NoParent + '</option>';
  for (key in AddRem.pagesSelect) {
    html += '<option value="' + AddRem.pagesSelect[key][1] + '">' + AddRem.pagesSelect[key][0] + '</option>';
  }
  html += '</select>';

  html += '<label data-title="' + text.LblTplHelp + '">' + text.LabelTemplate + '</label>';
  html += '<select id="tplfile" name="parent">';
  for (key in AddRem.allTpls) {
    html += '<option>' + AddRem.allTpls[key] + '</option>';
  }
  html += '</select><br/>';
  html += '<a id="submitnewform" href="#">' + text.CreateBtn + '</a><div class="clear"></div>';
  $('#page-form').html(html);
  $('#page-form #parentname').change(AddRem.update_templates);
  $('#submitnewform').click(AddRem.submit_form);
  $('label[data-title]').unbind().click(function (e) {
    notice($(e.currentTarget).attr('data-title'));
  });

};

AddRem.update_templates = function () {
  var pn = $('#page-form #parentname').val(), key, html = '', tplSelect = $('#tplfile'), submit = $('#submitnewform');
  AddRem.allTpls = cmx.get_all_tpls_by_parent(pn);
  for (key in AddRem.allTpls) {
    html += '<option>' + AddRem.allTpls[key] + '</option>';
  }
  if (html.length > 1) {
    tplSelect.show().html(html);
    tplSelect.siblings('label').last().html(text.LabelTemplate);
    submit.show();
  } else {
    tplSelect.hide().html('');
    tplSelect.siblings('label').last().html(text.TplChildError);
    submit.hide();
  }
};

AddRem.submit_form = function () {
  var pagename, filename;
  notice(text.Saving);
  pagename = safe_page_name($('#pagename').val().trim());
  if (pagename === '' || $('#tplfile').val() === '') {
    notice(text.PageNameEmpty, 1);
    AddRem.reload();
    return false;
  }

  if (AddRem.allPages.indexOf(pagename) !== -1) {
    notice(text.PageNameDupe, 1);
    AddRem.reload();
    return false;
  }

  filename = cmx.create_page(pagename, $('#tplfile').val());
  if (filename !== false) {
    notice(text.PageFileCreated);
    AddRem.insert_into_page_tree(pagename, filename)
  } else {
    notice(text.PageCreateError, 1);
  }

  AddRem.reload();
  return false;
};

AddRem.insert_into_page_tree = function (pagename, file) {
  var parent, parentName = $('#parentname').val();
  cmx.pageTree = cmx.get_page_tree();
  if (cmx.pageTree === false) {
    notice(text.PageTreeError, 1);
    AddRem.reload();
    return false;
  }
  parent = parentName === "_root" ? cmx.pageTree.pages : cmx.get_page_in_tree(parentName);
  if (parent === undefined) {
    notice(text.GetPageError, 1);
    AddRem.reload();
    return false;
  }

  if (!parent.hasOwnProperty('subpages')) {
    parent.subpages = [];
  }
  parent.subpages.push(
    {
      'page' : pagename,
      'file' : file,
      'cache': "true",
      'menu' : "true"
    }
  );

  if (cmx.set_page_tree(cmx.pageTree) !== false) {
    notice(text.PageCreated);
    AddRem.reload();
    return true;
  }

  notice(text.PageCreateStructureError, 1);
  AddRem.reload();
};

$(document).ready(AddRem.init);
