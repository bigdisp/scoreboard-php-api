<?php
/**
 * Game logic of the scoreboard class and the commands it sends.
 * Complements tests/scoreboard/scoreboard_test.php.
 *
 * @license see License.md
 */

class scoreboard_game_test extends PHPUnit_Framework_TestCase
{
	protected function create()
	{
		return new recording_scoreboard(\bigdisp\scoreboard\hwcontrol::INTERFACE_CONSOLE);
	}





	/**
	 * Pressing "-" at zero must not lead to negative values on the board.
	 */
	public function test_counts_never_become_negative()
	{
		$sb = $this->create();
		$sb->set_balls(-1);
		$sb->set_strikes(-1);
		$sb->set_outs(-1);
		$this->assertEquals(array('balls' => 0, 'strikes' => 0), $sb->get_count());
		$this->assertEquals(0, $sb->get_outs());
		$this->assertEquals(array('B 0', 'S 0', 'O 0'), $sb->commands);
	}

	public function test_counts_wrap_like_before()
	{
		$sb = $this->create();
		$sb->set_balls(5);
		$sb->set_strikes(4);
		$sb->set_outs(3);
		$this->assertEquals(array('balls' => 1, 'strikes' => 1), $sb->get_count());
		$this->assertEquals(0, $sb->get_outs());
	}

	public function test_runs_update_score_and_linescore()
	{
		$sb = $this->create();
		$sb->add_run();
		$sb->add_run();
		$this->assertEquals(array('home' => 0, 'away' => 2), $sb->get_score());
		$this->assertEquals(array('RH 0', 'RG 1', 'RH 0', 'RG 2'), $sb->commands);

		$sb->set_inning_bottom();
		$sb->add_run();
		$sb->add_run(true);
		$sb->add_run();
		$this->assertEquals(array('home' => 1, 'away' => 2), $sb->get_score());

		$this->assertEquals(array(
			1 => array('home' => 1, 'away' => 2, 'inning' => 1),
		), $sb->get_linescore());
	}

	public function test_linescore_defaults_to_first_inning()
	{
		$sb = $this->create();
		$this->assertEquals(array(
			1 => array('home' => 0, 'away' => 0, 'inning' => 1),
		), $sb->get_linescore());
	}

	public function test_score_and_inning_are_numbers()
	{
		$sb = $this->create();
		$sb->set_score('3', '4; rm -rf /');
		$sb->set_inning('7x');
		$this->assertEquals(array('home' => 3, 'away' => 4), $sb->get_score());
		$this->assertSame(7, $sb->get_inning());
		$this->assertEquals(array('RH 3', 'RG 4', 'I 7'), $sb->commands);
	}

	public function test_inning_markers()
	{
		$sb = $this->create();
		$sb->set_inning_bottom();
		$this->assertEquals('bottom', $sb->get_halfinning());
		$this->assertFalse($sb->get_halfinning(true));
		$sb->set_inning_top();
		$this->assertEquals('top', $sb->get_halfinning());
		$this->assertTrue($sb->get_halfinning(true));
		$this->assertEquals(array('IB', 'IT'), $sb->commands);
	}

	public function test_colors()
	{
		$sb = $this->create();
		$this->assertEquals('888888', $sb->get_color('RH'));

		$sb->set_color('FF0000', 'RH');
		$this->assertEquals('FF0000', $sb->get_color('RH'));
		$this->assertEquals('888888', $sb->get_color('RG'));

		$sb->set_color('00FF00');
		$this->assertEquals('00FF00', $sb->get_color('RH'));
		$this->assertEquals('00FF00', $sb->get_color('RG'));
		$this->assertEquals(array('color RH FF0000', 'color all 00FF00'), $sb->commands);
	}
}
