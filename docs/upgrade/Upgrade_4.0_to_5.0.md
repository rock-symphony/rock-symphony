Upgrade guide 4.0 to 5.0
========================

1. Make sure you're running PHP 5.6.0+.
   And that your code is compatible with PHP 5.6.
   
   Check [the official PHP 5.6 migration guide](http://php.net/migration56). 

2. Do not rely on symfony autoloader mechanism anymore. 
   As it has been completely dropped.
   Please use [composer autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) instead.
   
   - List all the autoloaded `lib` folders in your *composer.json* `autload` section.

   - Make sure you have zero class names collisions. 
     
     Especially check `myUser` implementations. 
     If you have followed the official documentation, you have duplications. 
     Every application should have unique user class name.
     
   - Better start using [PSR-4](https://www.php-fig.org/psr/psr-4/).

   - Don't forget to run `composer dump-autoload` after you introduce a new global-namespace class.
     You'll probably need this after generating new ORM models.
     
   - You can use `file` config option for filters. Like this: 
     [test/functional/fixtures/apps/frontend/modules/configFiltersSimpleFilter/config/filters.yml](https://github.com/rock-symphony/rock-symphony/blob/d62f1348/test/functional/fixtures/apps/frontend/modules/configFiltersSimpleFilter/config/filters.yml#L9)
    
3. Make sure you're not relying on symfony's built-in plugin management.
   Use [composer](https://getcomposer.org/) instead.
   
   Make sure you don't use any of the deleted classes or methods or properties.
   See the complete list in the Pull Request description: 
   [#17](https://github.com/rock-symphony/rock-symphony/pull/17).
   
4. Make sure your project plugins don't rely on symfony's testing commands.
   See the complete list of dropped stuff in 
   [#17](https://github.com/rock-symphony/rock-symphony/pull/18).

5. Make sure you don't rely on symfony's response compression (`sf_compressed` setting).
   Move compression to your webserver instead.

6. Make sure your code is using neither *APC* nor *eAccelerator* sfCache implementations.
   You can switch to `sfAPCuCache`. 

7. Make sure you don't use `sfServiceContainerDumperGraphviz`.
