<?php
/**
 * @license see License.md
 * @copyright 2016 Martin Beckmann
 */
namespace bigdisp\scoreboard;

class scoreboard_file extends \bigdisp\scoreboard\scoreboard
{
	protected $game_id = '';

	/** @var string */
	protected $file;

	public function __construct($filename, $interface = INTERFACE_DAEMON, $host = 'localhost', $port = 44322)
	{
		parent::__construct($interface, $host, $port);
		$this->file = $filename;
		if (file_exists($filename))
		{
			$this->read_data();
		}
	}

	/**
	 * Reads the basic scoreboard data from the database. Also reads linescores from the "normal" database if a game ID is set.
	 */
	function read_data()
	{
		$filedata = file_get_contents($this->file);
		$data = unserialize($filedata);
		foreach ($data as $row)
		{
			switch($row['scoreboard_field'])
			{
				case 'inning':
					$this->inning = $row['scoreboard_value'];
					break;
				case 'half_inning':
					$this->top = $row['scoreboard_value'];
					break;
				case 'balls':
					$this->balls = $row['scoreboard_value'];
					break;
				case 'strikes':
					$this->strikes = $row['scoreboard_value'];
					break;
				case 'outs':
					$this->outs = $row['scoreboard_value'];
					break;
				case 'runs_home':
					$this->runs_home = $row['scoreboard_value'];
					break;
				case 'runs_away':
					$this->runs_away = $row['scoreboard_value'];
					break;
				case 'game_id':
					$this->game_id = $row['scoreboard_value'];
					break;
				case 'line_home':
					$this->line_home = empty($row['scoreboard_value'])? array() : unserialize($row['scoreboard_value']);
					break;
				case 'line_away':
					$this->line_away = empty($row['scoreboard_value'])? array() : unserialize($row['scoreboard_value']);
					break;
				case 'brightness':
					$this->brightness = $row['scoreboard_value'];
					break;
			}
		}
	}

	/**
	 * Store data of this class in db
	 */
	function store_data()
	{
		$data = array(
			array(
				'scoreboard_field' => 'inning',
				'scoreboard_value' => $this->inning,
			),
			array(
				'scoreboard_field' => 'half_inning',
				'scoreboard_value' => $this->top,
			),
			array(
				'scoreboard_field' => 'balls',
				'scoreboard_value' => $this->balls,
			),
			array(
				'scoreboard_field' => 'strikes',
				'scoreboard_value' => $this->strikes,
			),
			array(
				'scoreboard_field' => 'outs',
				'scoreboard_value' => $this->outs,
			),
			array(
				'scoreboard_field' => 'runs_home',
				'scoreboard_value' => $this->runs_home,
			),
			array(
				'scoreboard_field' => 'runs_away',
				'scoreboard_value' => $this->runs_away,
			),
			array(
				'scoreboard_field' => 'game_id',
				'scoreboard_value' => $this->game_id,
			),
			array(
				'scoreboard_field' => 'line_home',
				'scoreboard_value' => serialize($this->line_home),
			),
			array(
				'scoreboard_field' => 'line_away',
				'scoreboard_value' => serialize($this->line_away),
			),
			array(
				'scoreboard_field' => 'brightness',
				'scoreboard_value' => $this->brightness,
			),
		);
		file_put_contents($this->file, serialize($data));
	}
}
