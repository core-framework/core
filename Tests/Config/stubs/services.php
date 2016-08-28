<?php

return [

    'baseServices' => [
        
    ],

    'lazyServices' => [
        'View' => [
            \Core\View\View::class,
            ['App']
        ],
        'Smarty' => \Core\View\SmartyEngine::class
    ]
];