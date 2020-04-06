<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   GCalendar
 * @author    Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
?>
<fieldset>
    <legend>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_LABEL');?>
    </legend>
    <form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jsmgcalendarimport');?>" method="post" name="adminForm" id="adminForm">
        <div class="row-fluid">
            <div class="span6">
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_name-lbl" for="user"
                            class="hasTip required invalid" title="" aria-invalid="true"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_NAME');?><span
                            class="star">&nbsp;*</span> </label>
                    </div>
                    <div class="controls">
                        <input type="text" name="user" id="user" value="<?PHP  echo ComponentHelper::getParams('com_sportsmanagement')->get('google_mail_account', ''); ?>"
                            class="inputbox required invalid" size="100" aria-required="true"
                            required="required" aria-invalid="true">
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_name-lbl" for="pass"
                            class="hasTip required invalid" title="" aria-invalid="true"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_PASSWORD');?><span
                            class="star">&nbsp;*</span> </label>
                    </div>
                    <div class="controls">
                        <input type="password" name="pass" id="pass" value="<?PHP  echo ComponentHelper::getParams('com_sportsmanagement')->get('google_mail_password', ''); ?>"
                            class="inputbox required invalid" size="100" aria-required="true"
                            required="required" aria-invalid="true">
                    </div>
                </div>
              
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_name-lbl" for="google_api_clientid"
                            class="hasTip required invalid" title="" aria-invalid="true"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_CLIENTID');?><span
                            class="star">&nbsp;*</span> </label>
                    </div>
                    <div class="controls">
                        <input type="text" name="google_api_clientid" id="google_api_clientid" value="<?PHP  echo ComponentHelper::getParams('com_sportsmanagement')->get('google_api_clientid', ''); ?>"
                            class="inputbox required invalid" size="200" aria-required="true"
                            required="required" aria-invalid="true">
                    </div>
                </div>
              
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_name-lbl" for="google_api_clientsecret"
                            class="hasTip required invalid" title="" aria-invalid="true"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_CLIENTSECRET');?><span
                            class="star">&nbsp;*</span> </label>
                    </div>
                    <div class="controls">
                        <input type="text" name="google_api_clientsecret" id="google_api_clientsecret" value="<?PHP  echo ComponentHelper::getParams('com_sportsmanagement')->get('google_api_clientsecret', ''); ?>"
                            class="inputbox required invalid" size="200" aria-required="true"
                            required="required" aria-invalid="true">
                    </div>
                </div>
              
              
              
              
            </div>
        </div>
        <input type="submit" value="Login" class="btn"/>
        <input type="" name="task" value="jsmgcalendarimport.login" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</fieldset>

