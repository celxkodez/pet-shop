<?php

return [

    //JWT secret key used in signing tokens
    'secret' => env('JWT_SECRET'),

    'ttl' => env('TTL', 60)

];
