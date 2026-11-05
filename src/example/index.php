<?php

/**
 * @license see License.md
 * @copyright 2016 Martin Beckmann
 */

$offset = '..';
include "$offset/bigdisp/scoreboard/hwcontrol.php";
include "$offset/bigdisp/scoreboard/scoreboard.php";
include "$offset/bigdisp/scoreboard/scoreboard_file.php";

$filename = 'scoreboard_data.txt';

/** @var $scoreboard \bigdisp\scoreboard\scoreboard_file */
$scoreboard = new \bigdisp\scoreboard\scoreboard_file($filename, \bigdisp\scoreboard\hwcontrol::INTERFACE_CONSOLE);

if (isset($_POST['score']))
{
	if ($_POST['score'] === '+')
	{
		$scoreboard->add_run();
	}
	else
	{
		$scoreboard->add_run(true);
	}
}
$inning = $scoreboard->get_inning();
$halfinning = $scoreboard->get_halfinning();

if (isset($_POST['inning']))
{
	if ($_POST['inning'] === '+')
	{
		if ($halfinning === 'top')
		{
			$scoreboard->set_inning_bottom();
		}
		else
		{
			$inning++;
			$scoreboard->set_inning($inning);
			$scoreboard->set_inning_top();
		}
	}
	else
	{
		if ($halfinning === 'top')
		{
			$inning--;
			$scoreboard->set_inning($inning);
			$scoreboard->set_inning_bottom();
		}
		else
		{
			$scoreboard->set_inning_top();
		}
	}
}
$count = $scoreboard->get_count();

if (isset($_POST['balls']))
{
	if ($_POST['balls'] === '+')
	{
		$scoreboard->ball();
	}
	else
	{
		$scoreboard->set_balls($count['balls'] - 1);
	}
}
if (isset($_POST['strikes']))
{
	if ($_POST['strikes'] === '+')
	{
		$scoreboard->strike();
	}
	else
	{
		$scoreboard->set_strikes($count['strikes'] - 1);
	}
}
if (isset($_POST['outs']))
{
	if ($_POST['outs'] === '+')
	{
		$scoreboard->out();
	}
	else
	{
		$scoreboard->set_outs($scoreboard->get_outs() - 1);
	}
}

if (isset($_POST['reset-count']))
{
	$scoreboard->batter_save();
}

