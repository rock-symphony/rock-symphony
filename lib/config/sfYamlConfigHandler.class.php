<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Yaml\Yaml;

/**
 * sfYamlConfigHandler is a base class for YAML (.yml) configuration handlers. This class
 * provides a central location for parsing YAML files.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfYamlConfigHandler extends sfConfigHandler
{
  /** @var array */
  protected $yamlConfig = null;

  /**
   * Parses an array of YAMLs files and merges them in one configuration array.
   *
   * @param string[] $configFiles An array of configuration file paths
   *
   * @return array A merged configuration array
   */
  static public function parseYamls(array $configFiles): array
  {
    $config = array();
    foreach ($configFiles as $configFile)
    {
      // the first level is an environment and its value must be an array
      $values = array();
      foreach (static::parseYaml($configFile) as $env => $value)
      {
        if (null !== $value)
        {
          $values[$env] = $value;
        }
      }

      $config = sfToolkit::arrayDeepMerge($config, $values);
    }

    return $config;
  }

  /**
   * Parses a YAML (.yml) configuration file.
   *
   * @param string $configFile An absolute filesystem path to a configuration file
   *
   * @return array A parsed .yml configuration
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   */
  static public function parseYaml(string $configFile): array
  {
    if (!is_readable($configFile))
    {
      // can't read the configuration
      throw new sfConfigurationException(sprintf('Configuration file "%s" does not exist or is not readable.', $configFile));
    }

    // parse our config
    $config = Yaml::parseFile($configFile);

    if ($config === false)
    {
      // configuration couldn't be parsed
      throw new sfParseException(sprintf('Configuration file "%s" could not be parsed', $configFile));
    }

    return null === $config ? array() : $config;
  }

  /**
   * Merges configuration values for a given key and category.
   *
   * @param string $keyName  The key name
   * @param string $category The category name
   *
   * @return array The value associated with this key name and category
   */
  protected function mergeConfigValue(string $keyName, string $category): array
  {
    $values = array();

    if (isset($this->yamlConfig['all'][$keyName]) && is_array($this->yamlConfig['all'][$keyName]))
    {
      $values = $this->yamlConfig['all'][$keyName];
    }

    if ($category && isset($this->yamlConfig[$category][$keyName]) && is_array($this->yamlConfig[$category][$keyName]))
    {
      $values = array_merge($values, $this->yamlConfig[$category][$keyName]);
    }

    return $values;
  }

  /**
   * Gets a configuration value for a given key and category.
   *
   * @param string $keyName      The key name
   * @param string $category     The category name
   * @param mixed $defaultValue The default value
   *
   * @return mixed The value associated with this key name and category
   */
  protected function getConfigValue(string $keyName, string $category, $defaultValue = null)
  {
    if (isset($this->yamlConfig[$category][$keyName]))
    {
      return $this->yamlConfig[$category][$keyName];
    }
    else if (isset($this->yamlConfig['all'][$keyName]))
    {
      return $this->yamlConfig['all'][$keyName];
    }

    return $defaultValue;
  }

  static public function flattenConfiguration(array $config): array
  {
    $config['all'] = sfToolkit::arrayDeepMerge(
      isset($config['default']) && is_array($config['default']) ? $config['default'] : array(),
      isset($config['all']) && is_array($config['all']) ? $config['all'] : array()
    );

    unset($config['default']);

    return $config;
  }

  /**
   * Merges default, all and current environment configurations.
   *
   * @param array $config The main configuration array
   *
   * @return array The merged configuration
   */
  static public function flattenConfigurationWithEnvironment(array $config): array
  {
    return sfToolkit::arrayDeepMerge(
      isset($config['default']) && is_array($config['default']) ? $config['default'] : array(),
      isset($config['all']) && is_array($config['all']) ? $config['all'] : array(),
      isset($config[sfConfig::get('sf_environment')]) && is_array($config[sfConfig::get('sf_environment')]) ? $config[sfConfig::get('sf_environment')] : array()
    );
  }
}
