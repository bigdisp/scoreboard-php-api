<?php
/**
 * Command line interface src/example/scoreboard, simulated against a fake i2cset.
 *
 * The logged i2cset arguments are: -y <bus> <address> <command> [<value> [w]]
 *
 * @license see License.md
 */

class cli_test extends PHPUnit_Framework_TestCase
{
	/**
	 * Runs the CLI and returns exit code, stderr and the i2cset calls.
	 *
	 * @param array $args
	 * @return array
	 */
	protected function run_cli($args)
	{
		$log = scoreboard_test_helper::new_log();
		putenv('SCOREBOARD_I2CSET=' . scoreboard_test_helper::fake_bin());

		$cmd = escapeshellarg(scoreboard_test_helper::php())
			. ' -d display_errors=stderr -d error_reporting=-1 '
			. escapeshellarg(SCOREBOARD_ROOT . '/src/example/scoreboard');
		foreach ($args as $arg)
		{
			$cmd .= ' ' . escapeshellarg($arg);
		}

		$proc = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$code = proc_close($proc);
		putenv('SCOREBOARD_I2CSET');

		return array(
			'code' => $code,
			'stdout' => $stdout,
			'stderr' => $stderr,
			'i2c' => scoreboard_test_helper::read_log_lines($log),
		);
	}

	public function test_write_number()
	{
		$result = $this->run_cli(array('RH', '5'));
		$this->assertSame(0, $result['code']);
		$this->assertSame('', $result['stderr']);
		$this->assertEquals(array('-y 1 17 6 5'), $result['i2c']);

		$result = $this->run_cli(array('RG', '12'));
		$this->assertEquals(array('-y 1 16 6 12'), $result['i2c']);

		$result = $this->run_cli(array('I', '3'));
		$this->assertEquals(array('-y 1 18 6 3'), $result['i2c']);
	}

	public function test_color_of_runs()
	{
		$result = $this->run_cli(array('color', 'RH', 'FF0000'));
		$this->assertSame(0, $result['code']);
		$this->assertEquals(array(
			'-y 1 17 0 0x00FF w',
			'-y 1 17 2 0x00FF w',
			'-y 1 17 1 0x0',
			'-y 1 17 3 0x0',
		), $result['i2c']);
	}

	public function test_color_all_is_broadcast()
	{
		$result = $this->run_cli(array('color', 'all', '888888'));
		$this->assertCount(16, $result['i2c']);
		$this->assertEquals('-y 1 17 0 0x8888 w', $result['i2c'][0]);
		$this->assertEquals('-y 1 19 3 0x110 w', $result['i2c'][15]);
	}

	public function test_inning_markers()
	{
		$result = $this->run_cli(array('IT'));
		$this->assertEquals(array('-y 1 18 11 32', '-y 1 18 12 16'), $result['i2c']);

		$result = $this->run_cli(array('IB'));
		$this->assertEquals(array('-y 1 18 11 16', '-y 1 18 12 32'), $result['i2c']);
	}

	public function test_balls_strikes_outs()
	{
		$result = $this->run_cli(array('B', '2'));
		$this->assertEquals(array(
			'-y 1 19 12 1', '-y 1 19 12 4', '-y 1 19 12 16',
			'-y 1 19 11 1', '-y 1 19 11 4',
		), $result['i2c']);

		$result = $this->run_cli(array('S', '1'));
		$this->assertEquals(array('-y 1 19 12 64', '-y 1 19 10 4', '-y 1 19 11 64'), $result['i2c']);

		$result = $this->run_cli(array('O', '2'));
		$this->assertEquals(array(
			'-y 1 19 10 1', '-y 1 19 10 2',
			'-y 1 19 9 1', '-y 1 19 9 2',
		), $result['i2c']);
	}

	/**
	 * "blank" used to fall through into "text" and the whole "test" sequence.
	 */
	public function test_blank_only_blanks()
	{
		$result = $this->run_cli(array('blank'));
		$this->assertSame(0, $result['code']);
		$this->assertSame('', $result['stderr']);
		$this->assertEquals(array(
			'-y 1 17 8 32', '-y 1 16 8 32', '-y 1 18 8 32', '-y 1 19 8 32',
			'-y 1 17 7 32', '-y 1 16 7 32', '-y 1 18 7 32', '-y 1 19 7 32',
		), $result['i2c']);
	}

	public function test_text_writes_two_letters()
	{
		$result = $this->run_cli(array('text', 'RH', 'ab'));
		$this->assertSame(0, $result['code']);
		$this->assertSame('', $result['stderr']);
		$this->assertEquals(array('-y 1 17 8 97', '-y 1 17 7 98'), $result['i2c']);
	}

	public function test_values_are_not_interpreted_by_shell()
	{
		$marker = sys_get_temp_dir() . '/sb_cli_injection_' . uniqid();
		$result = $this->run_cli(array('RH', "5;touch $marker"));
		$this->assertFalse(file_exists($marker));
		$this->assertEquals(array("-y 1 17 6 5;touch $marker w"), $result['i2c']);
	}

	public function test_unknown_or_missing_command()
	{
		$result = $this->run_cli(array());
		$this->assertSame(1, $result['code']);
		$this->assertSame("ERROR: Unknown command: \n", $result['stderr']);
		$this->assertEquals(array(), $result['i2c']);

		$result = $this->run_cli(array('foo'));
		$this->assertSame(1, $result['code']);
		$this->assertSame("ERROR: Unknown command: foo\n", $result['stderr']);
	}

	public function test_too_few_arguments()
	{
		$result = $this->run_cli(array('color', 'RH'));
		$this->assertSame(2, $result['code']);
		$this->assertSame("ERROR: Too few arguments.\n", $result['stderr']);
		$this->assertEquals(array(), $result['i2c']);

		$result = $this->run_cli(array('B'));
		$this->assertSame(2, $result['code']);
		$this->assertEquals(array(), $result['i2c']);

		// Commands without arguments still work
		$result = $this->run_cli(array('IT'));
		$this->assertSame(0, $result['code']);
	}
}
