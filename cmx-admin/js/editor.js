"use strict";

//var cmx = new Cmx();

function Editor() {
}

Editor.currentPage = null;
Editor.currentPageName = null;
Editor.currentPageFile = null;
Editor.currentPageStructure = null;
Editor.currentPageStructureIndex = null;
Editor.currentPageTpl = null;
Editor.currentPageParent = null;
Editor.pageListOpened = {};
Editor.allPages = [];
Editor.pagesLocked = true;
Editor.sortableInitiated = false;

Editor.plugins = [];

Editor.init = function () {
    $('#pageDescr').html(text.PageDescrEdit);
    $('#editor-form').html(text.Intro);
    //$('#editor-form').hide().submit(Editor.submit_editor);
    $('#editor-form').submit(Editor.submit_editor);
    $('#pages').html(text.LoadingMsg);
    $(window).resize(function () {
        var newWidth = $('#main').width() - $('#pages').outerWidth(true) - 5;
        $('#editor').width(newWidth > 370 ? newWidth : 370);
    });
    $(window).resize();

    Editor.pageListOpened = JSON.parse($('#treeOpened').html());
    Editor.reload();
};

Editor.reload = function () {
    $('#pages').html(text.LoadingMsg);
    cmx.pageTree = cmx.get_page_tree();
    if (cmx.pageTree === false) {
        $('#pages').html('');
        notice(text.PageTreeError, 1);
        return;
    }

    Editor.make_page_list();
};

Editor.make_page_list = function () {
    var pageLock;
    Editor.allPages = [];
    $('#pages').empty().html('<a href="#" id="pagelock" title="' + text.LockTitle + '" class="' + (Editor.pagesLocked ? 'locked' : 'unlocked') + '"></a>' + Editor.pages_list(cmx.pageTree.pages));
    pageLock = $('#pagelock');
    pageLock.click(function (e) {
        Editor.pagesLocked = !Editor.pagesLocked;
        //pageLock.html(Editor.pagesLocked ? 'locked' : 'unlocked');
        pageLock.attr('class', (Editor.pagesLocked ? 'locked' : 'unlocked'));
        Editor.enable_order(!Editor.pagesLocked);
        return false;
    });
    Editor.enable_order(!Editor.pagesLocked);

    $('.sublist.closed').hide();
    $('.page').click(Editor.page_click);
    $('.sub').click(Editor.page_sub_click);
    if (Editor.currentPageFile !== null && Editor.currentPageFile !== undefined) {
        $('.page[name="' + Editor.currentPageFile + '"]').click();
    }
};

Editor.enable_order = function (enable) {
    if (enable === false) {
        if (Editor.sortableInitiated) {
            $('#pages, #pages ul').sortable('destroy');
            Editor.sortableInitiated = false;
        }
    } else {
        $('#pages, #pages ul').sortable({
            items           : ">li",
            axis            : 'y',
            scrollSpeed     : 5,
            tolerance       : 'pointer',
            toleranceElement: 'a.page',
            opacity         : 0.5,
            update          : Editor.order_changed,
            forceHelperSize : true,
            dropOnEmpty     : true
        });
        Editor.sortableInitiated = true;
    }
};

Editor.order_changed = function (a, b) {
    var parentEl, pageEl, parent, page, newIndex, oldIndex, parentName;
    cmx.pageTree = cmx.get_page_tree();
    if (cmx.pageTree === false) {
        notice(text.PageTreeError, 1);
        Editor.reload();
        return false;
    }
    pageEl = $(b.item.context);
    parentEl = pageEl.parent();
    parentName = pageEl.find('a.page').attr('data-parent');
    parent = parentName === '_root' ? cmx.pageTree.pages : cmx.get_page_in_tree(parentName);
    page = cmx.get_page_in_tree(pageEl.find('a.page').html());
    newIndex = parentEl.children('li').index(pageEl);

    if (parent !== undefined && parent.hasOwnProperty('subpages') && page !== undefined && newIndex > -1) {
        oldIndex = parent.subpages.indexOf(page);
        parent.subpages.splice(newIndex, 0, parent.subpages.splice(oldIndex, 1)[0]);
        if (cmx.set_page_tree(cmx.pageTree)) {
            notice(text.OrderChanged);
            Editor.reload();
            return true;
        }
    }
    notice(text.OrderError, 1);
    Editor.reload();
}
;

