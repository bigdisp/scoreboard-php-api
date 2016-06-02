<?php

/**
 * @license see License.md
 * @copyright 2016 Martin Beckmann
 */

include '../bigdisp/scoreboard/hwcontrol.php';
include '../bigdisp/scoreboard/scoreboard.php';
include '../bigdisp/scoreboard/scoreboard_file.php';

$filename = 'scoreboard_data.txt';

/** @var $scoreboard \bigdisp\scoreboard\scoreboard_file */
$scoreboard = new \bigdisp\scoreboard\scoreboard_file($filename);

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
			$scoreboard->set_inning_top();
			$scoreboard->set_inning($inning);
		}
	}
	else
	{
		if ($halfinning === 'top')
		{
			$scoreboard->set_inning_bottom();
			$inning--;
			$scoreboard->set_inning($inning);
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
		$scoreboard->set_strikes($count['strike'] - 1);
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
if (isset($_POST['reset']) && isset($_POST['really-reset']) && $_POST['really-reset'])
{
	file_put_contents($filename, '');
	$scoreboard = new \bigdisp\scoreboard\scoreboard_file($filename);
	$scoreboard->set_score(0, 0);
	$scoreboard->set_balls(0);
	$scoreboard->set_strikes(0);
	$scoreboard->set_outs(0);
}

$scoreboard->store_data();

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

include "output.php";
