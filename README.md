# WPCompatibility

`wpcompatibility` is a collection of PHP_CodeSniffer (PHPCS) sniffs designed to check if your WordPress plugin/theme code is compatible with specific WordPress versions.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Functions](#Functions)
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

# To test against multiple wp versions you can separate versions by comma
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
### Functions
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


### [Buy me a coffee ☕](https://buymeacoffee.com/naveen17797)

![F_TwK0vbIAAShWP](https://github.com/WPCompatibility/WPCompatibility/assets/18109258/abeffd2c-0440-4774-a80d-70075a192820)

