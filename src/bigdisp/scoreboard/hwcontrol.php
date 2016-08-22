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
	protected $brightness = array('all' => '888888');

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
	 * Set the color of a certain digit. If no digit is given, all colors are set.
	 * Note that certain digits will be adjusted in conjunction due to hardware wiring.
	 * For example, the two digits of the home and away runs will always be the same color.
	 * The same goes for strikes and outs.
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
			$this->brightness = array('all' => $color);
		}
		else
		{
			$this->cmd("color $digit $color");
			$this->brightness[$digit] = $color;
		}
	}

	/**
	 * Get the color that is set for a certain digit. Returns false if the color is unknown.
	 *
	 * Note: This function does not work correctly if pwm is turned off.
	 *
	 * @param string $digit
	 */
	public function get_color($digit)
	{
		if (isset($this->brightness[$digit]))
		{
			return $this->brightness[$digit];
		}
		if (isset($this->brightness['all']))
		{
			return $this->brightness['all'];
		}
		return '888888';
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

		echo $this->path . " " . $params;
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
