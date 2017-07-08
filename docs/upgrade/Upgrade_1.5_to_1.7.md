Upgrade guide 1.5 to 1.7
========================

## Start using composer if you haven't yet

a. Install *composer* binary. 
   Get installation instructions for your OS on [getcomposer.org](https://getcomposer.org/doc/00-intro.md).
   
   From here and after I assume you did a *global installation* of composer (see instructions above).

b. Init project `composer.json`
   
   ```bash
   composer init
   ```
   
c. Require *rock-symphony/rock-symphony*

   ```bash
   composer require rock-symphony/rock-symphony:1.7
   ```

d. Commit `composer.json` and `composer.lock` files.

e. Make sure you require `vendor/autoload.php` in your *config/ProjectConfiguration.php*:

   ```php
   
   require_once __DIR__ . '/../vendor/autoload.php';
   
   // ...
   
   class ProjectConfiguration extends sfProjectConfiguration {
      // ...
   }
   
   ```

## Changelog

### 1. Git sub-module for swiftmailer has been dropped
 
Now you need to rely on composer autoload to get swiftmailer running.

Remove all mentions of *vendor/swiftmailer* in your project codebase. 
SwiftMailer is now autoloaded via Composer.