Editor.page_sub_click = function (e) {
    var i;
    //$(e.currentTarget).html(newsymbol);
    $(e.currentTarget).toggleClass('closed');
    $(e.currentTarget).siblings('ul').toggleClass('closed').stop(false, true).slideToggle();
    i = $(e.currentTarget).siblings('a.page').attr('name');
    Editor.pageListOpened[i] = !$(e.currentTarget).siblings('ul').hasClass('closed');
    cmx.store_opened(Editor.pageListOpened);
    return false;
};

Editor.pages_list = function (parent, html) {
    var key, opened;
    html = (html === undefined) ? "" : html;
    for (key in parent.subpages) {
        if (parent.subpages.hasOwnProperty(key)) {
            if (!Editor.pageListOpened.hasOwnProperty(parent.subpages[key].file)) {
                Editor.pageListOpened[parent.subpages[key].file] = false;
            }
            opened = Editor.pageListOpened[parent.subpages[key].file] === true || Editor.pageListOpened[parent.subpages[key].file] === 'true';

            html += '<li>';
            Editor.allPages.push(parent.subpages[key].page);

            if (parent.subpages[key].hasOwnProperty('subpages') && parent.subpages[key].subpages.length > 0) {
                html += '<a class="sub ' + (opened ? '' : 'closed') + '" href="#"></a> ';
            } else {
                //html += '<span class="placeholder">&nbsp;</span>';
            }
            html += '<a href="#" class="page" data-parent="' + parent.page + '" name="' + parent.subpages[key].file + '">' + parent.subpages[key].page + '</a>';
            if (parent.subpages[key].hasOwnProperty('subpages') && parent.subpages[key].subpages.length > 0) {

                html += '<ul class="sublist ' + (opened ? '' : 'closed') + '">';
                html = Editor.pages_list(parent.subpages[key], html);
                html += '</ul>';
            }
            html += '</li>';
        }
    }
    return html;
};

Editor.page_click = function (e) {
    var editorForm = $("#editor-form");
    $(window).resize();
    editorForm.show();
    $('.page.active').removeClass('active');
    $(e.currentTarget).addClass('active');
    Editor.currentPageName = $(e.currentTarget).html();
    Editor.currentPageFile = $(e.currentTarget).attr('name');
    Editor.currentPageParent = $(e.currentTarget).attr('data-parent');
    editorForm.html(text.LoadingMsg);
    Editor.currentPage = cmx.get_page(Editor.currentPageFile);
    if (Editor.currentPage === false) {
        notice(text.GetPageError, 1);
        confirm('Page does not exist, do you want to delete it? ' + Editor.currentPageName + '?', Editor.confirm_delete);

        editorForm.html('');
        return false;
    }
    Editor.currentPageTpl = cmx.get_tpl(Editor.currentPage.template);
    if (Editor.currentPageTpl === false) {
        notice(text.GetTplError, 1);

        editorForm.html('');
        return false;
    }
    Editor.currentPageStructure = cmx.get_page_in_tree(Editor.currentPageName);
    if (Editor.currentPageStructure === undefined) {
        notice(text.GetPageStructureError, 1);
        editorForm.html('');
        return false;
    } else {

        Editor.currentPageStructureIndex = cmx.pageTree.pagesList.indexOf(Editor.currentPageStructure.page);
    }
    // notice('Fetched '+Editor.currentPageName);

    Editor.generate_form(Editor.currentPage.tplData, Editor.currentPageTpl.tplData);
    $(window).resize();
    return false;
};

