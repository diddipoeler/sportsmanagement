<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @package   GCalendar
 * @license   GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$params = ComponentHelper::getParams('com_sportsmanagement');
?>
<fieldset>
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_LABEL'); ?></legend>
    <form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jsmgcalendarimport'); ?>"
          method="post" name="adminForm" id="adminForm">
        <div class="row-fluid">
            <div class="span6">
                <div class="control-group">
                    <div class="control-label">
                        <label for="user" class="required">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_NAME'); ?>
                            <span class="star">&nbsp;*</span>
                        </label>
                    </div>
                    <div class="controls">
                        <input type="text" name="user" id="user"
                               value="<?php echo htmlspecialchars((string) $params->get('google_mail_account', ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="inputbox required" size="100" required>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <label for="pass" class="required">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_PASSWORD'); ?>
                            <span class="star">&nbsp;*</span>
                        </label>
                    </div>
                    <div class="controls">
                        <input type="password" name="pass" id="pass"
                               value="<?php echo htmlspecialchars((string) $params->get('google_mail_password', ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="inputbox required" size="100" required>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <label for="google_api_clientid" class="required">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_CLIENTID'); ?>
                            <span class="star">&nbsp;*</span>
                        </label>
                    </div>
                    <div class="controls">
                        <input type="text" name="google_api_clientid" id="google_api_clientid"
                               value="<?php echo htmlspecialchars((string) $params->get('google_api_clientid', ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="inputbox required" size="200" required>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <label for="google_api_clientsecret" class="required">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_CLIENTSECRET'); ?>
                            <span class="star">&nbsp;*</span>
                        </label>
                    </div>
                    <div class="controls">
                        <input type="password" name="google_api_clientsecret" id="google_api_clientsecret"
                               value="<?php echo htmlspecialchars((string) $params->get('google_api_clientsecret', ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="inputbox required" size="200" required>
                    </div>
                </div>
            </div>
        </div>
        <input type="submit" value="Login" class="btn">
        <input type="hidden" name="task" value="jsmgcalendarimport.import">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</fieldset>
