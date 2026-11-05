<?php
/**
 * Simulates the complete chain of the example web interface:
 * HTTP POST -> src/example/index.php -> PHP API -> ./scoreboard CLI -> i2cset.
 *
 * The example is copied to a temporary directory, i2cset is replaced by
 * tests/fixtures/fake_bin.php which records all i2c transfers.
 *
 * @license see License.md
 */

class web_example_test extends PHPUnit_Framework_TestCase
{
	/** @var string */
	protected $dir;

	/** @var string */
	protected $log;

	protected function install()
	{
		$src = SCOREBOARD_ROOT . '/src';
		$this->dir = scoreboard_test_helper::temp_dir();
		scoreboard_test_helper::copy_dir("$src/bigdisp", $this->dir . '/bigdisp');
		mkdir($this->dir . '/example');
		foreach (array('index.php', 'output.php', 'scoreboard') as $file)
		{
			copy("$src/example/$file", $this->dir . "/example/$file");
		}
		// Run the CLI with the PHP binary executing the tests
		$cli = $this->dir . '/example/scoreboard';
		$code = preg_replace('/^#!.*\n/', '#!' . scoreboard_test_helper::php() . "\n", file_get_contents($cli));
		file_put_contents($cli, $code);
		chmod($cli, 0755);
	}

