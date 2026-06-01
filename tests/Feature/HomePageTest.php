<?php

it('renders welcome with inertia v3 script page payload', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('<script data-page="app" type="application/json">', false);
    $response->assertSee('"component":"Welcome"', false);
});
