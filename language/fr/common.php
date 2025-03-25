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
// ’ « » “ ” …
//

$lang = array_merge($lang, [
	'FAILED_LOGINS_LOG'				=> '<strong>Échec de connexion</strong><br>» Nom d’utilisateur : <strong>%s</strong>',
	'FAILED_LOGINS_NOTIFY_LANG'		=> [
		1	=> 'Depuis votre dernière visite, il y a eu <strong>%1$d</strong> tentative de connexion échouée !',
		2	=> 'Depuis votre dernière visite, il y a eu <strong>%1$d</strong> tentatives de connexion échouées !',
	],
	'FAILED_LOGINS_DATE_LANG'		=> 'Dernière tentative de connexion échouée le : <strong>%s</strong>. Si ce n’était pas vous, il pourrait s’avérer utile de modifier votre mot de passe.',
	'FAILED_LOGINS_REMOVE_BUTTON'	=> 'Supprimer le message et réinitialiser le compteur',
	'FAILED_LOGINS_REMOVED'			=> 'Les tentatives de connexion échouées depuis la dernière visite ont été réinitialisées.',
]);
