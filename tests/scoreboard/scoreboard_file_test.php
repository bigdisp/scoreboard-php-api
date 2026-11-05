<?php
/**
 * Persistence of the scoreboard state in a file.
 *
 * @license see License.md
 */

use bigdisp\scoreboard\hwcontrol;

class scoreboard_file_test extends PHPUnit_Framework_TestCase
{
	protected function create($file)
	{
		scoreboard_test_helper::new_log();
		$sb = new \bigdisp\scoreboard\scoreboard_file($file, hwcontrol::INTERFACE_CONSOLE);
		$sb->set_exec_path(scoreboard_test_helper::fake_bin());
		return $sb;
	}

	public function test_state_survives_store_and_read()
	{
		$file = tempnam(sys_get_temp_dir(), 'sb_data_');
		unlink($file);

		$sb = $this->create($file);
		$sb->add_run();
		$sb->set_inning_bottom();
		$sb->add_run();
		$sb->ball();
		$sb->strike();
		$sb->out();
		$sb->set_color('FF0000', 'S');
		$sb->store_data();

		$restored = $this->create($file);
		$this->assertEquals(array('home' => 1, 'away' => 1), $restored->get_score());
		$this->assertEquals(array('balls' => 1, 'strikes' => 1), $restored->get_count());
		$this->assertEquals(1, $restored->get_outs());
		$this->assertEquals('bottom', $restored->get_halfinning());
		$this->assertEquals('FF0000', $restored->get_color('S'));
		$this->assertEquals('888888', $restored->get_color('B'));
		$this->assertEquals(array(
			1 => array('home' => 1, 'away' => 1, 'inning' => 1),
		), $restored->get_linescore());
		unlink($file);
	}

	/**
	 * The web example truncates the file on reset, this must give a clean board.
	 */
	public function test_empty_file_gives_default_state()
	{
		$file = tempnam(sys_get_temp_dir(), 'sb_data_');
		file_put_contents($file, '');

		$sb = $this->create($file);
		$this->assertEquals(array('home' => 0, 'away' => 0), $sb->get_score());
		$this->assertEquals(1, $sb->get_inning());
		$this->assertEquals('top', $sb->get_halfinning());
		$this->assertEquals('888888', $sb->get_color('RH'));
		unlink($file);
	}

	public function test_corrupt_file_gives_default_state()
	{
		$file = tempnam(sys_get_temp_dir(), 'sb_data_');
		file_put_contents($file, 'not serialized');

		$sb = @$this->create($file);
		$this->assertEquals(array('home' => 0, 'away' => 0), $sb->get_score());
		unlink($file);
	}
}
