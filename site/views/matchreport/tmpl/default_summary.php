<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_summary.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<!-- START of match summary -->
<?php

// workaround to support {jcomments (off|lock)} in match summary
// no comments are shown if {jcomments (off|lock)} is found in the match summary
$commentsDisabled = 0;

if (!empty($this->match->summary) && preg_match('/{jcomments\s+(off|lock)}/is', $this->match->summary))
{
	$commentsDisabled = 1;
}

if (!empty($this->match->summary))
{
	?>
	<table class="table table-responsive">
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY' );
				?>
			</td>
		</tr>
	</table>
	<table class="table table-responsive">
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

// Comments integration
if (!$commentsDisabled) 
{

$dispatcher = JDispatcher::getInstance();
JPluginHelper::importPlugin( 'content', 'joomleague_comments' );
$comments = '';

	// get joomleague comments plugin params
	$plugin= JPluginHelper::getPlugin('content', 'joomleague_comments');
	if (is_object($plugin)) 
    {
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $pluginParams = new JRegistry($plugin->params);
        }
        else
        {
		$pluginParams = new JParameter($plugin->params);
        }
	}
	else 
    {
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $pluginParams = new JRegistry('');
        }
        else
        {
		$pluginParams = new JParameter('');
        }
	}
	$separate_comments 	= $pluginParams->get( 'separate_comments', 0 );

	if ($separate_comments) {

	// Comments integration trigger when separate_comments in plugin is set to yes/1
		if ($dispatcher->trigger( 'onMatchReportComments', array( &$this->match, $this->team1->name .' - '. $this->team2->name, &$comments ) )) {
			echo $comments;
		}
	}
	else {
		// Comments integration trigger when separate_comments in plugin is set to no/0
		if ($dispatcher->trigger( 'onMatchComments', array( &$this->match, $this->team1->name .' - '. $this->team2->name, &$comments ) )) {
			echo $comments;
		}
	}
}

?>
<!-- END of match summary -->

