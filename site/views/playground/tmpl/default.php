<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       deafault.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

if (!$this->playground) {
    return;
}
?>

<div class="<?php echo $this->divclasscontainer; ?>" id="playground">
	<?php
	if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
	{
		echo $this->loadTemplate('debug');
	}

	echo $this->loadTemplate('projectheading');

	if (!empty($this->config['show_sectionheader']))
	{
		echo $this->loadTemplate('sectionheader');
	}

	if (!empty($this->config['show_playground']))
	{
		echo $this->loadTemplate('playground');
	}

	if (!empty($this->config['show_extended']))
	{
		echo $this->loadTemplate('extended');
	}

	if (!empty($this->config['show_picture']))
	{
		echo $this->loadTemplate('picture');
	}

	if (!empty($this->playground->latitude) && !empty($this->playground->longitude))
	{
		if (!empty($this->config['show_maps']))
		{
			echo $this->loadTemplate('googlemap');
		}
	}

	if (!empty($this->config['show_description']))
	{
		echo $this->loadTemplate('description');
	}

	if (!empty($this->config['show_teams']))
	{
		echo $this->loadTemplate('teams');
	}

	if (!empty($this->config['show_matches']))
	{
		echo $this->loadTemplate('matches');
	}

	if (!empty($this->config['show_played_matches']))
	{
		echo $this->loadTemplate('played_matches');
	}

	echo $this->loadTemplate('jsminfo');
	?>
</div>
