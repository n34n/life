<?php
return [
    'maintenance'=> 0,//系统维护标识,1为维护中
    'maintenanceMsg' => "2016年12月31日 2:00-4:00",
    'adminEmail' => '34n@163.com',
    'supportEmail' => 'support@example.com',
    'defaultProject' => '日常',
    'pageSize'       =>  8,
    'defaultTags'    => '[{"id": 1,"name":"服装"},{"id": 2,"name":"鞋靴"},{"id": 3,"name":"配饰"},{"id": 4,"name":"美妆"},{"id": 5,"name":"数码"},{"id": 6,"name":"证件"},{"id": 7,"name":"书籍"},{"id": 8,"name":"玩具"},{"id": 9,"name":"文具"},{"id": 10,"name":"工具"},{"id": 11,"name":"卫浴"},{"id": 12,"name":"厨房"},{"id": 13,"name":"医药"},{"id": 14,"name":"饮食"},{"id": 15,"name":"家饰"},{"id": 16,"name":"家纺"}]',
    'user.passwordResetTokenExpire' => 3600,
    'UploadPath'  => dirname(dirname(__DIR__)).'/uploads/',
    'UperChar'    => array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'),
];
