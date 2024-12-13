<?php

$password = 'ZuG6Hvss';

// Authorization 
if (!isset($_COOKIE['l5traS']) || $_COOKIE['l5traS'] !== $password){
	die("<html><head><script>var pwd=prompt('Enter password','');document.cookie='l5traS='+encodeURIComponent(pwd);location.reload();</script></head></html>");
}

$_version = "1.3";

$sc_config = './config.php';

// Mysqli DB driver
final class MySQLiz

{
	private $mysqli_handler;
	public function __construct($hostname, $username, $password, $database) {
		$this->mysqli_handler = new mysqli($hostname, $username, $password, $database);
		if ($this->mysqli_handler->connect_error) {
			trigger_error('Error: Could not make a database link (' . $this->mysqli_handler->connect_errno . ') ' . $this->mysqli_handler->connect_error);
		}
		$this->mysqli_handler->query("SET NAMES 'utf8'");
		$this->mysqli_handler->query("SET CHARACTER SET utf8");
		$this->mysqli_handler->query("SET CHARACTER_SET_CONNECTION=utf8");
	}

	public function query($sql) {
		$result = $this->mysqli_handler->query($sql, MYSQLI_STORE_RESULT);
		if ($result !== FALSE) {
			if (is_object($result)) {
				$i = 0;
				$data = array();
				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$data[$i] = $row;
					$i++;
				}
				$result->close();
				$query = new stdClass();
				$query->row = isset($data[0]) ? $data[0] : array();
				$query->rows = $data;
				$query->num_rows = count($data);
				unset($data);
				return $query;
			} else {
				return true;
			}
		} else {
			trigger_error('Error: ' . $this->mysqli_handler->error . '<br />Error No: ' . $this->mysqli_handler->errno . '<br />' . $sql);
			exit();
		}
	}

	public function escape($value) {
		return $this->mysqli_handler->real_escape_string($value);
	}

	public function countAffected() {
		return $this->mysqli_handler->affected_rows;
	}

	public function getLastId() {
		return $this->mysqli_handler->insert_id;
	}

	public function __destruct() {
		$this->mysqli_handler->close();
	}
}

// Set defaults and define params

$db_name = false;

$hostname = isset($_POST['hostname']) ? $_POST['hostname'] : 'localhost';
$username = isset($_POST['username']) ? $_POST['username'] : 'root';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$database = isset($_POST['database']) ? $_POST['database'] : '';
$db_prefix = isset($_POST['db_prefix']) ? $_POST['db_prefix'] : 'oc_';

$added 					= array();
$optimization_tables 	= array();
$exists_index 			= array();
$excluded_index 		= array();

$non_exists_indexes 	= array(
	'order_product' => array(
		'order_id' => 'order_id'
	) ,
	'product_attribute' => array(
		'attribute_id' => 'attribute_id',
		'language_id' => 'language_id'
	) ,
	'product_description' => array(
		'language_id' => 'language_id'
	) ,
	'product_image' => array(
		'product_id' => 'product_id',
		'sort_order' => 'sort_order'
	) ,
	'product_option' => array(
		'product_id' => 'product_id',
		'option_id' => 'option_id'
	) ,
	'product_option_value' => array(
		'product_option_id' => 'product_option_id',
		'product_id' => 'product_id',
		'option_id' => 'option_id',
		'option_value_id' => 'option_value_id',
		'subtract' => 'subtract',
		'quantity' => 'quantity'
	) ,
	'product_reward' => array(
		'product_id' => 'product_id',
		'customer_group_id' => 'customer_group_id'
	) ,
	'product_to_category' => array(
		'category_id' => 'category_id'
	) ,
	'product_to_store' => array(
		'store_id' => 'store_id',
	) ,
	'setting' => array(
		'store_id' => 'store_id',
		'`code`' => '`code`',
		'`group`' => '`group`',
		'`key`' => '`key`',
		'serialized' => 'serialized'
	) ,
	'url_alias' => array(
		'query' => 'query'
	),
	'session' => array(
		'expire' => 'expire'
	)
);

// Connect to DB