if (isset($_POST['batter-out']))
{
	$scoreboard->batter_out();
}
if ((isset($_POST['reset']) && isset($_POST['really-reset']) && $_POST['really-reset']) || (isset($_POST['shutdown']) && isset($_POST['really-shutdown']) && $_POST['really-shutdown']))
{
	file_put_contents($filename, '');
	$scoreboard = new \bigdisp\scoreboard\scoreboard_file($filename, \bigdisp\scoreboard\hwcontrol::INTERFACE_CONSOLE);
	$scoreboard->set_score(0, 0);
	$scoreboard->set_balls(0);
	$scoreboard->set_strikes(0);
	$scoreboard->set_outs(0);
	$scoreboard->set_inning(1);
	$scoreboard->set_inning_top();
	$scoreboard->set_color('888888');
}
// Refresh board with current data
if (isset($_POST['refresh']))
{
	$score = $scoreboard->get_score();
	$scoreboard->set_score($score['home'], $score['away']);
	$count = $scoreboard->get_count();
	$scoreboard->set_balls($count['balls']);
	$scoreboard->set_strikes($count['strikes']);
	$scoreboard->set_outs($scoreboard->get_outs());
	$scoreboard->set_inning($scoreboard->get_inning());
	if ($scoreboard->get_halfinning() == 'top')
	{
		$scoreboard->set_inning_top();
	}
	else
	{
		$scoreboard->set_inning_bottom();
	}
	$scoreboard->set_color($scoreboard->get_color('RH'), 'RH');
	$scoreboard->set_color($scoreboard->get_color('RG'), 'RG');
	$scoreboard->set_color($scoreboard->get_color('I'), 'I');
	$scoreboard->set_color($scoreboard->get_color('IT'), 'IT');
	$scoreboard->set_color($scoreboard->get_color('B'), 'B');
	$scoreboard->set_color($scoreboard->get_color('S'), 'S');

}
if (isset($_POST['save-colors']))
{
	if (isset($_POST['color-rh']))
	{
		$matches = array();
		if (preg_match('#[A-F0-9]{6}#i', $_POST['color-rh'], $matches))
		{
			$scoreboard->set_color(strtoupper($matches[0]), 'RH');
		}
	}
	if (isset($_POST['color-rg']))
	{
		$matches = array();
		if (preg_match('#[A-F0-9]{6}#i', $_POST['color-rg'], $matches))
		{
			$scoreboard->set_color(strtoupper($matches[0]), 'RG');
		}
	}
	if (isset($_POST['color-i']))
	{
		$matches = array();
		if (preg_match('#[A-F0-9]{6}#i', $_POST['color-i'], $matches))
		{
			$scoreboard->set_color(strtoupper($matches[0]), 'I');
		}
	}
	if (isset($_POST['color-it']))
	{
		$matches = array();
		if (preg_match('#[A-F0-9]{6}#i', $_POST['color-it'], $matches))
		{
			$scoreboard->set_color(strtoupper($matches[0]), 'IT');
		}
	}
	if (isset($_POST['color-b']))
	{
		$matches = array();
		if (preg_match('#[A-F0-9]{6}#i', $_POST['color-b'], $matches))
		{
			$scoreboard->set_color(strtoupper($matches[0]), 'B');
		}
	}
	if (isset($_POST['color-s']))
	{
		$matches = array();
		if (preg_match('#[A-F0-9]{6}#i', $_POST['color-s'], $matches))
		{
			$scoreboard->set_color(strtoupper($matches[0]), 'S');
		}
	}
}
if (isset($_POST['reset-colors']) && isset($_POST['really-reset-colors']) && $_POST['really-reset-colors'])
{
	$scoreboard->set_color('888888');
}
if (isset($_POST['colorize']) && isset($_POST['really-colorize']) && $_POST['really-colorize'])
{
	$scoreboard->set_color('FF0000', 'S');
	$scoreboard->set_color('00FF00', 'B');
	$scoreboard->set_color('FFAA00', 'I');
	$scoreboard->set_color('FFAA00', 'IT');
	$scoreboard->set_color('CCDDCC', 'RH');
	$scoreboard->set_color('CCCCDD', 'RG');
}
if (isset($_POST['white']) && isset($_POST['really-white']) && $_POST['really-white'])
{
	$scoreboard->set_color('FFFFFF');
}


$scoreboard->store_data();

if (isset($_POST['shutdown']) && isset($_POST['really-shutdown']) && $_POST['really-shutdown'])
{
	echo "The board will now shut down. Please wait at least 30 seconds before turning off the power to allow for the system to properly shut down.";
	system('shutdown -h -P now');
	die();
}

$colors = array(
	'RH' 	=> $scoreboard->get_color('RH'),
	'RG' 	=> $scoreboard->get_color('RG'),
	'I'		=> $scoreboard->get_color('I'),
	'IT'	=> $scoreboard->get_color('IT'),
	'B'		=> $scoreboard->get_color('B'),
	'S'		=> $scoreboard->get_color('S'),
);

$curr_inning = $scoreboard->get_inning();
$halfinning = $scoreboard->get_halfinning();
$runs = $scoreboard->get_score();
$runs_home = $runs['home'];
$runs_away = $runs['away'];
$count = $scoreboard->get_count();
$balls = $count['balls'];
$strikes = $count['strikes'];
$outs = $scoreboard->get_outs();

$inning_list = $scoreboard->get_linescore();

include "$offset/example/output.php";
