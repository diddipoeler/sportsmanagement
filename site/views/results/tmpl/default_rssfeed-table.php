<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'club rssfeedoutput<pre>',print_r($this->rssfeedoutput,true),'</pre><br>';
}
$app	=& JFactory::getApplication();

?>
<div class="no-column">
	<div class="contentpaneopen">
		<div class="contentheading">
			<?php echo JText::_('COM_SPORTSMANAGEMENT_PROJECT_LIVE_RSS_FEEDS'); ?>
		</div>
	</div>


<div class="srfrContainer <?php echo $moduleclass_sfx; ?>">
	
	<?php if($this->feedsBlockPreText): ?>
	<p class="srfrPreText"><?php echo $this->feedsBlockPreText; ?></p>
	<?php endif; ?>
	
	<table width="100%" class="srfrList">
		
    <?php
    $rssfeedrow = 1; 
    foreach($this->rssfeedoutput as $key => $feed): 
    if ( $rssfeedrow %2 ) 
    { 
    echo "<tr>"; 
    }
     
    ?>
    
		<td class="srfrRow<?php echo $key%2; ?>">
			<?php if($this->feedItemTitle): ?>
			<h3><a target="_blank" href="<?php echo $feed->itemLink; ?>"><?php echo $feed->itemTitle; ?></a></h3>
			<?php endif; ?>
			
			<?php if($this->feedTitle): ?>
			<span class="srfrFeedSource">
				<a target="_blank" href="<?php echo $feed->siteURL; ?>"><?php echo $feed->feedTitle; ?></a>
			</span>
			<?php endif; ?>

			<?php if($this->feedItemDate): ?>
			<span class="srfrFeedItemDate"><?php echo $feed->itemDate; ?></span>
			<?php endif; ?>			

			<?php if($this->feedItemDescription || $feed->feedImageSrc): ?>
			<p>
				<?php if($feed->feedImageSrc): ?>
				<a target="_blank" href="<?php echo $feed->itemLink; ?>">
					<img class="srfrImage" src="<?php echo $feed->feedImageSrc; ?>" alt="<?php echo $feed->itemTitle; ?>" />
				</a>
				<?php endif; ?>
				
				<?php if($this->feedItemDescription): ?>
				<?php echo $feed->itemDescription; ?>
				<?php endif; ?>
			</p>
			<?php endif; ?>
			
			<?php if($this->feedItemReadMore): ?>
			<span class="srfrReadMore">
				<a target="_blank" href="<?php echo $feed->itemLink; ?>"><?php echo JText::_('MOD_JW_SRFR_READ_MORE'); ?></a>
			</span>
			<?php endif; ?>
			
			<span class="clr"></span>
		</td>
    
		<?php 
    $rssfeedrow++;
    if ( $rssfeedrow %2 ) 
    { 
    echo "</tr>"; 
    }
    endforeach; 
    ?>	
    
	</table>
	
  
  
	<?php if($this->feedsBlockPostText): ?>
	<p class="srfrPostText"><?php echo $this->feedsBlockPostText; ?></p>
	<?php endif; ?>
	
	<?php if($this->feedsBlockPostLink): ?>
	<p class="srfrPostTextLink"><a href="<?php echo $this->feedsBlockPostLinkURL; ?>"><?php echo $this->feedsBlockPostLinkTitle; ?></a></p>
	<?php endif; ?>
</div>

<div class="clr"></div>
</div>
