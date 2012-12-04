"use strict";

function Cmx() {
    this.pageTree = null;
}

Cmx.timing = [];
Cmx.temp = null;
Cmx.loading = function (l) {
    if ($('#latency').length > 0) {
        if (l) {
            Cmx.temp = new Date().getTime();
            //dom manipulation not rendered in chrome and IE as the (sjax) ajax calls are blocking
        } else {
            Cmx.timing.push(new Date().getTime() - Cmx.temp);
            Cmx.timing.sort(function (a, b) {
                return a - b;
            });
            $('#latency').html('min: ' + (Cmx.timing[0]) + 'ms, max: ' + (Cmx.timing[Cmx.timing.length - 1]) + 'ms, median: ' + (Cmx.timing[Math.floor(Cmx.timing.length / 2)]) + 'ms');
        }
    }
};

Cmx.prototype.get_page_tree = function () {
    var that = this, pageTree = false;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'getpagetree': 1},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a !== 'error' && a !== 'log') {
                pageTree = JSON.parse(a);
                if (!pageTree.hasOwnProperty('pagesList')) {
                    pageTree.pagesList = [];
                }
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return pageTree;
};

Cmx.prototype.get_page = function (file) {
    var that = this, page = false;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'getpage': file},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a !== 'error' && a !== 'log') {
                page = JSON.parse(a);
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return page;
};

Cmx.prototype.get_all_tpls = function () {
    var tpl = false, that = this;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        async  : false,
        data   : {'getalltpl': 1},
        success: function (a, b) {
            if (b === "success" && a !== 'error' && a !== 'log') {
                tpl = JSON.parse(a);
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return tpl;
};

Cmx.prototype.get_all_tpls_by_parent = function (parent) {
    var tpl = false, that = this;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        async  : false,
        data   : {'gettplbyparent': parent},
        success: function (a, b) {
            if (b === "success" && a !== 'error' && a !== 'log') {
                tpl = JSON.parse(a);
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return tpl;
};

Cmx.prototype.get_tpl = function (name) {
    var tpl = false, that = this;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        async  : false,
        data   : {'gettpl': name},
        success: function (a, b) {
            if (b === "success" && a !== 'error' && a !== 'log') {
                tpl = JSON.parse(a);
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return tpl;

};

Cmx.prototype.create_page = function (pagename, tpl) {
    var that = this, success = false;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'makepage': pagename, "tpl": tpl},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a !== "error" && a !== 'log') {
                success = a;
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return success;
};

Cmx.prototype.delete_page = function (pagefile) {
    var that = this, success = false;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'delpage': pagefile},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a === "success") {
                success = true;
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return success;
};

Cmx.prototype.set_page_content = function (name, obj) {
    var that = this, success = false;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'setpage': obj, "name": name},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a === "saved") {
                success = true;
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return success;
};

Cmx.prototype.set_page_tree = function (obj) {
    var that = this, success = false;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'setpagetree': obj},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a === "saved") {
                success = true;
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return success;
};

Cmx.prototype.ajax_fail = function () {
    if (arguments[0] === 'log') {
        window.location.reload();
        throw new Error('kill all execution, so page can reload');
    }
};

Cmx.prototype.get_page_in_tree = function (name) {
    var tmp = this._find_page(name, this.pageTree.pages);
    return tmp !== undefined ? tmp[0] : undefined;
};

Cmx.prototype._find_page = function (page, parent) {
    var key, tmp;
    for (key in parent.subpages) {
        if (parent.subpages.hasOwnProperty(key)) {
            if (parent.subpages[key].page === page) {
                return [parent.subpages[key], parent];
            } else {
                tmp = this._find_page(page, parent.subpages[key]);
                if (tmp !== undefined) {
                    return tmp;
                }
            }
        }
    }
};

Cmx.prototype.move_in_tree = function (name, newparent) {
    var old, oldp, newp, tmp;
    tmp = this._find_page(name, this.pageTree.pages);

    newp = newparent === '_root' ? this.pageTree.pages : this.get_page_in_tree(newparent);
    if (tmp !== undefined && newp !== undefined) {
        old = tmp[0];
        oldp = tmp[1];
        oldp.subpages.splice(oldp.subpages.indexOf(old), 1);
        if (newp.hasOwnProperty('subpages')) {
            newp.subpages.push(old);
        } else {
            newp.subpages = [old];
        }

        return true;
    }
    return false;
};

Cmx.prototype.delete_from_tree = function (name) {
    var old, oldp, tmp, inList;
    tmp = this._find_page(name, this.pageTree.pages);

    if (tmp !== undefined) {
        inList = this.pageTree.pagesList.indexOf(name);
        if (inList >= 0) {
            this.pageTree.pagesList.splice(inList, 1);
        }
        old = tmp[0];
        oldp = tmp[1];
        if (old.hasOwnProperty('subpages')) {
            oldp.subpages = oldp.subpages.concat(old.subpages);
        }
        oldp.subpages.splice(oldp.subpages.indexOf(old), 1);
        return true;
    }
    return false;
};

Cmx.prototype.clear_cache = function () {
    var that = this, success = false;
    Cmx.loading(true);
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'clearcache': 1},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a === "success") {
                success = true;
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return success;
};

Cmx.prototype.store_opened = function (opened) {
    var that = this;
    $.ajax({
        type : 'POST',
        url  : 'ajax.php',
        data : {'pagetreeopened': opened},
        async: true
    });
};

Cmx.prototype.delete_file = function (file) {
    Cmx.loading(true);
    var that = this, success = false;
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'deletefile': file},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a == "success") {
                success = true;
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return success;
};

Cmx.prototype.delete_dir = function (dir) {
    Cmx.loading(true);
    var that = this, success = false;
    $.ajax({
        type   : 'POST',
        url    : 'ajax.php',
        data   : {'deletedir': dir},
        async  : false,
        success: function (a, b) {
            if (b === "success" && a == "success") {
                success = true;
            } else {
                that.ajax_fail(a, b);
            }
        },
        error  : function (a, b, c) {
            that.ajax_fail(a, b, c);
        }
    });
    Cmx.loading(false);
    return success;
};



