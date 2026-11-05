<?php
/**
 * Minimal stand-in for the gn36 database abstraction used by scoreboard_db.
 *
 * @license see License.md
 */
namespace gn36\db\driver;

interface dbal_interface
{
	public function sql($query);
	public function fetchrow($result);
	public function create_query($data, $type);
	public function transaction($status = 'begin');
	public function multi_insert($table, $data);
}
