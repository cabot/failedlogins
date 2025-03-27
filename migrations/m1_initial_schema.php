<?php
/**
 *
 * Failed logins Log and Notify. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, cabot, https://forum.cabotweb.fr/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace cabot\failedlogins\migrations;

class m1_initial_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'failedlogins_count');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v33x\v3311'];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'failedlogins_count'			=> ['UINT', 0],
					'failedlogins_count_last'		=> ['UINT', 0],
					'failedlogins_timestamp_last'	=> ['TIMESTAMP', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'failedlogins_count',
					'failedlogins_count_last',
					'failedlogins_timestamp_last',
				],
			],
		];
	}
}
