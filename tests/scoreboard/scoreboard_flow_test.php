<?php
/**
 * Game flow over several plate appearances (complements scoreboard_game_test.php).
 *
 * @license see License.md
 */
class scoreboard_flow_test extends PHPUnit_Framework_TestCase
{
	public function test_walk_resets_count()
	{
		$sb = $this->get_scoreboard();
		$sb->ball();
		$sb->ball();
		$sb->ball();
		$sb->strike();
		$this->assertSame(array('balls' => 3, 'strikes' => 1), $sb->get_count());
		$sb->ball();
		$this->assertSame(array('balls' => 0, 'strikes' => 0), $sb->get_count());
		$this->assertSame(0, $sb->get_outs());
	}

	public function test_strikeout_adds_out()
	{
		$sb = $this->get_scoreboard();
		$sb->ball();
		$sb->strike();
		$sb->strike();
		$sb->strike();
		$this->assertSame(1, $sb->get_outs());
		$this->assertSame(array('balls' => 0, 'strikes' => 0), $sb->get_count());
	}

	public function test_six_outs_complete_an_inning()
	{
		$sb = $this->get_scoreboard();
		for ($i = 0; $i < 3; $i++)
		{
			$sb->batter_out();
		}
		$this->assertSame(1, $sb->get_inning());
		$this->assertSame('bottom', $sb->get_halfinning());
		for ($i = 0; $i < 3; $i++)
		{
			$sb->batter_out();
		}
		$this->assertSame(2, $sb->get_inning());
		$this->assertSame('top', $sb->get_halfinning());
		$this->assertSame(0, $sb->get_outs());
	}

	public function test_skipped_innings_appear_in_linescore()
	{
		$sb = $this->get_scoreboard();
		$sb->set_inning(3);
		$sb->add_run();
		$linescore = $sb->get_linescore();
		$this->assertSame(array(1, 2, 3), array_keys($linescore));
		$this->assertSame(0, $linescore[2]['away']);
		$this->assertSame(1, $linescore[3]['away']);
	}

	protected function get_scoreboard()
	{
		scoreboard_test_helper::new_log();
		$sb = new \bigdisp\scoreboard\scoreboard(\bigdisp\scoreboard\hwcontrol::INTERFACE_CONSOLE);
		$sb->set_exec_path(scoreboard_test_helper::fake_bin());
		return $sb;
	}
}
