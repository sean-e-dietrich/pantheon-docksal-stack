<?php

// System paths
putenv('PATH=' . getenv('PATH') . ':/srv/bin');

// Generic Pantheon site inf0z
define('PANTHEON_SITE', getenv('PANTHEON_SITE'));
define('PANTHEON_SITE_NAME', getenv('PANTHEON_SITE_NAME'));
define('PANTHEON_ENVIRONMENT', getenv('PANTHEON_ENVIRONMENT'));
$_ENV['PANTHEON_SITE'] = PANTHEON_SITE;
$_ENV['PANTHEON_SITE_NAME'] = PANTHEON_SITE_NAME;
$_ENV['PANTHEON_ENVIRONMENT'] = PANTHEON_ENVIRONMENT;

// Database things
define('PANTHEON_DATABASE_HOST', getenv('DB_HOST'));
define('PANTHEON_DATABASE_PORT', getenv('DB_PORT'));
define('PANTHEON_DATABASE_USERNAME', getenv('DB_USER'));
define('PANTHEON_DATABASE_PASSWORD', getenv('DB_PASSWORD'));
define('PANTHEON_DATABASE_DATABASE', getenv('DB_NAME'));
$_ENV['DB_HOST'] = PANTHEON_DATABASE_HOST;
$_ENV['DB_PORT'] = PANTHEON_DATABASE_PORT;
$_ENV['DB_USER'] = PANTHEON_DATABASE_USERNAME;
$_ENV['DB_PASSWORD'] = PANTHEON_DATABASE_PASSWORD;
$_ENV['DB_NAME'] = PANTHEON_DATABASE_DATABASE;

// Cache things
define('PANTHEON_REDIS_HOST', getenv('CACHE_HOST'));
define('PANTHEON_REDIS_PORT', getenv('CACHE_PORT'));
define('PANTHEON_REDIS_PASSWORD', getenv('CACHE_PASSWORD'));
$_ENV['CACHE_HOST'] = PANTHEON_REDIS_HOST;
$_ENV['CACHE_PORT'] = PANTHEON_REDIS_PORT;
$_ENV['CACHE_PASSWORD'] = PANTHEON_REDIS_PASSWORD;

// Index things
define('PANTHEON_INDEX_HOST', getenv('PANTHEON_INDEX_HOST'));
define('PANTHEON_INDEX_PORT', getenv('PANTHEON_INDEX_PORT'));
$_ENV['PANTHEON_INDEX_PORT'] = PANTHEON_INDEX_PORT;
$_ENV['PANTHEON_INDEX_HOST'] = PANTHEON_INDEX_HOST;

// Environmental things
$_ENV['FRAMEWORK'] = getenv('FRAMEWORK');

// Framework things
$_ENV['DRUPAL_HASH_SALT'] = getenv('DRUPAL_HASH_SALT');
$_ENV['AUTH_KEY'] = getenv('AUTH_KEY');
$_ENV['SECURE_AUTH_KEY'] = getenv('SECURE_AUTH_KEY');
$_ENV['LOGGED_IN_KEY'] = getenv('LOGGED_IN_KEY');
$_ENV['AUTH_SALT'] = getenv('AUTH_SALT');
$_ENV['SECURE_AUTH_SALT'] = getenv('SECURE_AUTH_SALT');
$_ENV['LOGGED_IN_SALT'] = getenv('LOGGED_IN_SALT');
$_ENV['NONCE_SALT'] = getenv('NONCE_SALT');
$_ENV['NONCE_KEY'] = getenv('NONCE_KEY');
$_SERVER['PRESSFLOW_SETTINGS'] = json_encode(array(
  'conf' => array (
    'pressflow_smart_start' => true,
    'pantheon_site_uuid' => PANTHEON_SITE,
    'pantheon_environment' => PANTHEON_ENVIRONMENT,
    'pantheon_tier' => 'live',
    'pantheon_index_host' => PANTHEON_INDEX_HOST,
    'pantheon_index_port' => PANTHEON_INDEX_PORT,
    'redis_client_host' => PANTHEON_REDIS_HOST,
    'redis_client_port' => PANTHEON_REDIS_PORT,
    'redis_client_password' => PANTHEON_REDIS_PASSWORD,
    'file_public_path' => 'sites/default/files',
    'file_private_path' => 'sites/default/files/private',
    'file_directory_path' => 'sites/default/files',
    'file_temporary_path' => '/tmp',
    'file_directory_temp' => '/tmp',
    'css_gzip_compression' => false,
    'js_gzip_compression' => false,
    'page_compression' => false,
    'error_level' => 0,
  ),
  'databases' => array (
    'default' => array (
      'default' => array (
        'host' => PANTHEON_DATABASE_HOST,
        'port' => PANTHEON_DATABASE_PORT,
        'username' => PANTHEON_DATABASE_USERNAME,
        'password' => PANTHEON_DATABASE_PASSWORD,
        'database' => PANTHEON_DATABASE_DATABASE,
        'driver' => 'mysql',
      ),
    ),
  ),
  'drupal_hash_salt' => $_ENV['DRUPAL_HASH_SALT'],
  'config_directory_name' => 'config',
));

