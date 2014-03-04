<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access');
$component_text = 'COM_SPORTSMANAGEMENT_';
?>
<?php
//echo '<br /><pre>~' . print_r($this,true) . '~</pre><br />';
if ((isset($this->config['show_prediction_heading'])) && ($this->config['show_prediction_heading']))
{
	?>
	<table class='blog' cellpadding='0' cellspacing='0' width='100%'>
		<tr>
			<td class='sectiontableheader'>
				<?php
				echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_HEAD_ACTUAL_PRED_GAME','<b><i>'.$this->predictionGame->name.'</i></b>');
				if ((isset($this->showediticon)) && ($this->showediticon) && ($this->predictionMember->pmID > 0))
				{
					echo '&nbsp;&nbsp;';
					$link = PredictionHelperRoute::getPredictionMemberRoute($this->predictionGame->id,$this->predictionMember->pmID,'edit');
					$imgTitle=JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_EDIT_IMAGE_TITLE');
					$desc = JHTML::image('media/com_sportsmanagement/jl_images/edit.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
					echo JHTML::link($link,$desc);
				}
                if ( $this->allowedAdmin )
                {
                echo '&nbsp;&nbsp;';
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_ENTRY_IMAGE_TITLE');
				$desc = JHTML::image(JURI::root().'media/com_sportsmanagement/jl_images/prediction_entry.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = PredictionHelperRoute::getPredictionTippEntryRoute($this->predictionGame->id,$this->predictionMember->pmID);
                echo JHTML::link($link,$desc);
					
					
                }
                
				?>
			</td>
			<?php
			if (!isset($this->allowedAdmin))
            {
                $this->allowedAdmin = false;
            }
			if ( ( $this->getName() == 'predictionusers' ) ||
       ( ( $this->allowedAdmin ) && ( $this->getName() == 'predictionentry' ) )
//        || ( $this->getName() == 'predictionresults' )
        )
			{
				?>
				<td class='sectiontableheader' style='text-align:right; ' width='15%'  nowrap='nowrap'>
					<form name='predictionMemberSelect' method='post' >
					<input type='hidden' name='prediction_id' value='<?php echo intval($this->predictionGame->id); ?>' />
					<input type='hidden' name='task' value='predictionusers.select' />
					<input type='hidden' name='option' value='com_sportsmanagement' />
					
					<?php echo JHTML::_('form.token'); ?>
						<?php 
            if ( $this->getName() == 'predictionresults' )
            {
            //echo $this->lists['predictionRounds'] ;
            }
            else
            {
            echo $this->lists['predictionMembers'];
            }
            
             
            
            
            ?>
					</form>
				</td>
				<?php
			}
			?>
			<td class='sectiontableheader' style='text-align:right; ' width='15%' nowrap='nowrap'>
				<?php
				$output = '';
				
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_ENTRY_IMAGE_TITLE');
				$img = JHTML::image(JURI::root().'media/com_sportsmanagement/jl_images/prediction_entry.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = PredictionHelperRoute::getPredictionTippEntryRoute($this->predictionGame->id);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
				
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_MEMBER_IMAGE_TITLE');
				$img = JHTML::image(JURI::root().'media/com_sportsmanagement/jl_images/prediction_member.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				if ($this->predictionMember->pmID > 0){$pmVar=$this->predictionMember->pmID;}else{$pmVar=null;}
				$link = PredictionHelperRoute::getPredictionMemberRoute($this->predictionGame->id,$pmVar);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
				
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RESULTS_IMAGE_TITLE');
				$img = JHTML::image(JURI::root().'media/com_sportsmanagement/jl_images/prediction_results.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = PredictionHelperRoute::getPredictionResultsRoute($this->predictionGame->id);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
                
				$imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RANKING_IMAGE_TITLE');
				$img = JHTML::image(JURI::root().'media/com_sportsmanagement/jl_images/prediction_ranking.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = PredictionHelperRoute::getPredictionRankingRoute($this->predictionGame->id,NULL,NULL,'',NULL,0);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
                
                if ( $this->config['show_pred_group_link'] )
                {
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RANKING_GROUP_IMAGE_TITLE');
				$img = JHTML::image(JURI::root().'media/com_sportsmanagement/jl_images/teaminfo_icon.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = PredictionHelperRoute::getPredictionRankingRoute($this->predictionGame->id,NULL,NULL,'',NULL,1);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
                }
                
				$imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RULES_IMAGE_TITLE');
				$img = JHTML::image(JURI::root().'media/com_sportsmanagement/jl_images/prediction_rules.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = PredictionHelperRoute::getPredictionRulesRoute($this->predictionGame->id);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));

				echo $output;
				?>
			</td>
			<?php
			
			?>
		</tr>
	</table><?php
}
?>