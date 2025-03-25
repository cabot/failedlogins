# Failed Logins

![phpBB 3.3.x Compatible](https://img.shields.io/badge/phpBB-3.3.x%20Compatible%20-blue.svg)

## Description
If a user tries to connect and the connection fails, this extension creates an entry in the user's log. The next time the user logs in, they will also see the number of failed attempts since their last login and the date and time of the last failed attempt.

## Install
1. Download the latest release `Source code`.
2. Unzip the downloaded release, and change the name of the folder to `failedlogins`.
3. In the `ext/` directory of your phpBB board, create a new directory named `cabot` (if it does not already exist).
4. Copy the `failedlogins` folder to `/ext/cabot/`.
5. Navigate in the ACP to `Customise -> Manage extensions`.
6. Look for `Failed logins Log and Notify` under the Disabled Extensions list, and click its `Enable` link.

## Uninstall
1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `Failed logins Log and Notify` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/cabot/failedlogins` folder.

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)
