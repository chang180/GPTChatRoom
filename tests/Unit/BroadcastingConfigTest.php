<?php

it('disables the broadcasting client when the default driver is not ably', function () {
    expect(config('broadcasting.default'))->not->toBe('ably')
        ->and(config('broadcasting.client_enabled'))->toBeFalse();
});
