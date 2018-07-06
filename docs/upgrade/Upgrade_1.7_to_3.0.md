Upgrade guide 1.7 to 3.0
========================

1. Make sure you're using PHP 5.4+
   PHP 5.3 is not supported anymore.
   
2. Make sure you don't use `sfComposerAutoload` inside your project 
   or any of your or 3rd party plugins.

3. Upgrade `rock-symphony` dependency to `3.0`

    ```bash
    composer require rock-symphony/rock-symphony:3.0
    ````
 
## Changelog

* Drop PHP 5.3 support
* Drop sfCoreAutoload and rely on Composer instead
* Drop sfDoctrinePlugin support and mentions

