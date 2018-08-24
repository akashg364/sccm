<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

\Yii::$container->set('yii\grid\GridView', [
    'layout'=>"{summary}\n<div class='tabe-grid-view'>{items}</div>\n{pager}",
     'tableOptions'=>[
                "class"=>"table-style tableBodyScroll"
        ],
]);
\Yii::$container->set('yii\grid\DataColumn', [
    'filterInputOptions' => [
        'class' => 'form-control table-filter',
    ],
]);
\Yii::$container->set('yii\widgets\DetailView', [
    "template"=>'<tr><td{captionOptions}>{label}</td><td{contentOptions}>{value}</td></tr>',
                "options"=>["class"=>"table-style tableBodyScroll"],
]);

return [
    'id' => 'app-backend',
    'layout'=>'sccm',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log', 'admin'],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'site/*',
            'admin/*',
            'some-controller/some-action',
            // The actions listed here will be allowed to everyone including guests.
            // So, 'admin/*' should not appear here in the production, of course.
            // But in the earlier stages of your development, you may probably want to
            // add a lot of actions here until you finally completed setting up rbac,
            // otherwise you may not even take a first step.
        ]
    ],
    'modules' => [
        'admin' => [
            'class' => 'mdm\admin\Module',
            // 'layout' => 'left-menu',// other available values are 'right-menu' and 'top-menu'
            
        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager' //or 'yii\rbac\PhpManager'
        ],
         'view' => [
            'theme' => [
                'pathMap' => [
                    '@mdm/admin/views' => '@backend/views/administrator',
                ]
            ]
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
              // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'eEbDG7xzoMNTPPapjLyJ-PoPQUbzFb5P',   
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
            'maxSourceLines' => 20,

        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
        //  'view' => [
        //     'theme' => [
        //         'basePath' => '@app/themes/basic',
        //         'baseUrl' => '@web/themes/basic',
        //         'pathMap' => [
        //             '@app/views' => '@app/themes/basic',
        //         ],
        //     ],
        // ],
    ],
    'params' => $params,
];