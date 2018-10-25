Upgrade guide 3.0 to 4.0
========================

1. Make sure you're using PHP 5.5.9+
   PHP 5.4 is not supported anymore.
   
2. Make sure you don't use `sfYaml` or `sfYamlInline` classes directly 
   in your project codebase. Or any of your or 3rd party plugins.
   If you do, switch the code to [Symfony / Yaml component](https://symfony.com/doc/current/components/yaml.html).

3. Make sure you escape `%` and `@` in all your YAML configuration files.
   This is a requirement of [Yaml spec v1.2](http://yaml.org/spec/1.2/spec.html). 

4. Upgrade `rock-symphony` dependency to `4.0`

    ```bash
    composer require rock-symphony/rock-symphony:^4.0
    ````
 


