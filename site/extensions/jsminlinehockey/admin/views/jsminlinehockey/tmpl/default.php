<?PHP
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage jsminlinehockey
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>

<div id="editcell">
	
    
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
    
<form enctype='multipart/form-data' action='<?php echo $this->request_url; ?>' method='post' id='adminForm' name='adminForm'>


<fieldset style='text-align: center; '>
<input type="text" id="matchlink" name="matchlink"  value="<?php echo $this->matchlink; ?>"  size="100" maxlength="100" >

<input type="radio" name="check" value="clubs" checked="checked"> Vereine
<input type="radio" name="check" value="teams"> Mannschaften
<input type="radio" name="check" value="players"> Spieler
	
<input class='input_box' id='import_package' name='import_package' type='file' size='57' />
<input class='button' type='submit' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_UPLOAD_BUTTON'); ?>' />
</fieldset>
<input type='hidden' name='sent' value='1' />
<input type='hidden' name='projectid' value='<?php echo $this->projectid ; ?>' />		
<input type='hidden' name='task' value='jsminlinehockey.save' />
                    
</form>


</div>

<?PHP
//echo $this->loadTemplate('jsminfo');
?> 
