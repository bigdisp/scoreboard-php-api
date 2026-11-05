<?php
/**
 * Transport of the commands: console interface (executable) and daemon (socket).
 *
 * @license see License.md
 */

use bigdisp\scoreboard\hwcontrol;

class hwcontrol_transport_test extends PHPUnit_Framework_TestCase
{
	/**
	 * @return array scoreboard and log file
	 */
	protected function console_scoreboard()
	{
		$log = scoreboard_test_helper::new_log();
		$sb = new \bigdisp\scoreboard\scoreboard(hwcontrol::INTERFACE_CONSOLE);
		$sb->set_exec_path(scoreboard_test_helper::fake_bin());
		return array($sb, $log);
	}

	public function test_console_interface_runs_executable_with_arguments()
	{
		list($sb, $log) = $this->console_scoreboard();
		$sb->set_score(3, 12);
		$sb->set_inning_top();
		$sb->set_color('AB0976', 'RH');
		$sb->pwm_on();
		$sb->pwm_off();
		$sb->set_point('RH', false);

		$this->assertEquals(array(
			array('RH', '3'),
			array('RG', '12'),
			array('IT'),
			array('color', 'RH', 'AB0976'),
			array('pwm', 'on'),
			array('pwm', 'off'),
			array('point', 'RH', '0'),
		), scoreboard_test_helper::read_log($log));
	}

	/**
	 * Commands must not be echoed into the generated page.
	 */
	public function test_console_interface_prints_nothing()
	{
		list($sb, $log) = $this->console_scoreboard();
		ob_start();
		$sb->set_balls(2);
		$output = ob_get_clean();
		$this->assertSame('', $output);
		$this->assertEquals(array(array('B', '2')), scoreboard_test_helper::read_log($log));
	}

	/**
	 * Values are passed as arguments, never interpreted by the shell.
	 */
	public function test_console_interface_escapes_arguments()
	{
		list($sb, $log) = $this->console_scoreboard();
		$marker = sys_get_temp_dir() . '/sb_injection_' . uniqid();
		$sb->set_color("FFFFFF;touch $marker", 'RH');

		$this->assertFileNotExists_compat($marker);
		// Commands are space separated, each part arrives as a literal argument
		$this->assertEquals(array(
			array('color', 'RH', 'FFFFFF;touch', $marker),
		), scoreboard_test_helper::read_log($log));

		$sb->set_color('$(touch ' . $marker . ')', 'RG');
		$this->assertFileNotExists_compat($marker);
	}

	public function test_default_interface_is_daemon()
	{
		$server = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
		if (!$server)
		{
			$this->markTestSkipped("Cannot open local socket: $errstr");
		}
		$name = stream_socket_get_name($server, false);
		$port = (int) substr($name, strrpos($name, ':') + 1);

		$sb = new \bigdisp\scoreboard\scoreboard();
		$this->set_property($sb, 'port', $port);
		$this->set_property($sb, 'host', '127.0.0.1');

		ob_start();
		$sb->set_inning(4);
		$output = ob_get_clean();

		$client = stream_socket_accept($server, 2);
		$this->assertNotFalse_compat($client);
		$this->assertEquals('I 4', stream_get_contents($client));
		$this->assertSame('', $output);
		fclose($client);
		fclose($server);
	}

	public function test_daemon_interface_without_daemon_fails_silently()
	{
		// Port 1 on localhost is not expected to accept connections
		$sb = new \bigdisp\scoreboard\scoreboard(hwcontrol::INTERFACE_DAEMON, '127.0.0.1', 1);
		ob_start();
		$sb->set_balls(1);
		$this->assertSame('', ob_get_clean());
		$this->assertEquals(array('balls' => 1, 'strikes' => 0), $sb->get_count());
	}

	protected function set_property($object, $name, $value)
	{
		$property = new ReflectionProperty($object, $name);
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

	protected function assertFileNotExists_compat($file)
	{
		$this->assertFalse(file_exists($file), "File $file must not exist");
	}

	protected function assertNotFalse_compat($value)
	{
		$this->assertTrue($value !== false);
	}
}
