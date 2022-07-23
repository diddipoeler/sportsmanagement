<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage uefawertung
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="<?php echo $this->divclasscontainer; ?>" id="uefawertung">
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="uefawertunganzeige">
		<?php
		if ($this->config['show_sectionheader'] == 1)
		{
			echo $this->loadTemplate('sectionheader');
		}


echo $this->lists('coefficientyears');






		echo $this->loadTemplate('jsminfo');
		?>
    </div>
</div>
