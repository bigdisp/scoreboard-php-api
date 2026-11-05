# PHP Scoreboard API
This API 


## Installation

We will make this project available via composer on packagist at a later stage. You can manually add it for now by [adding this project to your composer.json as a custom package](https://getcomposer.org/doc/05-repositories.md#vcs).

## Overview

The API consists of the following parts:

| Part | Purpose |
|------|---------|
| `src/bigdisp/scoreboard/hwcontrol.php` | Low level transport. Sends commands either to the `scoreboard` command line interface (`INTERFACE_CONSOLE`) or via TCP to a daemon (`INTERFACE_DAEMON`, default, `localhost:44322`). |
| `src/bigdisp/scoreboard/scoreboard.php` | Game logic: runs, linescore, inning, balls, strikes and outs. |
| `src/bigdisp/scoreboard/scoreboard_file.php` / `scoreboard_db.php` | Persist the scoreboard state in a file or a database. |
| `src/example/scoreboard` | Command line interface translating commands such as `RH 5` or `color RH FF0000` into `i2cset` calls for the atmega boards. |
| `src/example/index.php` | Example web interface. |
| `csrc/main.c` | Helper (setuid root) that blanks all digits and shuts down the Raspberry Pi. |

All values given to the console interface are escaped before they are passed
to the shell, so user input from the web interface cannot execute commands.

### Command line interface

    scoreboard RH|RG|I <number>          Write a number to home runs, guest runs or inning
    scoreboard RH1|RH10|... <symbol>     Write a single symbol to one digit
    scoreboard B|S|O <count>             Show balls, strikes or outs
    scoreboard IT|IB                     Mark top or bottom of the inning
    scoreboard color <target> <RRGGBB>   Set the color (target `all` for all boards)
    scoreboard text <target> <ab>        Write two letters
    scoreboard blank                     Blank all digits
    scoreboard test                      Run a display test

The `i2cset` binary defaults to `/usr/sbin/i2cset`. It can be replaced by setting
the environment variable `SCOREBOARD_I2CSET`, which is used by the tests to
simulate the i2c bus.

## Development

If you find a bug, please report it on https://github.com/bigdisp/scoreboard-php-api/issues

You can also suggest new features by filing an issue. Feel free to send a pull request for any existing feature request, but please add a note to the issue that you are doing so. If possible, please also add corresponding unit tests to your PR.

If you wish to contribute, feel free to send a pull request. Your contribution will be automatically checked using our automated testing. Please be sure to add tests for your new added features as well.

## Automated Testing

We use automated unit tests including functional tests to prevent regressions.

Run them with:

    composer install
    vendor/bin/phpunit

No hardware is required. The tests replace the hardware access by simulations:

* `tests/scoreboard/`: unit tests of the API classes. The console interface
  is pointed to a fake executable that records the commands it receives, the
  daemon interface is tested against a local TCP socket and the database
  against an in-memory fake.
* `tests/example/cli_test.php`: runs the `scoreboard` command line interface
  with a simulated `i2cset` (see `SCOREBOARD_I2CSET`) and checks the exact
  i2c transfers (address, command, value).
* `tests/example/web_example_test.php`: simulates the complete chain
  HTTP POST -> `index.php` -> API -> `scoreboard` CLI -> `i2cset` for the
  buttons of the web interface (score, count, outs, inning, colors, reset,
  refresh). Check out our travis build below:

master: [![Build Status](https://travis-ci.org/bigdisp/scoreboard-php-api.png?branch=master)](http://travis-ci.org/bigdisp/scoreboard-php-api)

## License

[See License.md](License.md)
