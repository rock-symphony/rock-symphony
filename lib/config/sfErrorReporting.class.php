<?php

/**
 * sfErrorReporting parses error reporting config entry value.
 *
 * This is needed, as the new symfony/yaml component does not perform in-yaml PHP code execution.
 * So we need to replace it with another human-readable way to define "error_reporting".
 *
 * This class converts human-readable error_reporting value to PHP bitmask int.
 * And sets.
 *
 * Support syntax:
 *
 * 1. int (bitmask):
 *    error_reporting: 2056
 *
 * 2. string (constant name):
 *    error_reporting: E_ALL
 *
 * 3. array of int/strings:
 *     error_reporting: [E_ALL, E_STRICT, ^E_DEPRECATED] # equivalent of (E_ALL | E_STRICT) ^ E_DEPRECATED
 *
 * @internal Do not use this class in your project. It's internal and can be removed/modified at any time.
 *
 * @param int|string|array $errorReporting config entry value
 * @return void
 */
class sfErrorReporting
{
  /**
   * @param array|null|string|int $error_reporting_config
   * @return void
   */
  public function set($error_reporting_config)
  {
    $error_reporting = $this->parse($error_reporting_config);
    if ($error_reporting !== null) {
      error_reporting($error_reporting);
    }
  }

  /**
   * @param array|null|string|int $error_reporting_config
   * @return int|null Error reporting level as INTEGER, or NULL to keep current level
   */
  public function parse($error_reporting_config)
  {
    if (is_null($error_reporting_config)) {
      // NULL === Keep current error reporting
      return null;
    }

    if (is_int($error_reporting_config)) {
      // No need to do conversions
      return $error_reporting_config;
    }

    if (is_string($error_reporting_config)) {
      // Leave it to parseArray()
      $error_reporting_config = [$error_reporting_config];
    }

    if (is_array($error_reporting_config)) {
      return $this->parseArray($error_reporting_config);
    }

    throw new InvalidArgumentException('Invalid value defined for "error_reporting" config entry.');
  }

  /**
   * @param array $error_reporting_config
   * @return int|null
   */
  private function parseArray($error_reporting_config)
  {
    if (count($error_reporting_config) === 0) {
      // Keep current error_reporting level
      return null;
    }

    $combined = 0;

    foreach ($error_reporting_config as $level) {
      if (is_string($level) && strlen($level) > 1 && $level[0] === '^') {
        $combined = $combined ^ $this->decodeLevel(substr($level, 1));
      } else {
        $combined = $combined | $this->decodeLevel($level);
      }
    }

    return $combined;
  }

  /**
   * @param int|string $level
   * @return int
   *
   * @throws \InvalidArgumentException if the given level cannot be decoded (unsupported string value or type)
   */
  private function decodeLevel($level)
  {
    if (is_int($level)) {
      return $level;
    }

    if (is_string($level)) {
      $mapping = [
        'E_ERROR'             => E_ERROR,
        'E_WARNING'           => E_WARNING,
        'E_PARSE'             => E_PARSE,
        'E_NOTICE'            => E_NOTICE,
        'E_CORE_ERROR'        => E_CORE_ERROR,
        'E_CORE_WARNING'      => E_CORE_WARNING,
        'E_COMPILE_ERROR'     => E_COMPILE_ERROR,
        'E_COMPILE_WARNING'   => E_COMPILE_WARNING,
        'E_USER_ERROR'        => E_USER_ERROR,
        'E_USER_WARNING'      => E_USER_WARNING,
        'E_USER_NOTICE'       => E_USER_NOTICE,
        'E_STRICT'            => E_STRICT,
        'E_RECOVERABLE_ERROR' => E_RECOVERABLE_ERROR,
        'E_DEPRECATED'        => E_DEPRECATED,
        'E_USER_DEPRECATED'   => E_USER_DEPRECATED,
        'E_ALL'               => E_ALL,
      ];

      if (isset($mapping[$level])) {
        return $mapping[$level];
      }

      throw new InvalidArgumentException(sprintf('Unsupported error_reporting level string given: "%s"', $level));
    }

    throw new InvalidArgumentException(sprintf(
      'Unsupported value type given for error_reporting level: %s',
      sprintf(gettype($level))
    ));
  }
}
