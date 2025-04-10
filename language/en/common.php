<?php
/**
 *
 * Failed logins Log and Notify. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, cabot, https://forum.cabotweb.fr/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'FAILED_LOGINS_LOG'				=> '<strong>Failed login</strong><br>» Username: <strong>%s</strong>',
	'FAILED_LOGINS_NOTIFY_LANG'		=> [
		1	=> 'Since your last visit there were <strong>%1$d</strong> failed login attempt!',
		2	=> 'Since your last visit there were <strong>%1$d</strong> failed login attempts!',
	],
	'FAILED_LOGINS_DATE_LANG'		=> 'Last failed login attempt on: <strong>%s</strong>. If it wasn’t you, it might be worth changing your password.',
	'FAILED_LOGINS_REMOVE_BUTTON'	=> 'Remove message and reset counter',
	'FAILED_LOGINS_REMOVED'			=> 'Failed login attempts since the last visit have been reset.',
]);
