<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage players
 * @file       players_upload.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<fieldset class="adminform">
    <form action="<?php echo $this->request_url; ?>" method="post" enctype="multipart/form-data" id="adminForm" name="adminForm">
            <div class="form-inline">
              
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Add files</span>
                     <input type="file" name="fileToUpload" id="fileToUpload">
                </span>
                
         <button type="button" class="btn btn-primary start" onclick="this.form.submit();">
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start upload</span>
                </button>
                
                <button type="reset" class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel upload</span>
                </button>
                
              <button type="button" class="btn btn-danger delete">
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                
            </div>
            
            <input type="hidden" name="task" value="players.importupload"/>
            <?php echo HTMLHelper::_('form.token') . "\n"; ?>
            
          </form>
</fieldset>
