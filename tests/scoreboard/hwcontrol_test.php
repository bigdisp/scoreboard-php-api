<?php


class hwcontrol_test extends PHPUnit_Framework_TestCase
{
	public function test_construct()
	{
		$hwcontrol = $this->get_instance();
		$this->assertInstanceOf("\bigdisp\scoreboard\hwcontrol", $hwcontrol);
	}

	/**
	 * @depends test_construct
	 */
	public function test_cmd()
	{
		$hwcontrol = $this->get_instance();
		$hwcontrol->set_point(1);
		$hwcontrol->set_point(1, 0);
		$this->assertResultfile("point 1 1\npoint 1 0\n");
	}

	/**
	 * @depends test_cmd
	 */
	public function test_pwm()
	{
		$hwcontrol = $this->get_instance();
		$hwcontrol->pwm_on();
		$hwcontrol->pwm_off();
		$hwcontrol->pwm_level(1);
		$this->assertResultfile("pwm on\npwm off\npwm 1\n");
	}

	/**
	 * @depends test_cmd
	 */
	public function test_color()
	{
		$hwcontrol = $this->get_instance();
		$hwcontrol->set_color("aabbcc");
		$hwcontrol->set_color("ddeeff", 1);
		$this->assertResultfile("color all aabbcc\ncolor 1 ddeeff\n");
	}

	/**
	 * @depends test_cmd
	 */
	public function test_value()
	{
		$hwcontrol = $this->get_instance();
		$hwcontrol->set_value(1, 2);
		$hwcontrol->set_value(2, 3);
		$this->assertResultfile("1 2\n2 3\n");
	}

	public function test_interface()
	{
		$this->markTestIncomplete();
	}

	protected function delResultfile()
	{
		$resultsfile = dirname(__FILE__) . '/../log/cmd.log';
		if (file_exists($resultsfile))
		{
			unlink($resultsfile);
		}
	}

	protected function assertResultfile($expected)
	{
		$resultsfile = dirname(__FILE__) . '/../log/cmd.log';
		$file = file_get_contents($resultsfile);
		$this->assertEquals($expected, $file);
	}

	/**
	 * Return an instance of the hwcontrol class.
	 *
	 * @param unknown $interface
	 * @param string $host
	 * @param number $port
	 */
	protected function get_instance($interface = \bigdisp\scoreboard\hwcontrol::INTERFACE_CONSOLE, $host = 'localhost', $port = 44322)
	{
		$hwcontrol = new bigdisp\scoreboard\hwcontrol($interface, $host, $port);
		$path = dirname(__FILE__) . '/../../travis/scoreboard';
		$hwcontrol->set_exec_path($path);
		$this->delResultfile();
		return $hwcontrol;
	}
}