<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage eventsranking
 */

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="container-fluid">
	<?php
	if ($this->config['show_sectionheader']==1)
	{
		echo $this->loadTemplate('sectionheader');
	}

	echo $this->loadTemplate('projectheading');

	if (($this->config['show_eventsstats'])==1)
	{
		echo $this->loadTemplate('eventsrank');
	}
?>
<div>
<?php
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');

?>
</div>
</div>