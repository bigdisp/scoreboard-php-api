<?php
/**
 * @license see License.md
 * @copyright 2016 Martin Beckmann
 */
namespace bigdisp\scoreboard;

class scoreboard_db extends \bigdisp\scoreboard\scoreboard
{
	protected $game_id = '';
	protected $line_home  = array();
	protected $line_away = array();

	/** @var \gn36\db\driver\dbal_interface */
	protected $db;

	public function __construct(\gn36\db\driver\dbal_interface $db, $interface = INTERFACE_DAEMON, $host = 'localhost', $port = 44322)
	{
		parent::__construct($interface, $host, $port);
		$this->db = $db;
	}

	function init($game_id = 0)
	{
		$this->game_id = $game_id;
		$this->read_db();
		//No gamedata present, let's have a look at the database
		if (empty($this->line_guest) && $this->game_id)
		{
			$sql = 'SELECT * FROM linescore WHERE ' . $db->create_query(array('linescore_game_id' => $this->game_id), 'WHERE') . ' ORDER BY linescore_inning';
			$result = $db->sql($sql);
			$this->line_home = array();
			$this->line_guest  = array();
			while ($row = $db->fetchrow($result))
			{
				$this->line_home[$row['linescore_inning']]  = $row['linescore_hruns'];
				$this->line_guest[$row['linescore_inning']] = $row['linescore_gruns'];
			}
		}
	}

	/**
	 * Reads the basic scoreboard data from the database. Also reads linescores from the "normal" database if a game ID is set.
	 */
	function read_db()
	{
		$db = $this->db;
		$sql = 'SELECT * FROM scoreboard';
		$result = $db->sql($sql);
		while ($row = $db->fetchrow($result))
		{
			switch ($row['scoreboard_field'])
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
	function store_db()
	{
		$db = $this->db;
		$db->transaction();

		$sql = 'DELETE FROM scoreboard';
		$db->sql($sql);

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
		$db->multi_insert('scoreboard', $data);
		$db->transaction('commit');
	}
}
