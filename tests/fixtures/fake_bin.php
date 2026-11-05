<?php
/**
 * Simulated executable for the tests.
 *
 * Used in place of `/usr/sbin/i2cset` and of the `scoreboard` command line
 * interface. Every invocation appends its arguments as one JSON encoded line
 * to the file named in the environment variable SCOREBOARD_TEST_LOG.
 *
 * @license see License.md
 */
$log = getenv('SCOREBOARD_TEST_LOG');
if ($log)
{
	file_put_contents($log, json_encode(array_slice($argv, 1)) . "\n", FILE_APPEND);
}
