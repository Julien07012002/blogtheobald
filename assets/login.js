$(function() {
    var usernameEl = $('#username');
    var passwordEl = $('#password');

    if (!usernameEl.val() || '' === usernameEl.val()) {
        usernameEl.val('');
        passwordEl.val('');
    }
});
