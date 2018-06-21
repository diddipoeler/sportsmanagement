<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_summary.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage results
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
use Joomla\Registry\Registry;
?>
<!-- START of match summary -->
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

if (!empty($this->match->summary))
{
	?>
	<table width="100%" class="contentpaneopen">
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY' );
				?>
			</td>
		</tr>
	</table>
	<table class="matchreport">
		<tr>
			<td>
			<?php
			$summary = $this->match->summary;
			$summary = JHtml::_('content.prepare', $summary);

			if ($commentsDisabled) {
				$summary = preg_replace('#{jcomments\s+(off|lock)}#is', '', $summary);
			}
			echo $summary;

			?>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Comments integration
 */
if (!$commentsDisabled) {

$dispatcher = JDispatcher::getInstance();
$comments = '';
if(file_exists(JPATH_ROOT.'/components/com_jcomments/classes/config.php'))
		{
			require_once JPATH_ROOT.'/components/com_jcomments/classes/config.php';
			require_once JPATH_ROOT.'/components/com_jcomments/jcomments.class.php';
			require_once JPATH_ROOT.'/components/com_jcomments/models/jcomments.php';
		}

/**
 * load sportsmanagement comments plugin files
 */
		JPluginHelper::importPlugin('content','sportsmanagement_comments');

/**
 * get sportsmanagement comments plugin params
 */
		$plugin = JPluginHelper::getPlugin('content', 'sportsmanagement_comments');

	if (is_object($plugin)) {
		$pluginParams = new Registry($plugin->params);
	}
	else {
		$pluginParams = new Registry('');
	}
	$separate_comments 	= $pluginParams->get( 'separate_comments', 0 );

	if ($separate_comments) {
/**
 * Comments integration trigger when separate_comments in plugin is set to yes/1
 */
		if ($dispatcher->trigger( 'onNextMatchComments', array( &$this->match, $this->teams[0]->name .' - '. $this->teams[1]->name, &$comments ) )) {
			echo $comments;
		}
	}
	else {
/**
 * Comments integration trigger when separate_comments in plugin is set to no/0
 */
		if ($dispatcher->trigger( 'onMatchComments', array( &$this->match, $this->teams[0]->name .' - '. $this->teams[1]->name, &$comments ) )) {
			echo $comments;
		}
	}
	
	
}

?>
<!-- END of match summary -->
