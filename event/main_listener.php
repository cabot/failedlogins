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

/**
 * @ignore
 */
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

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db				DB driver interface
	 * @param \phpbb\language\language				$language		Language object
	 * @param \phpbb\log\log						$log			The phpBB log system
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\log\log $log, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
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
	 *
	 * @param \phpbb\event\data $event Event object
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
	 *
	 * @param array $event An associative array containing event data, including the username of the failed login attempt.
	 *
	 * This method performs the following actions:
	 * - Increments the failed login counter for the user in the database.
	 * - Updates the `failedlogins_timestamp_last` to the current time for the user in the database.
	 * - Logs the failed login attempt in the user log.
	 */
	public function login_box_failed($event)
	{
		$sql = 'UPDATE ' . USERS_TABLE . "
				SET failedlogins_count = failedlogins_count + 1,
					failedlogins_timestamp_last = " . time() . "
				WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($event['username'])) . "'";
		$this->db->sql_query($sql);

		$this->log->add('user', ANONYMOUS, $this->user->ip, 'FAILED_LOGINS_LOG', time(), [
			'reportee_id'   => ANONYMOUS,
			'username'  => $event['username'],
		]);
	}

	/**
	 * Resets the failed login attempts for the current user.
	 *
	 * This method updates the `failedlogins_count_last` to the current `failedlogins_count` and
	 * resets the `failedlogins_count` to 0, for the user currently logged in.
	 *
	 * @return void
	 */
	public function login_box_redirect()
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
				SET failedlogins_count_last = failedlogins_count,
					failedlogins_count = 0
				WHERE user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);
	}

	/**
	 * Handles the page footer event.
	 *
	 * This method performs the following actions:
	 * - Clears the `failed_logins_count_last` for the current user if the `submit` request parameter is set and the form key is valid.
	 * - If the request is an AJAX request, it triggers an appropriate error message based on the form key validation result.
	 * - Displays the number of failed login attempts if `failed_logins_count_last` is greater than 0.
	 * - Adds a form key for `failedlogins_remove` and assigns template variables for displaying the failed login count and the URL to remove the message.
	 *
	 * @return void
	 */
	public function notify_failed_logins()
	{
		$form_key = 'failedlogins_remove';
		$submitted = false;

		if ($this->request->is_set_post('submit'))
		{
			if (check_form_key($form_key))
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
						SET failedlogins_count_last = 0,
							failedlogins_timestamp_last = 0
						WHERE user_id = ' . (int) $this->user->data['user_id'];
				$this->db->sql_query($sql);

				$message = $this->language->lang('FAILED_LOGINS_REMOVED');

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response();
					return $json_response->send([
						'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
						'MESSAGE_TEXT'  => $message,
						'REFRESH_DATA'  => [
							'time' => 3,
							'url'  => $this->user->data['session_page'],
						],
					]);
				}

				$message .= '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->user->data['session_page'] . '">', '</a>');
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

			$submitted = true;
		}

		if ($this->user->data['failedlogins_count_last'] > 0 && $submitted === false)
		{
			$timestamp_last = $this->user->data['failedlogins_timestamp_last'];
			$formatted_date = $this->user->format_date($timestamp_last, 'l d F Y H:i', true);
			add_form_key($form_key);

			$this->template->assign_vars([
				'FAILED_LOGINS_NOTIFY'			=> $this->language->lang('FAILED_LOGINS_NOTIFY_LANG', (int) $this->user->data['failedlogins_count_last']),
				'FAILED_LOGINS_DATE'			=> $this->language->lang('FAILED_LOGINS_DATE_LANG', $formatted_date),
				'U_FAILED_LOGINS_ACTION'		=> generate_board_url() . '/' . $this->user->page['page'],
				'FAILED_LOGINS_AJAX_CALLBACK'	=> 'failedlogins.remove',
			]);
		}
	}
}
