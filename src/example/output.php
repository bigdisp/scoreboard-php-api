<html>
<head>
	<title>Scoreboard</title>
	<!-- Bootstrap -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script> -->
	<link rel="stylesheet" href="<?=$offset ?>/example/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?=$offset ?>/example/bootstrap-3.3.7-dist/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="<?=$offset ?>/example/font-awesome-4.6.3/css/font-awesome.min.css">
	<script src="<?=$offset ?>/example/jquery-3.1.0.min.js"></script>
	<script src="<?=$offset ?>/example/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>

</head>
<body role="document">
	<div class="container" role="main">
		<div class="page-header">
			<h1>Scoreboard</h1>
		</div>

		<div class="page-header">
			<h2>Linescore</h2>
		</div>
		<form method="post">
		<div class="row">
			<table class="table">
				<thead>
				<tr>
					<th>Team</th>
					<?php foreach (array_keys($inning_list) as $inning)
					{
						?><th><?=$inning ?></th><?php
					}
						?>
					<th>R</th>
				</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo (isset($team_away) ? $team_away : 'Guest'); ?>
					<?php foreach ($inning_list as $inn_data)
					{
						?>
							<td> <?=$inn_data['away'] ?> </td>
						<?php
					}?>
					<td><?=$runs_away ?></td>
					</tr>
					<tr>
						<td><?php echo (isset($team_home) ? $team_home : 'Home'); ?>
					<?php foreach ($inning_list as $inn_data)
					{
						?>
							<td><?=$inn_data['home'] ?></td>
						<?php
					}?>
					<td><?=$runs_home ?></td>
					</tr>
				</tbody>
			</table>

		</div>
		<div class="row">
			<div class="col-sm-4 col-xs-6">
			<p>Score and Inning
				<ul class="list-group">
					<li class="list-group-item"><input type="submit" name="score" class="btn btn-sm btn-danger" value="-"> <input type="submit" name="score" class="btn btn-sm btn-success" value="+"> Score</li>
					<li class="list-group-item"><input type="submit" name="inning" class="btn btn-sm btn-danger" value="-"> <?=$curr_inning ?> <?=$halfinning ?> <input type="submit" name="inning" class="btn btn-sm btn-success" value="+"> Inning</li>
				</ul>
			</p>
			</div>
			<div class="col-sm-4 col-xs-6">
			<p>Count:
				<ul class="list-group"><li class="list-group-item"><input type="submit" name="balls" class="btn btn-sm btn-danger" value="-"> <?=$balls ?> <input type="submit" name="balls" class="btn btn-sm btn-success" value="+"> Balls</li>
					<li class="list-group-item"><input type="submit" name="strikes" class="btn btn-sm btn-danger" value="-"> <?=$strikes ?> <input type="submit" name="strikes" class="btn btn-sm btn-success" value="+"> Strikes</li>
					<li class="list-group-item"><input type="submit" name="reset-count" class="btn btn-sm btn-default" value="reset count"></li>
				</ul>
			</p>
			</div>
			<div class="col-sm-4 col-xs-6">
			<p>Outs:
				<ul class="list-group">
					<li class="list-group-item"><input type="submit" name="outs" class="btn btn-sm btn-danger" value="-"> <?=$outs ?> <input type="submit" name="outs" class="btn btn-sm btn-success" value="+"> Outs</li>
					<li class="list-group-item"><input type="submit" name="batter-out" class="btn btn-sm btn-default" value="Batter out"></li>
				</ul>
			</p>
			</div>
		</div>
		<h2>Additional Functions</h2>
		<h3>General</h3>
		<ul class="list-group">
			<li class="list-group-item">
				<input type="submit" name="refresh" class="btn btn-sm btn-default" value="Refresh board">
			</li>
		</ul>
		<h3><span class="fa fa-paint-brush"></span> Colors</h3>
		<div class="row">
			<div class="col-sm-6">
				<div class="list-group-item">
					<dl class="dl-horizontal">
						<dt>Runs Home</dt>
						<dd><input type="color" name="color-rh" value="#<?=$colors['RH']; ?>"></dd>
						<dt>Runs Away</dt>
						<dd><input type="color" name="color-rg" value="#<?=$colors['RG']; ?>"></dd>
						<dt>Innings</dt>
						<dd><input type="color" name="color-i" value="#<?=$colors['I']; ?>"></dd>
						<dt>Inning Markers</dt>
						<dd><input type="color" name="color-it" value="#<?=$colors['IT']; ?>"></dd>
						<dt>Balls</dt>
						<dd><input type="color" name="color-b" value="#<?=$colors['B']; ?>"></dd>
						<dt>Strikes and Outs</dt>
						<dd><input type="color" name="color-s" value="#<?=$colors['S']; ?>"></dd>
					</dl>
					<input type="submit" name="save-colors" class="btn btn-sm btn-default" value="Save Colors">
				</div>
			</div>
			<div class="col-sm-6">
				<ul class="list-group">
					<li class="list-group-item">
						<input type="submit" name="reset-colors" class="btn btn-sm btn-danger" value="Reset Colors to gray"> <input type="checkbox" name="really-reset-colors" value="1"> I really wish to reset the colors
					</li>
					<li class="list-group-item">
						<input type="submit" name="colorize" class="btn btn-sm btn-danger" value="Colorize"> <input type="checkbox" name="really-colorize" value="1"> Do change the colors
					</li>
					<li class="list-group-item">
						<input type="submit" name="white" class="btn btn-sm btn-danger" value="All White"> <input type="checkbox" name="really-white" value="1"> Set all colors to white
					</li>

				</ul>
			</div>
		</div>


		<h3><span class="fa fa-fw fa-warning"></span> Reset and Shutdown</h3>
		<ul class="list-group">
			<li class="list-group-item"><i class="fa fa-fw fa-warning"></i> <input type="submit" name="reset" class="btn btn-sm btn-danger" value="Reset whole scoreboard"> <input type="checkbox" name="really-reset" value="1"> I really wish to reset the board</li>
			<li class="list-group-item"><i class="fa fa-fw fa-warning"></i> <input type="submit" name="shutdown" class="btn btn-sm btn-danger" value="Shutdown"> <input type="checkbox" name="really-shutdown" value="1"> I really wish to shut down the board</li>
		</ul>
		</form>
		<div class="jumbotron">
			<div class="row">
				<div class="col-md-2">
					<i class="fa fa-5x fa-dashboard"></i>
				</div>
				<div class="col-md-8">
					<h2> <span class="label label-primary"><i class="fa fa-info-circle"></i> Current score: <?=$runs_home ?> : <?=$runs_away ?></span></h2>
					<p>This page can be used to control the scoreboard hardware. Use the buttons above to change the display of the board accordingly.</p>
				</div>
			</div>
		</div>

	</div>
</body>
</html>
