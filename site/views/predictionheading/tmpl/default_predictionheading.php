<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
//$component_text = 'COM_SPORTSMANAGEMENT_';
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
					$link = JSMPredictionHelperRoute::getPredictionMemberRoute($this->predictionGame->id,$this->predictionMember->pmID,'edit');
					$imgTitle=JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_EDIT_IMAGE_TITLE');
					$desc = JHTML::image('media/com_sportsmanagement/jl_images/edit.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
					echo JHTML::link($link,$desc);
				}
                if ( $this->allowedAdmin )
                {
                echo '&nbsp;&nbsp;';
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_ENTRY_IMAGE_TITLE');
				$desc = JHTML::image(JUri::root().'media/com_sportsmanagement/jl_images/prediction_entry.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = JSMPredictionHelperRoute::getPredictionTippEntryRoute($this->predictionGame->id,$this->predictionMember->pmID);
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
				$img = JHTML::image(JUri::root().'media/com_sportsmanagement/jl_images/prediction_entry.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = JSMPredictionHelperRoute::getPredictionTippEntryRoute($this->predictionGame->id);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
				
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_MEMBER_IMAGE_TITLE');
				$img = JHTML::image(JUri::root().'media/com_sportsmanagement/jl_images/prediction_member.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				if ($this->predictionMember->pmID > 0){$pmVar=$this->predictionMember->pmID;}else{$pmVar=null;}
				$link = JSMPredictionHelperRoute::getPredictionMemberRoute($this->predictionGame->id,$pmVar);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
				
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RESULTS_IMAGE_TITLE');
				$img = JHTML::image(JUri::root().'media/com_sportsmanagement/jl_images/prediction_results.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = JSMPredictionHelperRoute::getPredictionResultsRoute($this->predictionGame->id);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
                
				$imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RANKING_IMAGE_TITLE');
				$img = JHTML::image(JUri::root().'media/com_sportsmanagement/jl_images/prediction_ranking.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = JSMPredictionHelperRoute::getPredictionRankingRoute($this->predictionGame->id,0,0,'',0,0);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
                
                if ( $this->config['show_pred_group_link'] )
                {
                $imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RANKING_GROUP_IMAGE_TITLE');
				$img = JHTML::image(JUri::root().'media/com_sportsmanagement/jl_images/teaminfo_icon.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = JSMPredictionHelperRoute::getPredictionRankingRoute($this->predictionGame->id,0,0,'',0,1);
				$output .= JHTML::link($link,$img,array('title' => $imgTitle));
				$output .= '&nbsp;';
                }
                
				$imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RULES_IMAGE_TITLE');
				$img = JHTML::image(JUri::root().'media/com_sportsmanagement/jl_images/prediction_rules.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
				$link = JSMPredictionHelperRoute::getPredictionRulesRoute($this->predictionGame->id);
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