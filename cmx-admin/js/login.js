$(document).ready(init);

function init() {
    $('#nojs').hide();
    $('#login-form').show();
    $('#login-form').submit(submit)
}

function submit() {
    var key = $('#login-form #key').val();
    var pw = $('#login-form #password').val();
    $('#login-form #login').val(hex_sha1(hex_sha1(pw+'VzAVKtFAixn8B0rZq32k') + key));
}