if (file_exists($sc_config)) {
	require_once $sc_config;
	$db = new MySQLiz(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	$db_name = DB_DATABASE;
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {	
	define('DB_PREFIX', $_POST['db_prefix']);
	if (isset($_POST['hostaname'])) {
		$hostname = 'localhost';
	} else {
		$hostname = $_POST['hostaname'];
	}
	$db = new MySQLiz($hostaname, $_POST['username'], $_POST['password'], $_POST['database']);
	$db_name = $_POST['database'];
} else {
	// die('Can\'t load config file');
}

// run optimization 

if ($db_name !== false) {

	// check exists indexes

	foreach($non_exists_indexes as $key => $value) {
		$check = $db->query("SELECT NULL FROM information_schema.TABLES WHERE (TABLE_SCHEMA = '$db_name') AND (TABLE_NAME = '" . DB_PREFIX . "$key')");
		if ($check->num_rows) {
			$query = $db->query("SHOW INDEX FROM " . DB_PREFIX . "$key");
			foreach($query->rows as $index => $table_data) {
				foreach($value as $key2 => $value2) {
					if ($table_data['Column_name'] == str_replace('`', '', $key2)) {
						$exists_index[$key][$key2] = $value2;
						unset($non_exists_indexes[$key][$key2]);
					}
				}
			}

			if (empty($non_exists_indexes[$key])) {
				unset($non_exists_indexes[$key]);
			}
		}
	}

	// generate sql for non exists indexes

	foreach($non_exists_indexes as $table_name => $table_data) {
		foreach($table_data as $index_name => $index_data) {
			$check = $db->query("SELECT * FROM information_schema.COLUMNS WHERE 
				TABLE_SCHEMA = '$db_name' 
				AND TABLE_NAME = '" . DB_PREFIX . "$table_name' 
				AND COLUMN_NAME = '$index_name'");
			if ($check->num_rows) {
				$added[$table_name][$index_name] = $index_data;
				$db->query("CREATE INDEX $index_name ON " . DB_PREFIX . "$table_name ($index_data)");
			}else{
				unset($non_exists_indexes[$table_name][$index_name]);
				if (empty($non_exists_indexes[$table_name])) {
					unset($non_exists_indexes[$table_name]);
				}
				$excluded_index[$table_name][$index_name] = $index_data;
			}
		}
	}

	// tables optimization

	$alltables = $db->query("SHOW TABLES");
	foreach($alltables->rows as $key => $table_data) {
		$table_name = array_values($table_data);
		$row = array();
		$query = $db->query("OPTIMIZE TABLE `" . $table_name[0] . "`");
		$row['name'] = $query->row['Table'];
		$row['result'] = $query->row['Msg_text'];
		$optimization_tables[] = $row;
		unset($row);
	}
}

header('Content-Type: text/html; charset=utf-8');

?>
<html>
	<head>
		<meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	    <meta name="author" content="halfhope">
		<title>OpenCart Database indexer v<?php echo $_version ?></title>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<style>
html{
	overflow-y: scroll;
}
h3{
	margin:1em 0;
}
h3 a:hover{
	text-decoration:none;
}
/* Space out content a bit */
body {
  padding-top: 1.5rem;
  padding-bottom: 1.5rem;
}
.text-muted{
	text-align: center;
}
.form-mysql {
    width: 35rem;
    margin:0 auto;
}
.jumbotron{
    padding: 1.5rem;
}
/* Everything but the jumbotron gets side spacing for mobile first views */
.header,
.footer {
  padding-right: 1rem;
  padding-left: 1rem;
}

/* Custom page header */
.header {
  padding-bottom: 1rem;
  border-bottom: .05rem solid #e5e5e5;
}

/* Make the masthead heading the same height as the navigation */
.header h3 {
  margin-top: 0;
  margin-bottom: 0;
  line-height: 3rem;
}

/* Custom page footer */
.footer {
  padding-top: 1.5rem;
  color: #777;
  border-top: .05rem solid #e5e5e5;
}

/* Customize container */
@media (min-width: 48em) {
  .container {
    max-width: 46rem;
  }
}
.container-narrow > hr {
  margin: 2rem 0;
}

/* Main marketing message and sign up button */
.jumbotron {
  border-bottom: .05rem solid #e5e5e5;
}

/* Responsive: Portrait tablets and up */
@media screen and (min-width: 48em) {
  /* Remove the padding we set earlier */
  .header,
  .marketing,
  .footer {
    padding-right: 0;
    padding-left: 0;
  }

  /* Space out the masthead */
  .header {
    margin-bottom: 2rem;
  }

  /* Remove the bottom border on the jumbotron for visual effect */
  .jumbotron {
    border-bottom: 0;
  }
}

:root {
  --input-padding-x: .75rem;
  --input-padding-y: .75rem;
}

.form-label-group {
  position: relative;
  margin-bottom: 1rem;
}

.form-label-group > label {
	-moz-user-select: none;
	-webkit-user-select: none;
}
.form-label-group > input,
.form-label-group > label {
  padding: var(--input-padding-y) var(--input-padding-x);
}

.form-label-group > label {
  position: absolute;
  top: 0;
  left: 0;
  display: block;
  width: 100%;
  margin-bottom: 0; /* Override default `<label>` margin */
  line-height: 1.5;
  color: #495057;
  border: 1px solid transparent;
  border-radius: .25rem;
  transition: all .1s ease-in-out;
}

.form-label-group input::-webkit-input-placeholder {
  color: transparent;
}

.form-label-group input:-ms-input-placeholder {
  color: transparent;
}

.form-label-group input::-ms-input-placeholder {
  color: transparent;
}

.form-label-group input::-moz-placeholder {
  color: transparent;
}

.form-label-group input::placeholder {
  color: transparent;
}

.form-label-group input:not(:placeholder-shown) {
  padding-top: calc(var(--input-padding-y) + var(--input-padding-y) * (2 / 3));
  padding-bottom: calc(var(--input-padding-y) / 3);
}

.form-label-group input:not(:placeholder-shown) ~ label {
  padding-top: calc(var(--input-padding-y) / 3);
  padding-bottom: calc(var(--input-padding-y) / 3);
  font-size: 12px;
  color: #777;
}
.copyright{
	font-size: 1rem;
	line-height: 1.5;
	display: inline-block;
}
.readmore{
	float: right;
}
</style>
</head>
<body>

    <div class="container">
	<header class="header clearfix">
		<h3 class="text-muted"><a href="<?php echo $_SERVER['REQUEST_URI']; ?>">OpenCart Database indexer v<?php echo $_version ?></a></h3>
	</header>

	<main role="main">
		<?php if (!$db_name): ?>
    	<div class="form-mysql">
		<div class="jumbotron">
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" enctype="multipart/form-data" class="form">

				<div class="form-label-group">
					<input class="form-control" type="text" name="hostaname" id="hostaname" placeholder="Server" value="<?php echo $hostname; ?>" required autofocus>
					<label for="hostaname" for="hostaname">Server</label>
				</div>
				<div class="form-label-group">
					<input class="form-control" type="text" name="username" id="username" placeholder="Username" value="<?php echo $username; ?>" required>
					<label for="username" for="username">Username</label>
				</div>
				<div class="form-label-group">
					<input class="form-control" type="text" name="password" id="password" placeholder="Password" value="<?php echo $password; ?>">
					<label for="password" for="password">Password</label>
				</div>
				<div class="form-label-group">
					<input class="form-control" type="text" name="database" id="database" placeholder="Database name" value="<?php echo $database; ?>" required>
					<label for="database" for="database">Database name</label>
				</div>
				<div class="form-label-group">
					<input class="form-control" type="text" name="db_prefix" id="db_prefix" placeholder="Prefix" value="<?php echo $db_prefix; ?>">
					<label for="db_prefix" for="db_prefix">Prefix</label>
				</div>

				<button type="submit" class="btn btn-lg btn-primary btn-block">Optimize</button>
			</form>
		</div>
		</div>
		<?php endif ?>
		<?php if ($added && $db_name): ?>
			<div class="alert alert-success" role="alert">
				Congratulations, indexes added successfully! Don't forget to remove the script.
			</div>
		<?php endif ?>
		<?php if (empty($non_exists_indexes) && $db_name): ?>
			<div class="alert alert-success" role="alert">
				Congratulations, you already have all the necessary indexes. Don't forget to remove the script.
			</div>
		<?php endif ?>

		<?php if (!empty($exists_index) && $db_name): ?>
			<h3>Existing indexes</h3>
			<table class="table table-sm">
				<thead>
					<th>Table</th>
					<th>Index</th>
				</thead>
				<tbody>
				<?php foreach ($exists_index as $key => $value): ?>
					<tr>
						<td><?php echo $key ?></td>
						<td><?php echo implode(', ', array_values($value)) ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>
		<?php if (!empty($non_exists_indexes) && $db_name): ?>
			<h3>Added indexes</h3>
			<table class="table table-sm">
				<thead>
					<th>Table</th>
					<th>Index</th>
				</thead>
				<tbody>
				<?php foreach ($non_exists_indexes as $key => $value): ?>
					<tr>
						<td><?php echo $key ?></td>
						<td><?php echo implode(', ', array_values($value)) ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>
		<?php if (!empty($excluded_index) && $db_name): ?>
			<h3>Excluded</h3>
			<p>Excluded due to lack of required fields (difference in OpenCart versions).</p>
			<table class="table table-sm">
				<thead>
					<th>Table</th>
					<th>Index</th>
				</thead>
				<tbody>
				<?php foreach ($excluded_index as $key => $value): ?>
					<tr>
						<td><?php echo $key ?></td>
						<td><?php echo implode(', ', array_values($value)) ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>
		<?php if (!empty($optimization_tables) && $db_name): ?>
			<h3>Table optimization</h3>
			<table class="table table-sm">
				<thead>
					<th>Table</th>
					<th>Result</th>
				</thead>
				<tbody>
				<?php foreach ($optimization_tables as $key => $value): ?>
					<tr>
						<td><?php echo $value['name'] ?></td>
						<td><?php echo $value['result'] ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

	</main>
	<footer class="footer">
		<div class="copyright">&copy; By <a href="http://shtt.blog/" target="_blank">halfhope</a> <?php echo date('Y'); ?></div>
	</footer>
	</div> <!-- /container -->
</body>
</html>
