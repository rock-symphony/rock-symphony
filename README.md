<p align="center">
  <img src="docs/assets/logo_hand.png" alt="Rock Symphony"><br/>
  Rock with Symfony1 again.<br/>
  Just when you thought it's dead.
</p>


[![Build Status](https://travis-ci.org/rock-symphony/rock-symphony.svg?branch=master)](https://travis-ci.org/rock-symphony/rock-symphony)


RockSymphony Framework
======================

It's a fork of [symfony1](https://github.com/lexpress/symfony1) that will move forward.

Why not use Symfony2+?
----------------------

We have a rather big project running on symfony1 you cannot just throw out everything.  
This fork is intended to move still-running and still-in-development legacy projects forward 
to modern development best-practices.

If you start a new project, consider using latest Laravel, Symfony 
or another *modern maintained* framework of your choice.

Philosophy
----------

- [Semantic versioning](http://semver.org/)
- Incremental BC-breaking updates that bring something new to your symfony1 project
- PHP 7.1 compatibility
- Replace legacy sf1 parts with modern libraries reducing framework footprint to the very minimum

Roadmap
-------

- ✓ ~~Add argument return type hints everywhere (improve IDE static analysis)~~
- ✓ ~~Fix phpdoc / code inconsistencies~~
- ✓ ~~composer support~~
- ✓ ~~Drop sfCoreAutoload~~
- ✓ ~~Replace sfYaml with Symfony\Yaml~~
- Replace sfLogger with PSR Logger
- Descent service container, services auto-injection
- CommandBus + JobQueue
- Logging with Logger object (not via sfEventDispatcher)
- Namespaced controllers
- PSR HTTP Requests
- .env
- Fix sfContext
- Fix sfApplicationConfiguration / ProjectConfiguration
- symfony/console
- Replace services.yml with pure-PHP services.php 
- Drop module-level .yml configurations support
- Symfony2-like Bundles instead of plugins

Requirements
------------

PHP 5.5.9 and up. See prerequisites on http://symfony.com/legacy/doc/getting-started/1_4/en/02-Prerequisites

Migrating to Rock Symphony
--------------------------

It's not recommended to start a new project with Rock Symphony.
It's only intended for old projects to migrate to. 

1. Start using [Composer](http://getcomposer.org/doc/00-intro.md) for your project,
   if you haven't done so yet.
   
2. Remove from your codebase symfony framework you use 
   (stock symfony1, lexpress/symfony1 or whatever you have).

3. Require `rock-symphony/rock-symphony`:

       composer require rock-symphony/rock-symphony "^3.0"


Contributing
------------

You can send pull requests or create an issue.

Credits
-------

- Original symfony1 implementation by [SensioLabs](https://sensiolabs.com/)
- symfony1 v1.5 fork maintained by [L'Express Group](https://github.com/LExpress)
- RockSymphony logo font by [Google Fonts Directory](https://fonts.google.com/specimen/New+Rocker)
- RockSymphony logo hand icon by [Hum from TheNounProject](https://thenounproject.com/Hum/)
