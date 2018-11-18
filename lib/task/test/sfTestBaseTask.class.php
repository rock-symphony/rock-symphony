<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base test task.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfTestBaseTask extends sfBaseTask
{
  /**
   * Checks if a plugin exists.
   *
   * The plugin directory must exist and have at least one file or folder
   * inside for that plugin to exist.
   *
   * @param   string  $plugin
   *
   * @return  boolean True if the plugin exist, false otherwise
   */
  protected function checkPluginExists($plugin)
  {
    try
    {
      sfApplicationConfiguration::getActive()->getPluginConfiguration($plugin);

      return true;
    }
    catch (Exception $e)
    {
      return false;
    }
  }
}