	/**
	 * Sends a POST request to the page.
	 *
	 * @param array $post
	 * @return array output, stderr and the i2c transfers caused by the request
	 */
	protected function post($post)
	{
		$this->log = scoreboard_test_helper::new_log();
		putenv('SCOREBOARD_I2CSET=' . scoreboard_test_helper::fake_bin());
		putenv('SB_POST=' . http_build_query($post));

		$cmd = escapeshellarg(scoreboard_test_helper::php())
			. ' -d display_errors=stderr -d error_reporting=-1'
			. ' -r ' . escapeshellarg('parse_str(getenv("SB_POST"), $_POST); include "index.php";');
		$proc = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, $this->dir . '/example');
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);
		putenv('SCOREBOARD_I2CSET');
		putenv('SB_POST');

		return array(
			'output' => $stdout,
			'stderr' => $stderr,
			'i2c' => scoreboard_test_helper::read_log_lines($this->log),
		);
	}

	/**
	 * @return \bigdisp\scoreboard\scoreboard_file the stored state
	 */
	protected function state()
	{
		return new \bigdisp\scoreboard\scoreboard_file($this->dir . '/example/scoreboard_data.txt');
	}

	protected function assertCleanResponse($response)
	{
		$this->assertSame('', $response['stderr'], 'No PHP errors or warnings');
		$this->assertSame(0, strpos(ltrim($response['output']), '<html>'), 'Nothing is printed before the page');
	}

	public function test_page_renders_without_request()
	{
		$this->install();
		$response = $this->post(array());
		$this->assertCleanResponse($response);
		$this->assertEquals(array(), $response['i2c']);
		$this->assertContains_compat('Current score: 0 : 0', $response['output']);
	}

	public function test_score_count_and_outs()
	{
		$this->install();
		$response = $this->post(array('score' => '+'));
		$this->assertCleanResponse($response);
		$this->assertEquals(array('-y 1 17 6 0', '-y 1 16 6 1'), $response['i2c']);

		$this->assertCleanResponse($this->post(array('balls' => '+')));
		$this->assertCleanResponse($this->post(array('outs' => '+')));

		$state = $this->state();
		$this->assertEquals(array('home' => 0, 'away' => 1), $state->get_score());
		$this->assertEquals(array('balls' => 1, 'strikes' => 0), $state->get_count());
		$this->assertEquals(1, $state->get_outs());
	}

	/**
	 * The "-" button for strikes used a wrong array key.
	 */
	public function test_strikes_minus_button()
	{
		$this->install();
		$this->post(array('strikes' => '+'));
		$this->post(array('strikes' => '+'));
		$response = $this->post(array('strikes' => '-'));
		$this->assertCleanResponse($response);
		$this->assertEquals(array('balls' => 0, 'strikes' => 1), $this->state()->get_count());
		$this->assertEquals(array('-y 1 19 12 64', '-y 1 19 10 4', '-y 1 19 11 64'), $response['i2c']);
	}

	public function test_minus_at_zero_stays_zero()
	{
		$this->install();
		$response = $this->post(array('balls' => '-'));
		$this->assertCleanResponse($response);
		$this->assertEquals(array('balls' => 0, 'strikes' => 0), $this->state()->get_count());
	}

	public function test_inning_buttons()
	{
		$this->install();
		$response = $this->post(array('inning' => '+'));
		$this->assertCleanResponse($response);
		$this->assertEquals(array('-y 1 18 11 16', '-y 1 18 12 32'), $response['i2c']);
		$this->assertEquals('bottom', $this->state()->get_halfinning());

		$this->post(array('inning' => '+'));
		$this->assertEquals(2, $this->state()->get_inning());
		$this->assertEquals('top', $this->state()->get_halfinning());
	}

	/**
	 * Reset has to reach the board (console interface) and clear the state.
	 */
	public function test_reset_clears_board()
	{
		$this->install();
		$this->post(array('score' => '+'));
		$this->post(array('balls' => '+'));
		$response = $this->post(array('reset' => '1', 'really-reset' => '1'));
		$this->assertCleanResponse($response);

		$i2c = $response['i2c'];
		$this->assertEquals('-y 1 17 6 0', $i2c[0]);
		$this->assertEquals('-y 1 16 6 0', $i2c[1]);
		$this->assertContains_compat('-y 1 18 6 1', $i2c);
		$this->assertContains_compat('-y 1 18 11 32', $i2c);
		$this->assertContains_compat('-y 1 19 0 0x8888 w', $i2c);

		$state = $this->state();
		$this->assertEquals(array('home' => 0, 'away' => 0), $state->get_score());
		$this->assertEquals(array('balls' => 0, 'strikes' => 0), $state->get_count());
	}

	public function test_reset_requires_confirmation()
	{
		$this->install();
		$this->post(array('score' => '+'));
		$response = $this->post(array('reset' => '1'));
		$this->assertEquals(array(), $response['i2c']);
		$this->assertEquals(array('home' => 0, 'away' => 1), $this->state()->get_score());
	}

	public function test_save_colors_ignores_invalid_values()
	{
		$this->install();
		$response = $this->post(array(
			'save-colors' => '1',
			'color-rh' => '#ff0000',
			'color-rg' => 'invalid',
		));
		$this->assertCleanResponse($response);
		$this->assertEquals(array(
			'-y 1 17 0 0x00FF w',
			'-y 1 17 2 0x00FF w',
			'-y 1 17 1 0x0',
			'-y 1 17 3 0x0',
		), $response['i2c']);

		$state = $this->state();
		$this->assertEquals('FF0000', $state->get_color('RH'));
		$this->assertEquals('888888', $state->get_color('RG'));
		$this->assertContains_compat('value="#FF0000"', $response['output']);
	}

	public function test_refresh_resends_state()
	{
		$this->install();
		$this->post(array('score' => '+'));
		$response = $this->post(array('refresh' => '1'));
		$this->assertCleanResponse($response);
		$i2c = $response['i2c'];
		$this->assertEquals('-y 1 17 6 0', $i2c[0]);
		$this->assertEquals('-y 1 16 6 1', $i2c[1]);
		$this->assertContains_compat('-y 1 18 6 1', $i2c);
	}

	protected function assertContains_compat($needle, $haystack)
	{
		if (is_array($haystack))
		{
			$this->assertTrue(in_array($needle, $haystack, true), "'$needle' not found in " . implode(', ', $haystack));
		}
		else
		{
			$this->assertTrue(strpos($haystack, $needle) !== false, "'$needle' not found");
		}
	}
}
