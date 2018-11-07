<?php
//项目配置文件
    return array(
        'APP_GROUP_LIST' => 'NewWeb', //首页,管理员，上书方，站长
        //'DEFAULT_GROUP' => 'NewWeb', //默认分组
       // 'DEFAULT_MODULE' => 'Index', //默认模块
        'URL_MODEL' => '2', //URL模式
        'SESSION_AUTO_START' => true, //是否开启session
        'TMPL_FILE_DEPR' => '_', //模板文件MODULE_NAME与ACTION_NAME之间的分割符
        'DB_TYPE' => 'mysql', // 数据库类型
        'DB_HOST' => '101.132.142.46', // 服务器地址
        'DB_NAME' => 'newhezuo', // 数据库名
        'DB_USER' => 'root', // 用户名
        'DB_PWD' => 'shengwen!123', // 密码
        'DB_PORT' => '3306', // 端口    
        'DB_PREFIX' => 'hezuo_', // 数据库表前缀
        'ALL_ps' => 'hezuo',//自定义全局变量
        'Prices'=>6,//章节默认价格
        'TMPL_L_DELIM' => '<{',
        'TMPL_R_DELIM' => '}>',
       // 'SHOW_PAGE_TRACE' => true,//调试框
        'TMPL_STRIP_SPACE'=>FALSE,//模版打印不去空格
        'WebName' => '书库管理系统',//项目名字 
    );
