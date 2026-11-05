<?php
/**
 * Compatibility of the API (console interface) with the command line interface
 * src/example/scoreboard: every command hwcontrol/scoreboard emits must be
 * understood by the CLI and end up as I2C transfers (simulated i2cset).
 *
 * @license see License.md
 */
class api_cli_compat_test extends PHPUnit_Framework_TestCase
{
	use known_bug_trait;

	public function test_score_and_inning()
	{
		$this->assertSame(
			array('-y 1 17 6 3', '-y 1 16 6 12', '-y 1 18 6 7'),
			$this->run_api(function ($sb) {
				$sb->set_score(3, 12);
				$sb->set_inning(7);
			})
		);
	}

	public function test_pwm_on_off_level()
	{
		// hwcontrol sends "pwm on|off|<level>", the CLI has no "pwm" command
		// (exit 1, "Unknown command") -> nothing reaches the boards.
		// Firmware: 0x04 = pwm off, 0x05 = pwm on, broadcast to all 4 boards.
		$this->known_bug('CLI does not implement the pwm command', function () {
			$calls = $this->run_api(function ($sb) {
				$sb->pwm_off();
				$sb->pwm_on();
			});
			$this->assertSame(array(
				'-y 1 17 4', '-y 1 16 4', '-y 1 18 4', '-y 1 19 4',
				'-y 1 17 5', '-y 1 16 5', '-y 1 18 5', '-y 1 19 5',
			), $calls);
		});
	}

	public function test_point()
	{
		// hwcontrol sends "point <digit> 0|1", the CLI has no "point" command
		$this->known_bug('CLI does not implement the point command', function () {
			$calls = $this->run_api(function ($sb) {
				$sb->set_point('RH', true);
			});
			$this->assertCount(1, $calls);
		});
	}

	/**
	 * Run API calls with the real CLI and the simulated i2cset.
	 *
	 * @param callable $fn
	 * @return array i2cset calls
	 */
	protected function run_api(callable $fn)
	{
		$log = scoreboard_test_helper::new_log();
		putenv('SCOREBOARD_I2CSET=' . scoreboard_test_helper::fake_bin());
		$sb = new \bigdisp\scoreboard\scoreboard(\bigdisp\scoreboard\hwcontrol::INTERFACE_CONSOLE);
		$sb->set_exec_path(escapeshellarg(scoreboard_test_helper::php()) . ' '
			. escapeshellarg(SCOREBOARD_ROOT . '/src/example/scoreboard') . ' 2>/dev/null');
		try
		{
			$fn($sb);
		}
		finally
		{
			putenv('SCOREBOARD_I2CSET');
		}
		$calls = scoreboard_test_helper::read_log_lines($log);
		unlink($log);
		return $calls;
	}
}
