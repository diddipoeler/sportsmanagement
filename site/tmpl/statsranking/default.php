<?php
/**
 * Native Joomla 5/6 statistics ranking layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

?>
<div class="">
	<?php
	if ($this->config['show_sectionheader'] == 1)
	{
		echo $this->loadTemplate('sectionheader');
	}

	echo $this->loadTemplate('projectheading');

	echo $this->loadTemplate('stats');
	echo $this->loadTemplate('jsminfo');
	?>

</div>
