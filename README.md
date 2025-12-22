<p align="center">
  <img src="docs/assets/logo_hand.png" alt="Rock Symphony"><br/>
  Rock with Symfony1 again.<br/>
  Just when you thought it's dead.
</p>


![Build Status](https://github.com/rock-symphony/rock-symphony/actions/workflows/test.yml/badge.svg)


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
- Replace legacy sf1 parts with modern libraries reducing framework footprint to the very minimum

Roadmap
-------

- ✓ ~~Add argument return type hints everywhere (improve IDE static analysis)~~
- ✓ ~~Fix phpdoc / code inconsistencies~~
- ✓ ~~composer support~~
- ✓ ~~Drop sfCoreAutoload~~
- ✓ ~~Replace sfYaml with Symfony\Yaml~~
- ✓ ~~Drop ArrayAccess~~
- ✓ ~~Drop Iterator implementation in favor of IteratorAggregate~~
- Properties, arguments and return types everywhere 
- Fix sfApplicationConfiguration / ProjectConfiguration
- Replace sfLogger with PSR Logger
- Decent service container, services auto-injection
- CommandBus + JobQueue
- Logging with Logger object (not via sfEventDispatcher)
- Drop sfContext
- Namespaced controllers
- PSR HTTP Requests
- .env
- symfony/console
- Replace services.yml with pure-PHP services.php 
- Drop module-level .yml configurations support
- Symfony2-like Bundles or Laravel-like ServiceProviders instead of plugins

Requirements
------------

Starting from PHP 5.6.0 and then goes up with every new major version.
The latest v11.0 release requires PHP 7.4.

Migrating to Rock Symphony
--------------------------

It's not recommended to start a new project with Rock Symphony.
It's only intended for old projects to migrate to. 

1. Start using [Composer](http://getcomposer.org/doc/00-intro.md) for your project,
   if you haven't done so yet.
   
2. Remove from your codebase symfony framework you use 
   (stock symfony1, lexpress/symfony1 or whatever you have).

3. Require `rock-symphony/rock-symphony`:

       composer require rock-symphony/rock-symphony:^1.6

4. Follow [Upgrade guide](./UPGRADE.md) to upgrade your codebase step by step:

   - `composer require rock-symphony/rock-symphony:^1.7`
   - `composer require rock-symphony/rock-symphony:^3.0` (note: version `2.0` is intentionally skipped)
   - `composer require rock-symphony/rock-symphony:^4.0`
   - `composer require rock-symphony/rock-symphony:^5.0`
   - `composer require rock-symphony/rock-symphony:^6.0`
   - `composer require rock-symphony/rock-symphony:^7.0`
   - `composer require rock-symphony/rock-symphony:^8.0`
   - `composer require rock-symphony/rock-symphony:^9.0`
   - `composer require rock-symphony/rock-symphony:^10.0`
   - `composer require rock-symphony/rock-symphony:^11.0`
   - `composer require rock-symphony/rock-symphony:^12.0`


Contributing
------------

You can send pull requests or create an issue.

Credits
-------

- Original symfony1 implementation by [SensioLabs](https://sensiolabs.com/)
- symfony1 v1.5 fork maintained by [L'Express Group](https://github.com/LExpress)
- *New Rocker* font by [Google Fonts Directory](https://fonts.google.com/specimen/New+Rocker)
- *Hand* icon by [Hum from TheNounProject](https://thenounproject.com/Hum/)
