Cms-Alex (cmx)
==============

File-based end-user oriented CMS

Wiki's will be move here shortly (http://code.google.com/p/cmx/)


Early stages of a file-based CMS. I started this project out of necessity for a simple to customize and flexible CMS for my clients.

Most pre-made CMSes are either hard to customize, pack a bunch of features you never use or simply don't fit your project.

Basic idea: developer starts from scratch (no out-of-the-box website that just needs a layer of css/js)

This doesn't imply it's less productive than let's say customizing a wordpress site. On the contrary, you won't be spending hours in the docs to find the right functions and writing hacks to make it suit your needs (or your clients).

Cmx let's you do what you do best, write php/html/css without worrying about the variety of forms you'll need to create/validate/insert to make it into a cms (over and over again for each site you make).

*  Simple templating system, with no hard to learn api.
*  In a json file (1 for each template) you specify the input fields the client can use to edit his content. (4 standard html inputs, easily extendable with javascript admin-plugins)
*  Caching (you can specify which pages can be cached and which not, or manually cache parts of your template/page).
*  Multilingual support (and appropriate caching)
*  Backend currently allows users to create/edit/delete/reorder pages as well as upload files/images (images can be resized to dimensions specified in Config or dynamically in a template).
*  Built for speed both on back and frontend.

If you are interested in this project, and would like to comment/contribute/... I'd be thrilled if you mailed me at kobelipkens@gmail.com.

Installation
============

Simply download the source and edit .htaccess and Config.class.php to match your directory

![admin-panel editor](https://raw.github.com/frizinak/Alex/master/docs/images/admin_editor.jpg)
