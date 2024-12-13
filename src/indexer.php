<?php 
require_once "config.php";

$setup_sql = array();
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_attribute ADD INDEX attribute_id ( attribute_id );";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_attribute ADD INDEX language_id ( language_id );";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_description ADD INDEX language_id ( language_id );";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_image ADD INDEX product_id ( product_id );";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_image ADD INDEX sort_order ( sort_order );";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_option ADD INDEX product_id (product_id);";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_option ADD INDEX option_id (option_id);";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_option_value ADD INDEX product_option_id (product_option_id);";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_option_value ADD INDEX product_id (product_id);";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_option_value ADD INDEX option_id (option_id);";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_option_value ADD INDEX option_value_id (option_value_id);";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_option_value ADD INDEX subtract (subtract);";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_option_value ADD INDEX quantity (quantity);";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_reward ADD INDEX product_id ( product_id );";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_reward ADD INDEX customer_group_id ( customer_group_id );";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_to_category ADD INDEX category_id ( category_id );";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "product_to_store ADD INDEX store_id ( store_id );";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "setting ADD INDEX store_id ( store_id );";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "setting ADD INDEX `group` ( `group` );";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "setting ADD INDEX `key` ( `key` );";
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "setting ADD INDEX serialized ( serialized );";
 
$setup_sql[] = "ALTER TABLE " . DB_PREFIX . "url_alias ADD INDEX query ( query );";

//If Use Mysql Database + cache
require_once(DIR_SYSTEM . 'library/cache.php');
$cache = new Cache();

// Database 
require_once(DIR_SYSTEM . 'library/db.php');
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
foreach ($setup_sql as $key => $value) {
	$db->query($value);
}
unlink(__FILE__);

?>