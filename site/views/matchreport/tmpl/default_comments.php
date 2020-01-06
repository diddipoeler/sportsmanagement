<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_summary.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
use Joomla\CMS\Dispatcher\Dispatcher;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

?>
<!-- START of match summary -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="matchreport">
	<?php

/**
 * workaround to support {jcomments (off|lock)} in match summary
 * no comments are shown if {jcomments (off|lock)} is found in the match summary
 */

$commentsDisabled = 0;

if (!empty($this->match->summary) && preg_match('/{jcomments\s+(off|lock)}/is', $this->match->summary))
{
	$commentsDisabled = 1;
}

/**
 * Comments integration
 */
if (!$commentsDisabled)
{
	$commmentsInstance = sportsmanagementModelComments::CreateInstance($this->config);
	echo $commmentsInstance->showMatchComments($this->match, $this->team1, $this->team2, $this->config, $this->project);
}

	?>
</div>
<!-- END of match summary -->

