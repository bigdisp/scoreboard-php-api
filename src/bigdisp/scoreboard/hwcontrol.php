<?php
/**
 * @license see License.md
 * @copyright 2016 Martin Beckmann
 */
namespace bigdisp\scoreboard;

/**
 * Low level access to scoreboard.
 *
 */
class hwcontrol
{
	/** @var int */
	protected $interface;

	/** @var string */
	protected $host;

	/** @var int */
	protected $port;

	/** @var string */
	protected $path = './scoreboard ';

	/** @var int */
	protected $brightness = -1;

	const INTERFACE_CONSOLE = 1;
	const INTERFACE_DAEMON = 2;

	public function __construct($interface = INTERFACE_DAEMON, $host = 'localhost', $port = 44322)
	{
		$this->interface = $interface;
		$this->host = $host;
		$this->port = $port;
	}

	/**
	 * Set path for scoreboard executable for console interface.
	 *
	 * @param string $path
	 */
	public function set_exec_path($path)
	{
		$this->path = $path;
	}

	/**
	 * Set the value to display on a given digit.
	 *
	 * @param string $digit
	 * @param int $value
	 */
	public function set_value($digit, $value)
	{
		$this->cmd($digit . " " . $value);
	}

	/**
	 * Enable or disable the point associated with a certain digit.
	 *
	 * @param string $digit
	 * @param boolean $enable
	 */
	public function set_point($digit, $enable = true)
	{
		$this->cmd("point $digit " . ($enable ? "1" : "0"));
	}

	/**
	 * Disable pwm globally.
	 */
	public function pwm_off()
	{
		$this->cmd_pwm('off');
	}

	/**
	 * Enable pwm globally.
	 */
	public function pwm_on()
	{
		$this->cmd_pwm('on');
	}

	/**
	 * Set pwm brightness level (between 0 and 10).
	 *
	 * @param int $level
	 */
	public function pwm_level($level = 5)
	{
		$this->brightness = $level;
		$this->cmd_pwm(intval($level));
	}

	/**
	 * Set the color of a certain digit. If no digit is given, all colors are set.
	 *
	 * Color can be any rgb hex value such as AB0976.
	 *
	 * @param string $color
	 * @param string $digit
	 */
	public function set_color($color, $digit = null)
	{
		if ($digit === null)
		{
			$this->cmd("color all $color");
		}
		else
		{
			$this->cmd("color $digit $color");
		}
	}

	/**
	 * Sends given command to scoreboard.
	 *
	 * @param array|string $params
	 */
	protected function cmd($params)
	{
		if (is_array($params))
		{
			$params = implode(" ", $params);
		}
		//Todo: eigentlichen Befehl einsetzen

		if ($this->interface == hwcontrol::INTERFACE_CONSOLE)
		{
			system($this->path . ' ' . $params);
		}
		else
		{
			$resource = fsockopen($this->host, $this->port);
			if ($resource)
			{
				fwrite($resource, $params);
				fclose($resource);
			}
		}
	}

	/**
	 * Send given pwm command to scoreboard.
	 *
	 * @param array|string $params
	 */
	protected function cmd_pwm($params)
	{
		//Todo: eigentlichen Befehl einsetzen
		if (is_array($params))
		{
			$params = implode(" ", $params);
		}
		$this->cmd("pwm $params");
	}
}
