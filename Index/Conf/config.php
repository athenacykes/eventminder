<?php
$config = array(
	'EVM_PAGE_LIMIT' => '10',

);

$config = array_merge(include './Index/Lang/zh_cn.php', $config);
//$config = array_merge(include './Index/Lang/en_us.php', $config);

return array_merge(include './Conf/config.php', $config);
?>