<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextdfbkeyimport
 * @file       default_getdivision.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
	<?php
	echo '<br>' . HTMLHelper::_(
			'select.genericlist',
			$this->lists['divisions'],
			'divisionid',
			'class="inputbox" size="1" onchange=""',
			'value', 'text', $this->division
		);

	?>
    <input type="hidden" name="sent" value="1"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="projectid" value="<?php echo $this->project_id; ?> "/>

</form>
