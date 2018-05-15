# phpunused

A tool for attempting to identify unreferenced files and unused functions in a PHP codebase.

This is by no means exact, and the output is a report that should be checked manually.

## Requirements

* PHP 5.4 (probably)
* grep (so POSIX?)

## Usage

Run from the top directory of the project you want to check. Like so:

```php ~/repos/phpunused/phpunused.php```

## State

This is a quick hack to help me analyze a code base. Pull requests are welcome,
but apart from that, new features are unlikely.
