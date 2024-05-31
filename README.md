# wpcompatibility

`wpcompatibility` is a collection of PHP_CodeSniffer (PHPCS) sniffs designed to check if your WordPress plugin/theme code is compatible with specific WordPress versions.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Buy Me a Coffee](#donate)

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