Editor.generate_form = function (pageData, tplData) {
    var key, tpl, input, editorForm = $('#editor-form'), parentSelectString, parentIgnoredChildren, checked;

    editorForm.empty().append('<label data-title="' + text.LblPageNameHelp + '">' + text.LabelPageName + '</label><br/>' +
        '<input class="treevar" name="page" type="text" value="' +
        (Editor.currentPageStructure.page) + '" /><br/>');

    parentSelectString = '<label data-title="' + text.LblPageParentHelp + '" >' + text.LabelParent + '</label><select class="parentvar" ><option>' + text.NoParent + '</option>';

    parentIgnoredChildren = [];
    (function gen_ignored(parent) {
        for (key in parent) {
            if (parent.hasOwnProperty(key)) {
                parentIgnoredChildren.push(parent[key].page);
                if (parent[key].hasOwnProperty('subpages')) {
                    gen_ignored(parent[key].subpages);
                }
            }
        }
    }(Editor.currentPageStructure.subpages));

    for (key in Editor.allPages) {
        if (Editor.allPages[key] !== Editor.currentPageName && parentIgnoredChildren.indexOf(Editor.allPages[key]) < 0) {
            parentSelectString += '<option' + (Editor.currentPageParent === Editor.allPages[key] ? ' selected="selected" ' : '') + '>' + Editor.allPages[key] + '</option>';
        }
    }

    editorForm.append(parentSelectString + '</select><br/>');

    for (key in tplData) {
        if (tplData.hasOwnProperty(key)) {
            tpl = tplData[key];
            input = "";
            switch (tpl.type) {
            case 'text':
                input = '<label data-title="' + tpl.description + '">' + tpl.label + '</label><br/><input class="tplvar" name="' + key + '" type="text" value="' + htmlentities(pageData[key]) + '" /><br/>';
                break;
            case 'richtextarea':
                input = '<label data-title="' + tpl.description + '">' + tpl.label + '</label><br/><textarea class="tplvar rich" name="' + key + '" rows="8" cols="40" >' + htmlentities(pageData[key]) + '</textarea><br/>';
                break;
            case 'textarea':
                input = '<label data-title="' + tpl.description + '">' + tpl.label + '</label><br/><textarea class="tplvar" name="' + key + '" rows="8" cols="40" >' + htmlentities(pageData[key]) + '</textarea><br/>';
                break;
            case 'checkbox':
                checked = ((pageData === undefined || pageData[key] === undefined || $.isPlainObject(pageData[key])) && tpl['default'] === tpl['values'][0]) || pageData[key] === tpl['values'][0];
                input = '<label data-title="' + tpl.description + '">' + tpl.label + '</label>'
                input += '<span class="checkbox ' + (checked ? ' checked' : '') + '"></span>'
                input += '<input class="tplvar" name="' + key + '" type="checkbox" ' + (checked ? 'checked="checked"' : '') + ' /><br/>';
                break;
            case 'radio':
                input = '<label data-title="' + tpl.description + '">' + tpl.label + '</label><div class="rdgroup">';
                for (var i = 0; i < tpl.values.length; i++) {
                    checked = ((pageData === undefined || pageData[key] === undefined || $.isPlainObject(pageData[key])) && tpl['default'] === tpl['values'][i]) || pageData[key] === tpl['values'][i];
                    input += '<label class="rdlbl">' + tpl.labels[i] + '</label>';
                    input += '<span class="radio' + (checked ? ' checked' : '') + '"></span>'
                    input += '<input class="tplvar" type="radio" value="' + tpl.values[i] + '" name="' + key + '" ' + (checked ? 'checked="checked"' : '') + ' /><br/>';
                }
                input += '</div>';
                break;
            default:
                if (App.plugins.hasOwnProperty(tpl.type)) {
                    var plugin = new App.plugins[tpl.type]();
                    input = '<span class="pluginContainer" id="' + key + '">' + plugin.generate_form(tpl, pageData[key], key) + '</span>';
                    Editor.plugins.push(plugin);
                }
                break;
            }

            editorForm.append(input);

        }
    }

    for (var i = 0; i < Editor.plugins.length; i++) {
        Editor.plugins[i].generated_form();
    }

    checked = (!Editor.currentPageStructure.hasOwnProperty('cache') || Editor.currentPageStructure.cache === true || Editor.currentPageStructure.cache === "true");
    editorForm.append('<label data-title="' + text.LblCachePageHelp + '" >' + text.LabelCached + '</label>' +
        '<span class="checkbox' + (checked ? ' checked' : '') + '"></span>' +
        '<input class="treevar" type="checkbox" ' +
        (checked ? 'checked="checked"' : '') +
        ' name="cache"><br/>');

    checked = (!Editor.currentPageStructure.hasOwnProperty('menu') || Editor.currentPageStructure.menu === true || Editor.currentPageStructure.menu === "true");
    editorForm.append('<label data-title="' + text.LblMenuPageHelp + '" >' + text.LabelMenu + '</label>' +
        '<span class="checkbox' + (checked ? ' checked' : '') + '"></span>' +
        '<input class="treevar" type="checkbox" ' +
        (checked ? 'checked="checked"' : '') +
        ' name="menu"><br/>');

    checked = (Editor.currentPageStructureIndex > -1);
    editorForm.append('<label data-title="' + text.LblPublishPageHelp + '" >' + text.LabelPublish + '</label>' +
        '<span class="checkbox' + (checked ? ' checked' : '') + '"></span>' +
        '<input class="published" type="checkbox" ' +
        (checked ? 'checked="checked"' : '') +
        ' name="publish"><br/>');
    editorForm.append('<a id="removepage" href="#">' + text.DeleteBtn + '</a><a id="submiteditform" href="#">' + text.SaveBtn + '</a>');

    tinyMCE.init(
        {
            plugins                : "advimage",
            mode                   : "specific_textareas",
            editor_selector        : "rich",
            relative_urls          : false,
            external_image_list_url: "imgs.php",
            theme                  : "advanced",
            height                 : "300",
            theme_advanced_resizing: true,
            init_instance_callback : function () {
                $(window).resize();
            }

        }
    );

    $('#submiteditform').click(Editor.submit_editor);
    $('#removepage').click(Editor.delete_page);
    $('label[data-title]').unbind().click(function (e) {
        notice($(e.currentTarget).attr('data-title'));
    });
    $('input[type="checkbox"],input[type="radio"]').css({'position': 'absolute', 'left': '-10000px'});
    $('#editor span.checkbox,#editor span.radio').unbind().click(Editor.checkbox_click);
};

