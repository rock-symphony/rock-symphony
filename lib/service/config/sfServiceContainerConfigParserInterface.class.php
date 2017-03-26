<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfServiceContainerConfigParserInterface parses loaded services config array
 * and translates it to sfServiceContainerBuilder state
 *
 * @package    symfony
 * @subpackage service
 */
interface sfServiceContainerConfigParserInterface
{
  /**
   * Parse services config array.
   *
   * @param array $config The service config array
   * @return sfServiceContainerBuilder
   */
  public function parse(array $config);
}
