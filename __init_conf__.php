<?php

function dealstats_read_config_file($config) {
  $root = dirname(__FILE__);
  $conf = @include $root.'/conf/'.$config.'.conf.php';
  if ($conf === false) {
    throw new Exception("Failed to read config file '{$config}'.");
  }
  return $conf;
}

/**
* Identity function, returns its argument unmodified.
*
* This is useful almost exclusively as a workaround to an oddity in the PHP
* grammar -- this is a syntax error:
*
* COUNTEREXAMPLE
* new Thing()->doStuff();
*
* ...but this works fine:
*
* id(new Thing())->doStuff();
*
* @param wild Anything.
* @return wild Unmodified argument.
* @group util
*/
function id($x) {
  return $x;
}


/**
* Access an array index, retrieving the value stored there if it exists or
* a default if it does not. This function allows you to concisely access an
* index which may or may not exist without raising a warning.
*
* @param array Array to access.
* @param scalar Index to access in the array.
* @param wild Default value to return if the key is not present in the
* array.
* @return wild If $array[$key] exists, that value is returned. If not,
* $default is returned without raising a warning.
* @group util
*/
function idx(array $array, $key, $default = null) {
  return array_key_exists($key, $array) ? $array[$key] : $default;
}