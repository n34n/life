<?php
return [
    'maintenance'=> 0,//系统维护标识,1为维护中
    'maintenanceMsg' => "2016年12月31日 2:00-4:00",
    'adminEmail' => '34n@163.com',
    'supportEmail' => 'support@example.com',
    'defaultProject' => '日常',
    'pageSize'       =>  8,
    'defaultTags'    => '[{"tag_id": 1,"tag":"服装"},{"tag_id": 2,"tag":"鞋靴"},{"tag_id": 3,"tag":"配饰"},{"tag_id": 4,"tag":"美妆"},{"tag_id": 5,"tag":"数码"},{"tag_id": 6,"tag":"证件"},{"tag_id": 7,"tag":"书籍"},{"tag_id": 8,"tag":"玩具"},{"tag_id": 9,"tag":"文具"},{"tag_id": 10,"tag":"工具"},{"tag_id": 11,"tag":"卫浴"},{"tag_id": 12,"tag":"厨房"},{"tag_id": 13,"tag":"医药"},{"tag_id": 14,"tag":"饮食"},{"tag_id": 15,"tag":"家饰"},{"tag_id": 16,"tag":"家纺"}]',
    'user.passwordResetTokenExpire' => 3600,
    'UploadPath'  => dirname(dirname(__DIR__)).'/uploads/',
    'UperChar'    => array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'),
];
