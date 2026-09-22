<?php
/**
 * Native Joomla 5/6 curve layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="<?php echo $this->divclasscontainer; ?>" id="curve">
	<?php
	echo $this->loadTemplate('projectheading');

	if ($this->config['show_sectionheader'])
	{
		echo $this->loadTemplate('sectionheader');
	}

	// If ( $this->config['show_curve'] )
	// {
	if ($this->config['which_curve'])
	{
		echo $this->loadTemplate('curvejs');
	}

	// Else
	// {
	// echo $this->loadTemplate('curve');
	// }
	// }

	if ($this->config['show_colorlegend'])
	{
		echo $this->loadTemplate('colorlegend');
	}

	echo $this->loadTemplate('jsminfo');
	?>

</div>
