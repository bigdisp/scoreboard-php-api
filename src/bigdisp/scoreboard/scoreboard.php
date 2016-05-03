<?php
/**
 * @license see License.md
 * @copyright 2016 Martin Beckmann
 */

namespace bigdisp\scoreboard;

/**
 * Class controlling the currently displayed values on the scoreboard.
 *
 *
 */
class scoreboard extends hwcontrol
{
	protected $runs_home = 0;
	protected $runs_away = 0;
	protected $inning = 1;
	protected $top = true;

	/**
	 * Set the score that should currently be displayed.
	 *
	 * @param int $home
	 * @param int $away
	 */
	public function set_score($home, $away)
	{
		$this->runs_away = $away;
		$this->runs_home = $home;

		$rh10 = intval($home / 10);
		$rh1  = intval($home % 10);
		$this->set_value("RH1", $rh1);
		$this->set_value("RH10", $rh10);

		$rg10 = intval($away / 10);
		$rg1  = intval($away % 10);
		$this->set_value("RG1", $rg1);
		$this->set_value("RG10", $rg10);
	}

	/**
	 * Set the current inning.
	 *
	 * @param int $inning
	 */
	public function set_inning($inning)
	{
		$this->inning = $inning;

		$i10 = intval($inning / 10);
		$i1  = intval($inning % 10);
		$this->set_value("I1", $i1);
		$this->set_value("I10", $i10);
	}

	/**
	 * Set top of inning active.
	 */
	public function set_inning_top()
	{
		$this->top = true;

		$this->set_point("I1", true);
		$this->set_point("I10", false);
	}

	/**
	 * Set bottom of inning active.
	 */
	public function set_inning_bottom()
	{
		$this->top = false;

		$this->set_point("I1", false);
		$this->set_point("I10", true);
	}

	/**
	 * Returns the currently set score.
	 *
	 * @return array
	 */
	public function get_score()
	{
		return array(
			'home' => $this->runs_home,
			'away' => $this->runs_away,
		);
	}

	/**
	 * Returns the current inning.
	 *
	 * @return int
	 */
	public function get_inning()
	{
		return $this->inning;
	}

	/**
	 * Returns current halfinning (top or bottom).
	 *
	 * @param bool $binary return as true or false.
	 * @return bool|string
	 */
	public function get_halfinning($binary = false)
	{
		return $binary ? $this->top : ($this->top ? 'top' : 'bottom');
	}
}
