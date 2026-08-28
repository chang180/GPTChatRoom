<?php

it('disables the broadcasting client when reverb is not configured', function () {
    expect(config('broadcasting.default'))->not->toBe('reverb')
        ->and(config('broadcasting.client_enabled'))->toBeFalse();
});
