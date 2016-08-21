<?php
include_once __DIR__ . '/../../src/bigdisp/scoreboard/scoreboard.php';

class scoreboard_test extends hwcontrol_test
{
	public function test_construct()
	{
		$hwcontrol = $this->get_instance();
		$this->assertInstanceOf("\bigdisp\scoreboard\scoreboard", $hwcontrol);
		$this->assertInstanceOf("\bigdisp\scoreboard\hwcontrol", $hwcontrol);
	}

	/**
	 * @depends test_construct
	 */
	public function test_count()
	{
		$hwcontrol = $this->get_instance();

		// Check internal state
		$this->assertEquals(array(
			'balls' => 0,
			'strikes' => 0,
		), $hwcontrol->get_count());

		$hwcontrol->set_balls(3);
		$this->assertEquals(array(
			'balls' => 3,
			'strikes' => 0,
		), $hwcontrol->get_count());

		$hwcontrol->set_strikes(2);
		$this->assertEquals(array(
			'balls' => 3,
			'strikes' => 2,
		), $hwcontrol->get_count());

		$hwcontrol->ball();
		$this->assertEquals(array(
			'balls' => 0,
			'strikes' => 0,
		), $hwcontrol->get_count());

		$hwcontrol->ball();
		$this->assertEquals(array(
			'balls' => 1,
			'strikes' => 0,
		), $hwcontrol->get_count());

		$hwcontrol->strike();
		$this->assertEquals(array(
			'balls' => 1,
			'strikes' => 1,
		), $hwcontrol->get_count());

		$hwcontrol->batter_save();
		$this->assertEquals(array(
			'balls' => 0,
			'strikes' => 0,
		), $hwcontrol->get_count());

		// Check commands sent:
		$this->assertResultfile("B 3\nS 2\nS 0\nB 0\nB 1\nS 1\nB 0\nS 0\n");
	}

	/**
	 * @depends test_count
	 */
	public function test_outs()
	{
		$hwcontrol = $this->get_instance();

		$this->assertEquals(0, $hwcontrol->get_outs());

		$hwcontrol->out();
		$this->assertEquals(1, $hwcontrol->get_outs());

		$hwcontrol->set_balls(1);
		$hwcontrol->batter_out();
		$this->assertEquals(2, $hwcontrol->get_outs());
		$this->assertEquals(array(
			'balls' => 0,
			'strikes' => 0,
		), $hwcontrol->get_count());

		$hwcontrol->set_outs(1);
		$this->assertEquals(1, $hwcontrol->get_outs());

		$hwcontrol->set_strikes(2);
		$hwcontrol->strike();
		$this->assertEquals(2, $hwcontrol->get_outs());

		// Inning skip:
		$this->assertEquals(1, $hwcontrol->get_inning());
		$this->assertEquals(true, $hwcontrol->get_halfinning(true));
		$this->assertEquals('top', $hwcontrol->get_halfinning());
		$hwcontrol->out();
		$this->assertEquals(1, $hwcontrol->get_inning());
		$this->assertEquals(false, $hwcontrol->get_halfinning(true));
		$this->assertEquals('bottom', $hwcontrol->get_halfinning());

		// Same for other half of inning:
		$hwcontrol->set_outs(2);
		$hwcontrol->out();
		$this->assertEquals(2, $hwcontrol->get_inning());
		$this->assertEquals(true, $hwcontrol->get_halfinning(true));
		$this->assertEquals('top', $hwcontrol->get_halfinning());

		// TODO resultfile test split up to make it more readable
		$this->assertResultfile("O 1\nB 1\nB 0\nS 0\nO 2\nO 1\nS 2\nB 0\nO 2\nS 0\n" .
			"B 0\nS 0\nIB\nO 0\nO 2\n" . // Inning skip t -> b
			"B 0\nS 0\nI 2\nIT\nO 0\n" // Inning skip b -> t
			);
	}

	/**
	 * @depends test_construct
	 */
	public function test_score()
	{
		$hwcontrol = $this->get_instance();
		$this->assertEquals(array(
			'home' => 0,
			'away' => 0,
		), $hwcontrol->get_score());

		$hwcontrol->set_score(2, 3);
		$this->assertEquals(array(
			'home' => 2,
			'away' => 3,
		), $hwcontrol->get_score());

		$hwcontrol->set_score(20, 30);
		$this->assertEquals(array(
			'home' => 20,
			'away' => 30,
		), $hwcontrol->get_score());

		$this->assertResultfile("RH 2\nRG 3\n" .
			"RH 20\nRG 30\n");
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
		$hwcontrol = new bigdisp\scoreboard\scoreboard($interface, $host, $port);
		$path = dirname(__FILE__) . '/../../travis/scoreboard';
		$hwcontrol->set_exec_path($path);
		$this->delResultfile();
		return $hwcontrol;
	}
}