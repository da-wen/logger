logger
======

This is another logger class based on PSR logger standard. It was inspired by monolog (one of te best loggers in php)

The goal of this logger is to provide more than one logger. In some applications you need a very big complex logger
for handling this app. Another small projects only needs a simple file logger. This inspired me to create three logger types:

- VerySimpleLogger (it's a file stream logger in one file)
- AdvancedLogger (it's more testable and replaceable, but only one handler)
- ComplexLogger (it's completely testable and handles more than one handler)

Every logger implements the PSR LoggerInterface.


How to install
==============
The best way for installing is the usage of composer, for managing your dependencies.

Add or modify the require section of composer.json file:
```json
"require": {
        "da-wen/logger": "dev-master",
    },
```

Add or modify a new repository for the logger:
```json
"repositories": [
        {
            "url": "https://github.com/da-wen/logger.git",
            "type": "git"
        }
    ]
```

For getting the code run:
```
php path/to/composer.phar install
```

For keeping it up to date, if git de-master is set:
```
php path/to/composer.phar update
```


Usage
=====

