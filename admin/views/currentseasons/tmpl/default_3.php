<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
* OHNE JEDE GEW�HRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/ 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html.bootstrap');

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$count = 0;
?>

<?PHP
if ( $this->items )
{
foreach ($this->items as $item)
{
    
if ( !$count )
{
// Define slides options
        $slidesOptions = array(
            "active" => "slide".$item->id."_id" // It is the ID of the active tab.
        );    
// Define tabs options for version of Joomla! 3.0
        $tabsOptions = array(
            "active" => "tab".$item->id."_id" // It is the ID of the active tab.
        );      
}    
?>

<?PHP
$count++;	   
}   
}        
?>

       
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

<div id="jsm" class="admin override">

<div id="j-main-container" class="span10">
<section class="content-block" role="main">




<?php // This renders the beginning of the slides code. ?>
<?php echo JHtml::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions); ?>

<?PHP
if ( $this->items )
{
foreach ($this->items as $item)
{            
// Open the first slide
echo JHtml::_('bootstrap.addSlide', 'slide-group-id', JSMCountries::getCountryFlag($item->country).' '.$item->name, 'slide'.$item->id.'_id');
?>
<a class="btn" href="index.php?option=com_sportsmanagement&task=project.edit&id=<?PHP echo $item->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/projekte.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS') ?></span>
</a>

<a class="btn" href="index.php?option=com_sportsmanagement&view=templates&pid=<?PHP echo $item->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/templates.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_P_PANEL_FES') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_P_PANEL_FES') ?></span>
</a>

<?php
if ((isset($item->project_type)) &&
								 (($item->project_type == 'PROJECT_DIVISIONS') ||
								   ($item->project_type == 'DIVISIONS_LEAGUE')))
{
?>
<a class="btn" href="index.php?option=com_sportsmanagement&view=divisions&pid=<?PHP echo $item->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/divisionen.png" alt="<?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $item->count_projectdivisions) ?>" /><br />
<span><?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $item->count_projectdivisions) ?></span>
</a>								
<?php
}
                            
if ((isset($item->project_type)) &&
								(($item->project_type == 'TOURNAMENT_MODE') ||
								($item->project_type == 'DIVISIONS_LEAGUE')))
{

?>
<a class="btn" href="index.php?option=com_sportsmanagement&view=treetos&pid=<?PHP echo $item->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/turnierbaum.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE') ?></span>
</a>
<?PHP

}

if ( $item->project_art_id != 3 )
{
?>
<a class="btn" href="index.php?option=com_sportsmanagement&view=projectpositions&pid=<?PHP echo $item->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/positionen.png" alt="<?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $item->count_projectpositions) ?>" /><br />
<span><?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $item->count_projectpositions) ?></span>
</a>
<?PHP
}
?>

<a class="btn" href="index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=<?PHP echo $item->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/projektschiedsrichter.png" alt="<?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $item->count_projectreferees) ?>" /><br />
<span><?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $item->count_projectreferees) ?></span>
</a>		

<a class="btn" href="index.php?option=com_sportsmanagement&view=projectteams&pid=<?PHP echo $item->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/mannschaften.png" alt="<?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $item->count_projectteams) ?>" /><br />
<span><?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $item->count_projectteams) ?></span>
</a>

<a class="btn" href="index.php?option=com_sportsmanagement&view=rounds&pid=<?PHP echo $item->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/spieltage.png" alt="<?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $item->count_matchdays) ?>" /><br />
<span><?php echo JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $item->count_matchdays) ?></span>
</a>

<a class="btn" href="index.php?option=com_sportsmanagement&view=jlxmlexports&pid=<?PHP echo $item->project->id; ?>">
<img src="components/com_sportsmanagement/assets/icons/xmlexport.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT') ?></span>
</a>


<?PHP	
// This is the closing tag of the first slide
echo JHtml::_('bootstrap.endSlide');


}            
}
            
            
?>            
<?php // This renders the end part of the slides code. ?>	
<?php echo JHtml::_('bootstrap.endAccordion'); ?>





</section>
</div>
</div>

<?php echo $this->table_data_div; ?>