<?php
/**
 * Shared helpers for the tests: a simulated command line and a fake database.
 *
 * @license see License.md
 */

/**
 * Helpers to run commands against the simulated executable
 * (tests/fixtures/fake_bin.php) and read back what it received.
 */
class scoreboard_test_helper
{
	/**
	 * Returns a fresh, empty log file and points SCOREBOARD_TEST_LOG to it.
	 *
	 * @return string
	 */
	public static function new_log()
	{
		$log = tempnam(sys_get_temp_dir(), 'sb_log_');
		file_put_contents($log, '');
		putenv('SCOREBOARD_TEST_LOG=' . $log);
		return $log;
	}

	/**
	 * Returns the logged invocations, one array of arguments per call.
	 *
	 * @param string $log
	 * @return array
	 */
	public static function read_log($log)
	{
		$calls = array();
		foreach (file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
		{
			$calls[] = json_decode($line, true);
		}
		return $calls;
	}

	/**
	 * Returns the logged invocations as space separated strings.
	 *
	 * @param string $log
	 * @return array
	 */
	public static function read_log_lines($log)
	{
		return array_map(function ($args) {
			return implode(' ', $args);
		}, self::read_log($log));
	}

	/**
	 * PHP binary to run helper scripts with.
	 *
	 * @return string
	 */
	public static function php()
	{
		return defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';
	}

	/**
	 * Command line that runs the simulated executable.
	 *
	 * @return string
	 */
	public static function fake_bin()
	{
		return escapeshellarg(self::php()) . ' ' . escapeshellarg(__DIR__ . '/fixtures/fake_bin.php');
	}

	/**
	 * Creates an empty temporary directory.
	 *
	 * @return string
	 */
	public static function temp_dir()
	{
		$dir = tempnam(sys_get_temp_dir(), 'sb_dir_');
		unlink($dir);
		mkdir($dir);
		return $dir;
	}

	/**
	 * Recursively copies a directory.
	 *
	 * @param string $src
	 * @param string $dst
	 */
	public static function copy_dir($src, $dst)
	{
		if (!is_dir($dst))
		{
			mkdir($dst, 0777, true);
		}
		foreach (scandir($src) as $entry)
		{
			if ($entry === '.' || $entry === '..')
			{
				continue;
			}
			if (is_dir("$src/$entry"))
			{
				self::copy_dir("$src/$entry", "$dst/$entry");
			}
			else
			{
				copy("$src/$entry", "$dst/$entry");
			}
		}
	}
}

/**
 * Scoreboard that records the commands instead of sending them to the hardware.
 */
class recording_scoreboard extends \bigdisp\scoreboard\scoreboard
{
	/** @var array */
	public $commands = array();

	protected function cmd($params)
	{
		$this->commands[] = is_array($params) ? implode(' ', $params) : $params;
	}
}

/**
 * In-memory database supporting the queries issued by scoreboard_db.
 */
class fake_dbal implements \gn36\db\driver\dbal_interface
{
	/** @var array table name => rows */
	public $tables = array(
		'scoreboard' => array(),
		'linescore' => array(),
	);

	/** @var array */
	public $transactions = array();

	public function sql($query)
	{
		if (strpos($query, 'DELETE FROM scoreboard') === 0)
		{
			$this->tables['scoreboard'] = array();
			return true;
		}
		if (strpos($query, 'SELECT * FROM scoreboard') === 0)
		{
			return new ArrayIterator($this->tables['scoreboard']);
		}
		if (strpos($query, 'SELECT * FROM linescore') === 0)
		{
			$rows = $this->tables['linescore'];
			usort($rows, function ($a, $b) {
				return $a['linescore_inning'] - $b['linescore_inning'];
			});
			return new ArrayIterator($rows);
		}
		throw new \RuntimeException("Unexpected query: $query");
	}

	public function fetchrow($result)
	{
		if (!$result->valid())
		{
			return false;
		}
		$row = $result->current();
		$result->next();
		return $row;
	}

	public function create_query($data, $type)
	{
		$parts = array();
		foreach ($data as $key => $value)
		{
			$parts[] = "$key = '" . addslashes($value) . "'";
		}
		return implode(' AND ', $parts);
	}

	public function transaction($status = 'begin')
	{
		$this->transactions[] = $status;
	}

	public function multi_insert($table, $data)
	{
		foreach ($data as $row)
		{
			// Values are stored as strings like in a real database
			$row['scoreboard_value'] = (string) $row['scoreboard_value'];
			$this->tables[$table][] = $row;
		}
	}
}
