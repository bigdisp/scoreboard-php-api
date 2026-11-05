<?php
/**
 * Test helpers: known bug markers and running the command line interface.
 *
 * @license see License.md
 */
trait known_bug_trait
{
	/**
	 * Run assertions that describe the intended behaviour but are known to fail
	 * today (known bug). The test is then reported as incomplete ("I") instead of
	 * failing the suite. Once the bug is fixed, the test fails and asks to remove
	 * the marker (so a fix is never left undocumented).
	 *
	 * @param string $reason
	 * @param callable $assertions
	 */
	protected function known_bug($reason, callable $assertions)
	{
		try
		{
			$assertions();
		}
		catch (\PHPUnit\Framework\AssertionFailedError $e)
		{
			$this->markTestIncomplete('Known bug: ' . $reason . ' (' . $e->getMessage() . ')');
		}
		catch (\Throwable $e)
		{
			$this->markTestIncomplete('Known bug: ' . $reason . ' (' . get_class($e) . ': ' . $e->getMessage() . ')');
		}
		$this->fail('Known bug seems to be fixed, remove the known_bug() marker: ' . $reason);
	}

	/**
	 * Run src/example/scoreboard with the simulated i2cset.
	 *
	 * @param array $args
	 * @return array exit code, i2cset calls (one string per call), stderr
	 */
	protected function run_cli(array $args)
	{
		$log = scoreboard_test_helper::new_log();
		putenv('SCOREBOARD_I2CSET=' . scoreboard_test_helper::fake_bin());

		$cmd = escapeshellarg(scoreboard_test_helper::php()) . ' '
			. escapeshellarg(SCOREBOARD_ROOT . '/src/example/scoreboard');
		foreach ($args as $arg)
		{
			$cmd .= ' ' . escapeshellarg($arg);
		}
		$proc = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
		stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$exit = proc_close($proc);
		putenv('SCOREBOARD_I2CSET');

		$calls = scoreboard_test_helper::read_log_lines($log);
		unlink($log);
		return array($exit, $calls, $stderr);
	}

	/**
	 * Assert the exact i2cset calls of one CLI invocation.
	 *
	 * @param array $args
	 * @param array $expected_calls
	 */
	protected function assertCli(array $args, array $expected_calls)
	{
		list($exit, $calls, $stderr) = $this->run_cli($args);
		$name = 'scoreboard ' . implode(' ', $args);
		$this->assertSame(0, $exit, "$name: exit code (stderr: $stderr)");
		$this->assertSame($expected_calls, $calls, "$name: i2cset calls");
	}
}
