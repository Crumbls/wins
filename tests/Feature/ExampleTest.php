<?php

test('the application redirects guests to the login screen', function () {
    $this->get('/')->assertRedirect(route('login'));
});
