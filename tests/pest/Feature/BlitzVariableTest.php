<?php

/**
 * Tests the markup generated by the Blitz variable.
 */

use putyourlightson\blitz\variables\BlitzVariable;

test('Include cached tag does not contain encoded slashes in params', function() {
    $variable = new BlitzVariable();
    $tagString = (string)$variable->includeCached('test');

    expect($tagString)
        ->not()
        ->toContain('%2F');
});
