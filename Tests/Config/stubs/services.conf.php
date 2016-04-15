<?php

return [
    
    'View' => [
        'definition' => \Core\View\View::class,
        'dependencies' => [
            '\\Core\\Application\\Application::$app'
        ]
    ],

    'Smarty' => \Smarty::class
];