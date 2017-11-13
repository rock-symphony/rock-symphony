Upgrade guide 1.7 to 2.0
========================

```
composer require rock-symphony/rock-symphony:v2.0.0-alpha3
```

## Service Container rewritten

**ServiceContainer class, interface and most of service-container related code 
  and functionality is BC incompatible.**

1. *sfServiceContainer* interface has changed.

   - *sfServiceContainer* is now implementing PSR Container Interface. 
     So better interoperability.
   - *sfServiceContainer* is using 
     [RockSymphony Container](https://github.com/rock-symphony/container/tree/2.0.0#basics) now.
     Please read the docs on available functionality.

2. Dropped support of *parameters* section inside *services.yml*.
   Please move them to settings.yml configuration instead.  

3. *sfContext::setServiceContainerConfiguration(array $config)* was dropped.
   If your code was using it, please use 
   *sfContext::setServiceContainerResolver(Closure $resolver)* instead.

4. All the *service.yml* handling code was rewritten from scratch. Backwards incompatible.
   This includes:
   
   - *sfServiceContainerConfigParserInterface* 
   - *sfServiceContainerConfigParser*
   - *sfServiceContainerDumperInterface* 
   - *sfServiceContainerDumperPhp* is now generating a closure function
   - *sfContext* is now expecting a resolver closure to provide a container instance.
    
   If your project is using/extending them, please make sure you adapt your code
   accordingly.

5. *service.yml* stock config handler options have been changed
   
   - instead of *class* and *base_class* it now has: `class` the service container class to be instantiated.
   - added `parser` option to replace parser implementation by your own: 
     an object with `class` and `arguments` props.
   - added `dumper` option to replace dumper implementation by your own:
     an object with `class` and `arguments` props.
     
   Usage:
   
   ```yaml
   # config_handlers.yml
   config/services.yml:
     class:    sfServiceConfigHandler
     param:
       class: sfServiceContainer
       parser: { class: MyServiceContainerConfigParser }
       dumper: { class: MyServiceContainerDumperPhp, arguments: { indent: 4 } }
   ```   

## sfCoreAutoload dropped

*sfCoreAutoload* auto-loading functionality has been dropped. 
All the classes are auto-loaded with [composer](https://getcomposer.org/) now.
Remove all the mentions of *sfCoreAutoload* from your codebase.   
