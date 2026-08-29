<?php
/** Native Joomla 5/6 Google Calendar OAuth login template. */
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$params = ComponentHelper::getParams('com_sportsmanagement');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jsmgcalendarimport'); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-validate">
    <fieldset class="options-form">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_LABEL'); ?></legend>

        <div class="row">
            <div class="col-lg-8 col-xl-6">
                <div class="control-group">
                    <div class="control-label">
                        <label for="user" class="required">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_NAME'); ?>
                            <span class="star" aria-hidden="true">&#160;*</span>
                        </label>
                    </div>
                    <div class="controls">
                        <input type="text"
                               name="user"
                               id="user"
                               value="<?php echo htmlspecialchars((string) $params->get('google_mail_account', ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="form-control required"
                               required>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="pass" class="required">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_PASSWORD'); ?>
                            <span class="star" aria-hidden="true">&#160;*</span>
                        </label>
                    </div>
                    <div class="controls">
                        <input type="password"
                               name="pass"
                               id="pass"
                               value="<?php echo htmlspecialchars((string) $params->get('google_mail_password', ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="form-control required"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="google_api_clientid" class="required">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_CLIENTID'); ?>
                            <span class="star" aria-hidden="true">&#160;*</span>
                        </label>
                    </div>
                    <div class="controls">
                        <input type="text"
                               name="google_api_clientid"
                               id="google_api_clientid"
                               value="<?php echo htmlspecialchars((string) $params->get('google_api_clientid', ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="form-control required"
                               autocomplete="off"
                               required>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="google_api_clientsecret" class="required">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_LOGIN_AUTH_DEFAULT_FIELD_CLIENTSECRET'); ?>
                            <span class="star" aria-hidden="true">&#160;*</span>
                        </label>
                    </div>
                    <div class="controls">
                        <input type="password"
                               name="google_api_clientsecret"
                               id="google_api_clientsecret"
                               value="<?php echo htmlspecialchars((string) $params->get('google_api_clientsecret', ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="form-control required"
                               autocomplete="off"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?php echo Text::_('JLOGIN'); ?>
                </button>
            </div>
        </div>

        <input type="hidden" name="task" value="jsmgcalendarimport.import">
        <?php echo HTMLHelper::_('form.token'); ?>
    </fieldset>
</form>
