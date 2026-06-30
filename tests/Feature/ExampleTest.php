<?php

test('the welcome page renders successfully', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
