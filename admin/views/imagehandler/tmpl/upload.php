<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       upload.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage imagehandler
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

?>
<div class="container-fluid">
<form method="post" action="<?php echo $this->request_url; ?>" enctype="multipart/form-data" id="adminForm" name="adminForm">

<table class="table">
      <tr>
        <td width="50%" valign="top">
        
                <?php if($this->ftp) : ?>
                <fieldset class="adminform">
                    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_FTP_TITLE'); ?></legend>

        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_FTP_DESC'); ?>
                    
        <?php if(!$this->ftp ) : ?>
                        <p><?php echo Text::_($this->ftp->message); ?></p>
        <?php endif; ?>

                    <table class="adminform nospace">
                        <tbody>
                            <tr>
                                <td width="120">
                                    <label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?>:</label>
                                </td>
                                <td>
                                    <input type="text" id="username" name="username" class="input_box" size="70" value="" />
                                </td>
                            </tr>
                            <tr>
                                <td width="120">
                                    <label for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?>:</label>
                                </td>
                                <td>
                                    <input type="password" id="password" name="password" class="input_box" size="70" value="" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
                <?php endif; ?>

            <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_SEL_IMG_UPLOAD'); ?></legend>
            <table class="admintable" cellspacing="1">
                <tbody>
                    <tr>
                          <td>
                             <input class="inputbox" name="userfile" id="userfile" type="file" />
                            <br /><br />
                            </td>
                            </tr>
                            <tr>
                          <td>
                            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MSG_INSTALL_ENTER_A_URL'); ?></legend>
              <input class="inputbox" type="text" id="linkaddress" name="linkaddress" size="50" maxlength="250" value="" />
              <br />
              
                            <input class="button" type="submit" value="<?php echo Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE') ?>" />
                           </td>
                      </tr>
                </tbody>
            </table>
            </fieldset>

        </td>
        <td width="50%" valign="top">

            <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ATTENTION'); ?></legend>
            <table class="admintable" cellspacing="1">
                <tbody>
                    <tr>
                          <td>
                                <?php
                                echo "<b>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_FIELD_PATH_IMAGE_FOLDER_LABEL').": "."</b>";
                                echo "/images/com_sportsmanagement/database/".$this->folder;    
                                echo "<br /><b>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_MEDIA_FILESIZE').": </b>".$this->params->get('image_max_size', 120)."kb<br />";
        ?>

        <?php
        if (isset($gd_info) ) {

            if (imagetypes() & IMG_PNG) {
                echo "<br /><font color='green'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_PNG_SUPPORT')."</font>";
            } else {
                echo "<br /><font color='red'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_PNG_SUPPORT')."</font>";
            }
            if (imagetypes() & IMG_JPEG) {
                echo "<br /><font color='green'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_JPG_SUPPORT')."</font>";
            } else {
                echo "<br /><font color='red'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_JPG_SUPPORT')."</font>";
            }
            if (imagetypes() & IMG_GIF) {
                echo "<br /><font color='green'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_GIF_SUPPORT')."</font>";
            } else {
                echo "<br /><font color='red'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_GIF_SUPPORT')."</font>";
            }
        } else {
            echo "<br /><font color='green'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_PNG_SUPPORT')."</font>";
            echo "<br /><font color='green'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_JPG_SUPPORT')."</font>";
            echo "<br /><font color='green'>".Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_GIF_SUPPORT')."</font>";
        }
        ?>
                           </td>
                      </tr>
                </tbody>
            </table>
            </fieldset>

        </td>
    </tr>
</table>

<?php if (isset($gd_info) ) { ?>

<table class="table">
    <tr>
        <td>

            <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ATTENTION'); ?></legend>
            <table class="admintable" cellspacing="1">
                <tbody>
                    <tr>
                          <td align="center">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_GD_WARNING'); ?>
                          </td>
                      </tr>
                </tbody>
            </table>
            </fieldset>

        </td>
    </tr>
</table>

<?php } ?>

<input type="hidden" name="option" value="com_sportsmanagement" />
<input type="hidden" name="task" value="imagehandler.upload" />
<input type="hidden" name="folder" value="<?php echo $this->folder;?>" />
<?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>
