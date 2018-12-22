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
use Joomla\CMS\Dispatcher\Dispatcher;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

?>
<!-- START of match summary -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="matchreport">
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
	<table class="table ">
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;' . Text::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY' );
				?>
			</td>
		</tr>
	</table>
	<table class="table ">
		<tr>
			<td>
			<?php
			$summary = $this->match->summary;
			$summary = HTMLHelper::_('content.prepare', $summary);

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
if (version_compare(JSM_JVERSION, '4', 'eq')) {
            //$dispatcher = Dispatcher::getInstance();
        } else {
            $dispatcher = JDispatcher::getInstance();
        }
/**
 * load sportsmanagement comments plugin files
 */
PluginHelper::importPlugin( 'content', 'sportsmanagement_comments' );
$comments = '';

/**
 * get sportsmanagement comments plugin params
 */
	$plugin = PluginHelper::getPlugin('content', 'sportsmanagement_comments');
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
		if (Factory::getApplication()->triggerEvent( 'onMatchReportComments', array( &$this->match, $this->team1->name .' - '. $this->team2->name, &$comments ) )) {
			echo $comments;
		}
	}
	else {
		// Comments integration trigger when separate_comments in plugin is set to no/0
		if (Factory::getApplication()->triggerEvent( 'onMatchComments', array( &$this->match, $this->team1->name .' - '. $this->team2->name, &$comments ) )) {
			echo $comments;
		}
	}
}

?>
</div>
<!-- END of match summary -->

