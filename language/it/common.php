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
	'FAILED_LOGINS_LOG'				=> '<strong>Accesso fallito</strong><br>» Nome utente: <strong>%s</strong>',
	'FAILED_LOGINS_NOTIFY_LANG'		=> [
		1	=> 'Dall’ultimo accesso ci sono stati <strong>%1$d</strong> tentativo di accesso fallito!',
		2	=> 'Dall’ultimo accesso ci sono stati <strong>%1$d</strong> tentativi di accesso falliti!',
	],
	'FAILED_LOGINS_DATE_LANG'		=> 'Ultimo tentativo di accesso fallito il: <strong>%s</strong>. Se non siete stati voi, potrebbe valere la pena di cambiare la password.',
	'FAILED_LOGINS_REMOVE_BUTTON'	=> 'Rimuovi messaggio e reimposta contatore',
	'FAILED_LOGINS_REMOVED'			=> 'I tentativi di accesso falliti dall’ultimo accesso sono stati reimpostati.',
]);
