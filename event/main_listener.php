<?php
/**
 *
 * Failed logins Log and Notify. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, cabot, https://forum.cabotweb.fr/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace cabot\failedlogins\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'			=> 'load_language_on_setup',
			'core.login_box_failed'		=> 'login_box_failed',
			'core.login_box_redirect'	=> 'login_box_redirect',
			'core.page_footer'			=> 'notify_failed_logins',
		];
	}

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user\user */
	protected $user;

	public function __construct( \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\log\log $log, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->db		= $db;
		$this->language	= $language;
		$this->log		= $log;
		$this->request	= $request;
		$this->template = $template;
		$this->user		= $user;
	}

	/**
	 * Load common language files during user setup
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'cabot/failedlogins',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Handles the event when a login attempt fails.
	 * Increments the failed login counter & updates timestamp.
	 */
	public function login_box_failed($event)
	{
		$now = time();
		$user_id = 0;

		// 1) Prefer user_id if available in the event
		if (isset($event['result']['user_row']['user_id']))
		{
			$user_id = (int) $event['result']['user_row']['user_id'];
		}

		// 2) Fallback: resolve user_id via username_clean if account exists
		if ($user_id <= 0 && !empty($event['username']))
		{
			$username_clean = utf8_clean_string($event['username']);
			$sql = 'SELECT user_id
					FROM ' . USERS_TABLE . "
					WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'
					LIMIT 1";
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			if (!empty($row['user_id']))
			{
				$user_id = (int) $row['user_id'];
			}
		}

		// 3) Update failed logins counter & timestamp for that user_id
		if ($user_id > 0)
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
					SET failedlogins_count = failedlogins_count + 1,
						failedlogins_timestamp_last = $now
					WHERE user_id = $user_id";
			$this->db->sql_query($sql);
		}

		// 4) Mog the failed attempt
		$this->log->add('user', ANONYMOUS, $this->user->ip, 'FAILED_LOGINS_LOG', $now, [
				'reportee_id' => ANONYMOUS,
				'username'    => $event['username'] ?? '',
			]
		);
	}

	/**
	 * On successful login, move current counter to *_count_last then reset it.
	 */
	public function login_box_redirect()
	{
		$user_id = $this->user->data['user_id'] ?? 0;
		if ($user_id <= 0 || $user_id === ANONYMOUS)
		{
			return;
		}

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET failedlogins_count_last = failedlogins_count,
			    failedlogins_count = 0
			WHERE user_id = $user_id";
		$this->db->sql_query($sql);
	}

	/**
	 * Display the notification to logged-in users and allow them to clear it.
	 */
	public function notify_failed_logins()
	{
		$user_id = $this->user->data['user_id'] ?? 0;
		if ($user_id <= 0 || $user_id === ANONYMOUS)
		{
			return;
		}

		$form_key = 'failedlogins_remove';

		// Handle user action to remove notification
		if ($this->request->is_set_post($form_key))
		{
			if (check_form_key($form_key))
			{
				$sql = 'UPDATE ' . USERS_TABLE . "
						SET failedlogins_count_last = 0,
							failedlogins_timestamp_last = 0
						WHERE user_id = $user_id";
				$this->db->sql_query($sql);

				$message = $this->language->lang('FAILED_LOGINS_REMOVED');

				if ($this->request->is_ajax())
				{
					$json = new \phpbb\json_response();
					return $json->send([
						'MESSAGE_TITLE' => $this->language->lang('INFORMATION'),
						'MESSAGE_TEXT'  => $message,
						'REFRESH_DATA'  => [
							'time' => 3,
							'url'  => $this->user->data['session_page'] ?? '',
						],
					]);
				}

				$return = $this->user->data['session_page'] ?? '';
				$message .= '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $return . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				if ($this->request->is_ajax())
				{
					trigger_error('FORM_INVALID', E_USER_WARNING);
				}
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
			return;
		}

		// Fetch fresh data for display
		$sql = 'SELECT failedlogins_count_last, failedlogins_timestamp_last
				FROM ' . USERS_TABLE . "
				WHERE user_id = $user_id";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$count_last = (int)($row['failedlogins_count_last'] ?? 0);

		if ($count_last > 0)
		{
			$timestamp_last = (int)($row['failedlogins_timestamp_last'] ?? 0);
			$formatted_date = $timestamp_last ? $this->user->format_date($timestamp_last, 'l d F Y H:i', true) : '';

			add_form_key($form_key);

			$this->template->assign_vars([
				'FAILED_LOGINS_NOTIFY'			=> $this->language->lang('FAILED_LOGINS_NOTIFY_LANG', $count_last),
				'FAILED_LOGINS_DATE'			=> $this->language->lang('FAILED_LOGINS_DATE_LANG', $formatted_date),
				'U_FAILED_LOGINS_ACTION'		=> generate_board_url() . '/' . ($this->user->page['page'] ?? ''),
				'FAILED_LOGINS_AJAX_CALLBACK'	=> 'failedlogins.remove',
			]);
		}
	}
}
