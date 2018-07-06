<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfYaml offers convenience methods to load and dump YAML.
 *
 * @package    symfony
 * @subpackage yaml
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfYaml.class.php 8988 2008-05-15 20:24:26Z fabien $
 */
class sfYaml
{
  static protected
    $spec = '1.2';

  /**
   * Gets the YAML specification version to use.
   *
   * @return string The YAML specification version
   */
  static public function getSpecVersion()
  {
    return self::$spec;
  }
}
