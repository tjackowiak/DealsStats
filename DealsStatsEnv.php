<?php

final class DealsStatsEnv {
  private static $env;

  public static function setEnvConfig(array $config) {
    self::$env = $config;
  }

  public static function getEnvConfig($key, $default = null) {
    return idx(self::$env, $key, $default);
  }

  public static function envConfigExists($key) {
    return array_key_exists($key, self::$env);
  }

  public static function getURI($path) {
    return rtrim(self::getEnvConfig('phabricator.base-uri'), '/').$path;
  }

  public static function getProductionURI($path) {
    $uri = self::getEnvConfig('phabricator.production-uri');
    if (!$uri) {
      $uri = self::getEnvConfig('phabricator.base-uri');
    }
    return rtrim($uri, '/').$path;
  }

  public static function getAllConfigKeys() {
    return self::$env;
  }

}