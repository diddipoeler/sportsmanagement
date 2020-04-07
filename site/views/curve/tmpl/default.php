<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage curve
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Make sure that in case extensions are written for mentioned (common) views,
 * that they are loaded i.s.o. of the template of this view
 */
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="<?php echo $this->divclasscontainer;?>" id="curve">
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
