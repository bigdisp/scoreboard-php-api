<?php
/**
 * Bootstrap for the unit tests.
 *
 * @license see License.md
 */

$root = dirname(__DIR__);
define('SCOREBOARD_ROOT', $root);
if (file_exists($root . '/vendor/autoload.php'))
{
	require_once $root . '/vendor/autoload.php';
}

// The database driver is an external dependency, provide a minimal interface.
if (!interface_exists('\gn36\db\driver\dbal_interface'))
{
	require_once __DIR__ . '/fixtures/dbal_interface.php';
}

require_once $root . '/src/bigdisp/scoreboard/hwcontrol.php';
require_once $root . '/src/bigdisp/scoreboard/scoreboard.php';
require_once $root . '/src/bigdisp/scoreboard/scoreboard_file.php';
require_once $root . '/src/bigdisp/scoreboard/scoreboard_db.php';

// Support old (PHPUnit_Framework_TestCase) and new (namespaced) PHPUnit versions.
if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('\PHPUnit\Framework\TestCase'))
{
	class_alias('\PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

require_once __DIR__ . '/test_helper.php';
require_once __DIR__ . '/known_bug_trait.php';
