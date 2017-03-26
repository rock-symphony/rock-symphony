<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfServiceContainerDumperInterface dumps the given sfServiceContainerBuilder state
 * to specific format string representation (to be stored to filesystem).
 *
 * @package    symfony
 * @subpackage service
 */
interface sfServiceContainerDumperInterface
{
  /**
   * Dump sfServiceContainerBuilder state to string representation.
   *
   * @param \sfServiceContainerBuilder $builder
   * @param array                      $options
   *
   * @return string
   */
  public function dump(sfServiceContainerBuilder $builder, array $options = array());

}