Editor.checkbox_click = function (e) {
    var real = $(e.currentTarget).next('input');
    real.click();
    $('#editor span.checkbox,#editor span.radio').each(function (i, e) {
        if ($(e).next('input').is(':checked')) {
            $(e).hasClass('checked') ? '' : $(e).addClass('checked');
        } else {
            $(e).hasClass('checked') ? $(e).removeClass('checked') : '';
        }
    });
    return false;
};

Editor.delete_page = function () {
    confirm(text.ConfirmDelete + ' ' + Editor.currentPageName + '?', Editor.confirm_delete);
    return false;
};

Editor.confirm_delete = function (confirmed) {
    if (confirmed === true) {
        cmx.pageTree = cmx.get_page_tree();
        if (cmx.pageTree === false) {
            notice(text.PageTreeError, 1);
            Editor.reload();
            return;
        }

        if (cmx.delete_from_tree(Editor.currentPageName) === false) {
            notice(text.DeleteFromTreeError, 1);
            Editor.reload();
            return;
        }

        if (cmx.set_page_tree(cmx.pageTree) === false) {
            notice(text.SetTreeError, 1);
            Editor.reload();
            return;
        }

        if (cmx.delete_page(Editor.currentPageFile) === false) {
            notice(text.DeletePageError, 1);
            Editor.reload();
            return;
        }
        notice(text.PageDelete);
        Editor.currentPageFile = null;
        $('#editor-form').empty();
        Editor.reload();
    }
};

