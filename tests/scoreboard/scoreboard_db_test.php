<?php
/**
 * Persistence of the scoreboard state in a database.
 *
 * @license see License.md
 */

use bigdisp\scoreboard\hwcontrol;

class scoreboard_db_test extends PHPUnit_Framework_TestCase
{
	protected function create($db)
	{
		scoreboard_test_helper::new_log();
		$sb = new \bigdisp\scoreboard\scoreboard_db($db, hwcontrol::INTERFACE_CONSOLE);
		$sb->set_exec_path(scoreboard_test_helper::fake_bin());
		return $sb;
	}

	public function test_state_survives_store_and_read()
	{
		$db = new fake_dbal();
		$sb = $this->create($db);
		$sb->add_run();
		$sb->ball();
		$sb->set_color('00FF00', 'B');
		$sb->store_db();

		$this->assertEquals(array('begin', 'commit'), $db->transactions);

		$restored = $this->create($db);
		$restored->read_db();
		$this->assertEquals(array('home' => 0, 'away' => 1), $restored->get_score());
		$this->assertEquals(array('balls' => 1, 'strikes' => 0), $restored->get_count());
		$this->assertEquals('00FF00', $restored->get_color('B'));
		$this->assertEquals('888888', $restored->get_color('S'));
	}

	public function test_legacy_brightness_value_is_used_for_all_digits()
	{
		$db = new fake_dbal();
		$db->tables['scoreboard'][] = array('scoreboard_field' => 'brightness', 'scoreboard_value' => 'ABCDEF');
		$sb = $this->create($db);
		$sb->read_db();
		$this->assertEquals('ABCDEF', $sb->get_color('RH'));
	}

	public function test_invalid_brightness_value_falls_back_to_default()
	{
		$db = new fake_dbal();
		$db->tables['scoreboard'][] = array('scoreboard_field' => 'brightness', 'scoreboard_value' => 'Array');
		$sb = $this->create($db);
		$sb->read_db();
		$this->assertEquals('888888', $sb->get_color('RH'));
	}

	public function test_init_loads_linescore_of_game()
	{
		$db = new fake_dbal();
		$db->tables['linescore'] = array(
			array('linescore_inning' => 2, 'linescore_hruns' => 3, 'linescore_gruns' => 0),
			array('linescore_inning' => 1, 'linescore_hruns' => 1, 'linescore_gruns' => 2),
		);
		$sb = $this->create($db);
		$sb->init(5);

		$this->assertEquals(array(
			1 => array('home' => 1, 'away' => 2, 'inning' => 1),
			2 => array('home' => 3, 'away' => 0, 'inning' => 2),
		), $sb->get_linescore());
	}
}
