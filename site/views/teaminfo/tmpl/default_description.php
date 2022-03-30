<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       deafult_description.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>



<?php  
$this->notes = array();
$this->notes[] = Text::_('Beschreibung Projektteam');
echo $this->loadTemplate('jsm_notes');
?>       
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_description">
		<?php
		$description = $this->team->notes;
		$description = HTMLHelper::_('content.prepare', $description);
		echo $description;
		?>
    </div>
	
<?php  
$this->notes = array();
$this->notes[] = Text::_('Beschreibung Stammdaten Team');
echo $this->loadTemplate('jsm_notes');
?>       
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_description">
		<?php
		$description = $this->team->teamnotes;
		$description = HTMLHelper::_('content.prepare', $description);
		echo $description;
		?>
    </div>







<?php



?>
<br/>
