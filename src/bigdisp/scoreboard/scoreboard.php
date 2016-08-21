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
	/** @var int */
	protected $runs_home = 0;

	/** @var int */
	protected $runs_away = 0;

	/** @var int */
	protected $inning = 1;

	/** @var bool */
	protected $top = true;

	/** @var int */
	protected $outs = 0;

	/** @var int */
	protected $strikes = 0;

	/** @var int */
	protected $balls = 0;

	/** @var array */
	protected $line_home  = array();

	/** @var array */
	protected $line_away = array();

	public function add_run($subtract = false)
	{
		// Linescore
		for ($inning = 1; $inning <= $this->inning; $inning++)
		{
			if (!isset($this->line_away[$inning]))
			{
				$this->line_away[$inning] = 0;
			}
			if (!isset($this->line_home[$inning]))
			{
				$this->line_home[$inning] = 0;
			}
		}

		if ($subtract)
		{
			if ($this->top)
			{
				$this->set_score($this->runs_home, $this->runs_away - 1);
				$this->line_away[$this->inning] -= 1;
			}
			else
			{
				$this->set_score($this->runs_home - 1, $this->runs_away);
				$this->line_home[$this->inning] -= 1;
			}
		}
		else
		{
			if ($this->top)
			{
				$this->set_score($this->runs_home, $this->runs_away + 1);
				$this->line_away[$this->inning] += 1;
			}
			else
			{
				$this->set_score($this->runs_home + 1, $this->runs_away);
				$this->line_home[$this->inning] += 1;
			}
		}
	}

	/**
	 * Blank count and add an out. If already at two outs, increment innings.
	 */
	public function batter_out()
	{
		$this->set_balls(0);
		$this->set_strikes(0);
		$this->out();
	}

	/**
	 * Blank count.
	 */
	public function batter_save()
	{
		$this->set_balls(0);
		$this->set_strikes(0);
	}

	/**
	 * Add an out. If already at two outs, increment inning and blank balls/strikes.
	 */
	public function out()
	{
		$this->outs += 1;

		if ($this->outs > 2)
		{
			$this->outs = 0;
			$this->set_balls(0);
			$this->set_strikes(0);

			if ($this->top)
			{
				$this->set_inning_bottom();
			}
			else
			{
				$this->set_inning($this->inning + 1);
				$this->set_inning_top();
			}
		}

		$this->set_outs($this->outs);
	}

	/**
	 * Add one ball. If 4 balls are reached, blanks count.
	 */
	public function ball()
	{
		$this->balls += 1;
		if ($this->balls > 3)
		{
			$this->set_strikes(0);
		}

		$this->set_balls($this->balls);
	}

	/**
	 * Add one strike. If 3 strikes are reached, an out is added and the count is blanked.
	 */
	public function strike()
	{
		$this->strikes += 1;
		if ($this->strikes > 2)
		{
			$this->set_balls(0);
			$this->out();
		}
		$this->set_strikes($this->strikes);
	}


	/**
	 * Set the number of balls. Forced to be a number between 0 and 3.
	 *
	 * @param int $balls
	 */
	public function set_balls($balls)
	{
		$this->balls = intval($balls) % 4;
		$this->set_value('B', $this->balls);
	}

	/**
	 * Set the number of strikes. Forced to be a number between 0 and 2.
	 *
	 * @param int $strikes
	 */
	public function set_strikes($strikes)
	{
		$this->strikes = intval($strikes) % 3;
		$this->set_value('S', $this->strikes);
	}

	/**
	 * Set the number of outs. Forced to be a number between 0 and 2.
	 *
	 * @param int $outs
	 */
	public function set_outs($outs)
	{
		$this->outs = intval($outs) % 3;
		$this->set_value('O', $this->outs);
	}

	/**
	 * Set the score that should currently be displayed.
	 * WARNING: Using this directly messes up the linescore!
	 *
	 * @param int $home
	 * @param int $away
	 */
	public function set_score($home, $away)
	{
		$this->runs_away = $away;
		$this->runs_home = $home;

		$this->set_value("RH", $home);
		$this->set_value("RG", $away);
	}

	/**
	 * Set the current inning.
	 *
	 * @param int $inning
	 */
	public function set_inning($inning)
	{
		$this->inning = $inning;

		$this->set_value("I", $inning);
	}

	/**
	 * Set top of inning active.
	 */
	public function set_inning_top()
	{
		$this->top = true;

		$this->cmd('IT');
	}

	/**
	 * Set bottom of inning active.
	 */
	public function set_inning_bottom()
	{
		$this->top = false;

		$this->cmd('IB');
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
	 * Returns the complete linescore
	 */
	public function get_linescore()
	{
		$linescore = array();
		foreach ($this->line_home as $inning => $home)
		{
			$linescore[$inning] = array(
				'home' => $home,
				'away' => $this->line_away[$inning],
				'inning' => $inning,
			);
		}
		if (empty($linescore))
		{
			$linescore[1] = array(
				'home' => 0,
				'away' => 0,
				'inning' => 1,
			);
		}
		return $linescore;
	}

	/**
	 * Returns the currently set count.
	 *
	 * @return array
	 */
	public function get_count()
	{
		return array(
			'balls' 	=> $this->balls,
			'strikes' 	=> $this->strikes,
		);
	}

	/**
	 * returns the number of outs.
	 *
	 * @return int
	 */
	public function get_outs()
	{
		return $this->outs;
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
