<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage specialextensions
 */
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<table width="100%" border="0">
	<tr>
		<td width="100%" valign="top">
			<div id="cpanel">
				<?php 
                foreach ( $this->Extensions as $key => $value )
                {
                        echo $this->addIcon('extensions.png','index.php?option=com_sportsmanagement&view='.$value.'', Text::_($value));
                }
                ?>

			</div>
		</td>
	</tr>
</table>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   