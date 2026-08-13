<?php

test('legal pages are publicly available', function (string $routeName) {
    $this->get(route($routeName))->assertSuccessful();
})->with(['privacy', 'terms']);
