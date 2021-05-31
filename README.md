# I18nComparatorBundle
Utility for comparison of duplicated translation files in Symfony projects.

### Use case
The case this util resolves is very specific - it's intended to help find differences between 
translation files belonging to the same domain and language, but saved in different formats 
(messages.en.xlf and messages.en.yml, for example).

Note: Such differences should not occur under usual circumstances when using symfony/translation component.
Please see the _Example_ section below and check if this bundle actually solves your problem.

### Installation
You can add this bundle into your Symfony project using Composer:
```bash
composer require pfcode/symfony-i18n-comparator
```

This bundle requires at least Symfony 4.4 or 5.0 and PHP 7.1 or later and it's not intended to be used 
on production environments.

### Example
Let's say you have two translation files in your project, that belong to the same domain and are in the same language:

_translations/messages.en.xlf_:
```xml
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
    <file source-language="fr" target-language="en" datatype="plaintext" original="file.ext">
        <header>
            <tool tool-id="symfony" tool-name="Symfony"/>
        </header>
        <body>
            <trans-unit id="XXXXXXX" resname="nav.home">
                <source>nav.home</source>
                <target>Dashboard</target>
            </trans-unit>
            <trans-unit id="YYYYYY" resname="nav.login">
                <source>nav.login</source>
                <target>Sign In</target>
            </trans-unit>
            <trans-unit id="ZZZZZZ" resname="nav.logout">
                <source>nav.logout</source>
                <target>Logout</target>
            </trans-unit>
        </body>
    </file>
</xliff>
```

_translations/messages.en.yml_:
```yaml
nav:
  home: 'Home'
  login: 'Sign In'
  logout: 'Sign Out'
  search: 'Search something...'
```

These translation files obviously have some differences. One of them has nav.search key missing and other messages have 
different values. Comparing those files in a text editor would be painful.

To compare them at the logical level (translation keys, that is), you can use a command provided by this bundle:
```
bash-4.4# bin/console i18n-comparator:find-conflicts --translations-dir=translations
+---------------------+-- messages.en ------+-----------------+
| Key                 | messages.en.yml     | messages.en.xlf |
+---------------------+---------------------+-----------------+
| messages.nav.home   | Home                | Dashboard       |
| messages.nav.logout | Sign Out            | Logout          |
| messages.nav.search | Search something... |                 |
+---------------------+---------------------+-----------------+
```

With this command, searching for differences across various translation formats should be a lot easier.

### Supported translation formats
This bundle registers a ComparatorFactory service and passes references to all known translation loaders 
(all services tagged by `translation.loader`) to it. So it supports all formats 
that your Symfony installation already does.

### License
This project is published under the MIT license.