Editor.submit_editor = function () {
    //alert('submit');
    var editorForm = $("#editor-form"), sendToPhp = true, parentVar = $('.parentvar');
    notice(text.Saving);
    editorForm.hide();
    cmx.pageTree = cmx.get_page_tree();
    if (cmx.pageTree === false) {
        notice(text.PageTreeError, 1);
        Editor.reload();
        return;
    }
    Editor.currentPageStructure = cmx.get_page_in_tree(Editor.currentPageName);
    if (Editor.currentPageStructure === undefined) {
        notice(text.GetPageStructureError, 1);
        editorForm.html('');
        return false;
    }
    tinyMCE.triggerSave();
    Editor.currentPageStructureIndex = cmx.pageTree.pagesList.indexOf(Editor.currentPageStructure.page);
    $('.tplvar').each(function (i, e) {
        var inp = $(e);
        if ((e.nodeName.toLowerCase() === 'input' && inp.attr('type') === 'text') || e.nodeName.toLowerCase() === "textarea") {
            Editor.currentPage.tplData[inp.attr('name')] = inp.val();
        }

        if ((e.nodeName.toLowerCase() === 'input' && inp.attr('type') === 'checkbox')) {
            var checked = inp.is(':checked');
            Editor.currentPage.tplData[inp.attr('name')] = Editor.currentPageTpl.tplData[inp.attr('name')]['values'][(checked ? 0 : 1)];
        }

        if ((e.nodeName.toLowerCase() === 'input' && inp.attr('type') === 'radio')) {
            var checked = inp.is(':checked');
            if (checked) {
                Editor.currentPage.tplData[inp.attr('name')] = inp.val();
            }
        }

    });

    for (var i = 0; i < Editor.plugins.length; i++) {
        Editor.currentPage.tplData[Editor.plugins[i].id] = Editor.plugins[i].submit_form();
    }

    $('.treevar').each(function (i, e) {
        if ((e.nodeName.toLowerCase() === 'input' && $(e).attr('type') === 'text') || e.nodeName.toLowerCase() === "textarea") {
            if ($(e).attr('name') === 'page') {
                var newPageName = safe_page_name($(e).val().trim());
                if ((cmx.get_page_in_tree(newPageName) === undefined || newPageName === Editor.currentPageName) && newPageName.length > 0) {
                    Editor.currentPageStructure[$(e).attr('name')] = newPageName;
                    if ($('.published').is(':checked')) {
                        if (Editor.currentPageStructureIndex === -1) {
                            cmx.pageTree.pagesList.push(newPageName);
                        } else {
                            cmx.pageTree.pagesList[Editor.currentPageStructureIndex] = newPageName;
                        }
                    } else {
                        if (Editor.currentPageStructureIndex !== -1) {
                            cmx.pageTree.pagesList.splice(Editor.currentPageStructureIndex, 1);
                        }
                    }
                } else {
                    sendToPhp = false;
                    //return
                }
            } else {
                Editor.currentPageStructure[$(e).attr('name')] = $(e).val();

            }
        } else if (e.nodeName.toLowerCase() === 'input' && $(e).attr('type') === 'checkbox') {
            Editor.currentPageStructure[$(e).attr('name')] = !!$(e).is(':checked');
        }
    });
    if (parentVar.val() !== Editor.currentPageParent && !(Editor.currentPageParent === '_root' && parentVar.val() === text.NoParent)) {
        cmx.move_in_tree(Editor.currentPageName, (parentVar.val() === text.NoParent ? '_root' : parentVar.val()));
    }

    if (sendToPhp) {
        if (cmx.set_page_content(Editor.currentPageStructure.file, Editor.currentPage) !== false && cmx.set_page_tree(cmx.pageTree) !== false) {
            //$("#editor-form").show();
            //$("#editor-form").html('success!');
            notice(text.SavedPage + ' ' + Editor.currentPageStructure.page + '!');

            Editor.reload();
        } else {
            //$("#editor-form").show();
            //$("#editor-form").html('failed to save, check file permissions');
            notice(text.SavePageError, 1);
            Editor.reload();
        }
    } else {
        editorForm.show();
        notice(text.PageNameError, 1);
    }
    return false;
};

$(document).ready(Editor.init);
