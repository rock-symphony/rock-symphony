<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for validation classes.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfValidation extends sfBaseTask
{
  protected $task = null;

  /**
   * Validates the current project.
   *
   * @return string[] Files to be upgraded.
   */
  abstract public function validate(): array;

  abstract public function getHeader(): string;

  /**
   * @return string[]|string
   */
  abstract public function getExplanation(): array | string;

  public function execute(array $arguments = [], array $options = []): int
  {
    throw new sfException('You can\'t execute this task.');
  }

  /**
   * Returns a finder that exclude upgrade scripts from being upgraded!
   *
   * @param string $type  String directory or file or any (for both file and directory)
   *
   * @return sfFinder A sfFinder instance
   */
  protected function getFinder(string $type): sfFinder
  {
    return sfFinder::type($type)->prune('symfony')->discard('symfony');
  }

  /**
   * Returns all project directories where you can put PHP classes.
   *
   * @return string[]
   */
  protected function getProjectClassDirectories(): array
  {
    return array_merge(
      $this->getProjectLibDirectories(),
      $this->getProjectActionDirectories()
    );
  }

  /**
   * Returns all project directories where you can put templates.
   *
   * @return string[]
   */
  protected function getProjectTemplateDirectories(): array
  {
    return array_merge(
      glob(sfConfig::get('sf_apps_dir') . '/*/modules/*/templates'),
      glob(sfConfig::get('sf_apps_dir') . '/*/templates')
    );
  }

  /**
   * Returns all project directories where you can put actions and components.
   *
   * @return string[]
   */
  protected function getProjectActionDirectories(): array
  {
    return glob(sfConfig::get('sf_apps_dir') . '/*/modules/*/actions');
  }

  /**
   * Returns all project lib directories.
   *
   * @param string|null $subdirectory  A subdirectory within lib (i.e. "/form")
   *
   * @return string[]
   */
  protected function getProjectLibDirectories(string | null $subdirectory = null): array
  {
    return array_merge(
      glob(sfConfig::get('sf_apps_dir') . '/*/modules/*/lib' . $subdirectory),
      glob(sfConfig::get('sf_apps_dir') . '/*/lib' . $subdirectory),
      [
        sfConfig::get('sf_apps_dir') . '/lib' . $subdirectory,
        sfConfig::get('sf_lib_dir') . $subdirectory,
      ]
    );
  }

  /**
   * Returns all project config directories.
   *
   * @return string[]
   */
  protected function getProjectConfigDirectories(): array
  {
    return array_merge(
      glob(sfConfig::get('sf_apps_dir') . '/*/modules/*/config'),
      glob(sfConfig::get('sf_apps_dir') . '/*/config'),
      glob(sfConfig::get('sf_config_dir'))
    );
  }

  /**
   * Returns all application names.
   *
   * @return string[] An array of application names
   */
  protected function getApplications(): array
  {
    return sfFinder::type('dir')->maxdepth(0)->relative()->in(sfConfig::get('sf_apps_dir'));
  }
}