# WordPress Specifc Constants
if (isset($_SERVER['SERVER_PORT'])) {
  define('JETPACK_SIGNATURE__HTTP_PORT', $_SERVER['SERVER_PORT']);
  define('JETPACK_SIGNATURE__HTTPS_PORT', $_SERVER['SERVER_PORT']);
}

// Let drupal know when to generate absolute links as https.
// Used in drupal_settings_initialize()
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['HTTP_X_SSL'] = 'ON';
}

/*
 * These $_SERVER variables are often used for redirects in code that is read
 * directly (e.g. settings.php) so we can't have them visible to the CLI lest
 * CLI processes might hit a redirect (e.g. header() and exit()) and die.
 *
 * CLI tools are encouraged to use getenv() or $_ENV going forward to read
 * environment configuration.
 */
if (isset($_SERVER['GATEWAY_INTERFACE'])) {
  $_SERVER['PANTHEON_ENVIRONMENT'] = 'dev';
  $_SERVER['PANTHEON_SITE'] = '7587b3ea-95b6-44ab-b36b-6ebd9c3e9866';
}
else {
  unset($_SERVER['PANTHEON_ENVIRONMENT']);
  unset($_SERVER['PANTHEON_SITE']);
}

/*
 * We need to set this on Drupal 8 to make sure we are getting
 * properly redirected to install.php in the event that the
 * user does not have the needed core tables.
 * @todo: how does this check impact performance?
 *
 * Issue: https://github.com/pantheon-systems/drops-8/issues/139
 *
 */
if (
  isset($_ENV['FRAMEWORK']) &&
  $_ENV['FRAMEWORK'] == 'drupal8' &&
  (empty($GLOBALS['install_state'])) &&
  php_sapi_name() != "cli"
) {

  /* Connect to an ODBC database using driver invocation */
  $dsn = 'mysql:dbname=' . $_ENV['DB_NAME'] . ';host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'];
  $user = $_ENV['DB_USER'];
  $password = $_ENV['DB_PASSWORD'];

  try {
    $dbh = new PDO($dsn, $user, $password);
  } catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
  }

  /*
   * Check to see if the `users` table exists and if it does not set
   * PANTHEON_DATABASE_STATE to `empty` to allow for correct redirect to
   * install.php. This is for users who create sites on Pantheon but
   * don't go through the database setup before they pull them down
   * on Lando.
   *
   * Issue: https://github.com/pantheon-systems/drops-8/issues/139
   *
   */
  if ((gettype($dbh->exec("SELECT count(*) FROM users")) == 'integer') != 1) {
    $_SERVER['PANTHEON_DATABASE_STATE'] = 'empty';
  }

  // And now we're done; close it up!
  $dbh = null;

}
