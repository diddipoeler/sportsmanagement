<?php
/**
 * Native Joomla 5/6 OAuth/import controller for Google calendars.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 OAuth/import controller for Google calendars. */
final class JsmgcalendarimportController extends BaseController
{
    public function import(): void
    {
        $input = $this->app->getInput();
        $code = (string) $input->get('code', '', 'raw');
        $state = trim((string) $input->getString('state'));
        $oauthError = trim((string) $input->getString('error'));
        $isOauthCallback = $code !== '' || $state !== '' || $oauthError !== '';

        if (!$isOauthCallback) {
            $this->checkToken();
        }

        $model = $this->getModel('Jsmgcalendarimport', 'Administrator', ['ignore_request' => false]);
        $result = $model !== false && $model->import();
        $message = Text::_($result
            ? 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_IMPORT_YES'
            : 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_IMPORT_NO'
        );

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=jsmgcalendars',
            $message,
            $result ? 'message' : 'error'
        );
    }
}
