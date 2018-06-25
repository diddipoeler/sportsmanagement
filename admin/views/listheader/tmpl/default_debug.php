<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

//echo ' _success_text<br><pre>'.print_r(sportsmanagementHelper::$_success_text,true).'</pre>';
?>

<div id='editcell'>
<?PHP

if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Define slides options
        $slidesOptions = array(
            "active" => "slide2_id" // It is the ID of the active tab.
        );    
echo JHtml::_('bootstrap.startAccordion', 'slide-group-id1', $slidesOptions);
echo JHtml::_('bootstrap.addSlide', 'slide-group-id1', JText::_('COM_SPORTSMANAGEMENT_DEBUG_INFO'), 'debug_info');

echo JHtml::_('bootstrap.startAccordion', 'slide-group-id2', $slidesOptions);
$array_schluessel = array_keys(sportsmanagementHelper::$_success_text);


for($a=0; $a < sizeof($array_schluessel); $a++ )
{
echo JHtml::_('bootstrap.addSlide', 'slide-group-id2', JText::_($array_schluessel[$a]), 'debug_info_text');
foreach (sportsmanagementHelper::$_success_text[$array_schluessel[$a] ] as $row)
{
?>
<fieldset>
<legend><?php echo JText::_($row->methode); ?></legend>
<table class='adminlist' width="100%"><tr><td><?php echo $row->line; ?></td><td><?php echo $row->text; ?></td></tr></table>
</fieldset>
<?php
}
echo JHtml::_('bootstrap.endSlide');		  
}
echo JHtml::_('bootstrap.endAccordion');

echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.endAccordion');
}
else
{   
$options = array(
    'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
    'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
    'startOffset' => 1,  // 0 starts on the first tab, 1 starts the second, etc...
    'useCookie' => true, // this must not be a string. Don't use quotes.
);    
echo JHtml::_('sliders.start', 'debug_info',$options);   
echo JHtml::_('sliders.panel', JText::_('COM_SPORTSMANAGEMENT_DEBUG_INFO'), 'debug_info'); 
$array_schluessel = array_keys(sportsmanagementHelper::$_success_text);

echo JHtml::_('sliders.start', 'debug_info_text',$options);
for($a=0; $a < sizeof($array_schluessel); $a++ )
{
echo JHtml::_('sliders.panel', JText::_($array_schluessel[$a]), 'debug_info_text');
foreach (sportsmanagementHelper::$_success_text[$array_schluessel[$a] ] as $row)
{
?>
			<fieldset>
				<legend><?php echo JText::_($row->methode); ?></legend>
				<table class='adminlist' width="100%"><tr><td><?php echo $row->line; ?></td><td><?php echo $row->text; ?></td></tr></table>
			</fieldset>
			<?php
}		  
}
echo JHtml::_('sliders.end');
echo JHtml::_('sliders.end');
}




?>
</div>