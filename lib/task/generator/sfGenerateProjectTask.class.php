<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/sfGeneratorBaseTask.class.php');

/**
 * Generates a new project.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGenerateProjectTask extends sfGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function doRun(sfCommandManager $commandManager, $options)
  {
    $this->process($commandManager, $options);

    return $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());
  }

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The project name'),
      new sfCommandArgument('author', sfCommandArgument::OPTIONAL, 'The project author', 'Your name here'),
    ));

    $this->addOptions(array(
      new sfCommandOption('installer', null, sfCommandOption::PARAMETER_REQUIRED, 'An installer script to execute', null),
    ));

    $this->namespace = 'generate';
    $this->name = 'project';

    $this->briefDescription = 'Generates a new project';

    $this->detailedDescription = <<<EOF
The [generate:project|INFO] task creates the basic directory structure
for a new project in the current directory:

  [./symfony generate:project blog|INFO]

If the current directory already contains a symfony project,
it throws a [sfCommandException|COMMENT].

You can also pass the [--installer|COMMENT] option to further customize the
project:

  [./symfony generate:project blog --installer=./installer.php|INFO]

You can optionally include a second [author|COMMENT] argument to specify what name to
use as author when symfony generates new classes:

  [./symfony generate:project blog "Jack Doe"|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (file_exists('symfony'))
    {
      throw new sfCommandException(sprintf('A symfony project already exists in this directory (%s).', getcwd()));
    }

    if ($options['installer'] && $this->commandApplication && !file_exists($options['installer']))
    {
      throw new InvalidArgumentException(sprintf('The installer "%s" does not exist.', $options['installer']));
    }

    $this->arguments = $arguments;
    $this->options = $options;

    // create basic project structure
    $this->installDir(__DIR__.'/skeleton/project');

    // try to locate vendor/autoload.php
    $composerAutoload = $this->locateComposerAutoloadFile();

    $this->replaceTokens(array(sfConfig::get('sf_config_dir')), array(
      'COMPOSER_AUTOLOAD' => var_export(str_replace('\\', '/', $composerAutoload), true),
    ));

    $this->tokens = array(
      'PROJECT_NAME' => $this->arguments['name'],
      'AUTHOR_NAME'  => $this->arguments['author'],
      'PROJECT_DIR'  => sfConfig::get('sf_root_dir'),
    );

    $this->replaceTokens();

    // execute a custom installer
    if ($options['installer'] && $this->commandApplication)
    {
      if ($this->canRunInstaller($options['installer']))
      {
        $this->reloadTasks();
        include $options['installer'];
      }
    }

    // fix permission for common directories
    $fixPerms = new sfProjectPermissionsTask($this->dispatcher, $this->formatter);
    $fixPerms->setCommandApplication($this->commandApplication);
    $fixPerms->setConfiguration($this->configuration);
    $fixPerms->run();

    $this->replaceTokens();
  }

  protected function canRunInstaller($installer)
  {
    if (preg_match('#^(https?|ftps?)://#', $installer))
    {
      if (ini_get('allow_url_fopen') === false)
      {
        $this->logSection('generate', sprintf('Cannot run remote installer "%s" because "allow_url_fopen" is off', $installer));
      }
      if (ini_get('allow_url_include') === false)
      {
        $this->logSection('generate', sprintf('Cannot run remote installer "%s" because "allow_url_include" is off', $installer));
      }
      return ini_get('allow_url_fopen') && ini_get('allow_url_include');
    }
    return true;
  }

  private function locateComposerAutoloadFile()
  {
    $locations = array(
      __DIR__ . '/../../../vendor/autoload.php',
      __DIR__ . '/../../../../../vendor/autoload.php',
    );

    foreach ($locations as $location) {
      if (file_exists($location)) {
        return realpath($location);
      }
    }

    throw new sfCommandException('Cannot locate composer\'s vendor/autoload.php file');
  }
}
