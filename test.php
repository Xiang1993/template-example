<?php
include "Template.php";
$tpl = Template::getInstance();
$tpl->assign('data', 'hello world');
$tpl->assign('person', 'cafeCAT');
$tpl->assign('pai', 3.14);
$arr = array(1, 2, 3, 4, 'hahattt', 6);
$tpl->assign('b', $arr);
$tpl->show('member');
