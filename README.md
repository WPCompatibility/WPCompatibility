# WPCompatibility

`wpcompatibility` is a collection of PHP_CodeSniffer (PHPCS) sniffs designed to check if your WordPress plugin/theme code is compatible with specific WordPress versions.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Info](#Info)
- [Buy me a coffee ☕](https://buymeacoffee.com/naveen17797)

## Installation:
```shell
composer require wpcompatibility/wp-compatibility 
```

## Usage:

### Command
```shell
# For single wp version
WP_COMPAT_PHPCS_SUPPORTED_VERSIONS='5.0.0' vendor/bin/phpcs --standard=WPCompatibility your-plugin-or-theme-folder/

# To test against multiple wp version you separate value by comma
WP_COMPAT_PHPCS_SUPPORTED_VERSIONS='5.0.1,6.0' vendor/bin/phpcs --standard=WPCompatibility your-plugin-or-theme-folder/
```

### Ruleset File
```xml
<rule ref="WPCompatibility.Signature.Function">
    <properties>
        <property name="versions" value="4.3,6.1" />
    </properties>
</rule>
```
### Info
- Detect if a wordpress function is present in specific version. For example if you
have a plugin file with the below code which is set to be compatible with 5.0, this will
throw an error
```injectablephp
<?php wp_date(); ?>
```
```injectablephp
Function: wp_date is not available in wordpress version 5.0.0
```
- Verify wordpress function signature, if you pass less number of arguments to a wordpress function then it will raise an error.      

<p align="center">
![F_TwK0vbIAAShWP](https://github.com/WPCompatibility/WPCompatibility/assets/18109258/abeffd2c-0440-4774-a80d-70075a192820)
### [Buy me a coffee ☕](https://buymeacoffee.com/naveen17797)
</p>