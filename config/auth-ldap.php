<?php

return [
    'provider' => [
        'driver' => 'eloquent',
        'must_exist' => false,
    ],
    'convert_fields' => [
        'name' => 'displayname',
    ],
    'credentials_fields' => [
        'username' => 'username',
        'password' => 'password',
    ],
    'username_field' => 'samaccountname',
];
