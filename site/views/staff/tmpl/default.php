<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage staff
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="<?php echo $this->divclasscontainer; ?>" id="staff">
	<?php
	echo $this->loadTemplate('projectheading');

	if ($this->config['show_sectionheader'])
	{
		echo $this->loadTemplate('sectionheader');
	}

	/**
	 * diddipoeler
	 * aufbau der templates
	 */
	$this->output = array();

	if ($this->config['show_info'])
	{
		echo $this->loadTemplate('info');
	}

	if ($this->config['show_extended'])
	{
		echo $this->loadTemplate('extended');
	}

	if ($this->config['show_status'])
	{
		echo $this->loadTemplate('status');
	}

	if ($this->config['show_description'])
	{
		echo $this->loadTemplate('description');
	}

	// General part of person view END

	if ($this->config['show_careerstats'])
	{
		echo $this->loadTemplate('careerstats');
	}

	if ($this->config['show_career'])
	{
		echo $this->loadTemplate('career');
	}

	echo $this->loadTemplate('jsminfo');

	?>

</div>
