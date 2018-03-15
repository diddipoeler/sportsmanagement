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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
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
echo JHtml::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions);
echo JHtml::_('bootstrap.addSlide', 'slide-group-id', JText::_('COM_SPORTSMANAGEMENT_DEBUG_INFO'), 'debug_info');
foreach (sportsmanagementHelper::$_success_text as $key => $value)
		{
			?>
			<fieldset>
				<legend><?php echo JText::_($key); ?></legend>
				<table class='adminlist'><tr><td><?php echo '<pre>'.print_r($value,true).'</pre>' ; ?></td></tr></table>
			</fieldset>
			<?php
		}
echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.endAccordion');
}
else
{   
?>


<div class="panel-group" id="accordion">
<?PHP
$array_schluessel = array_keys(sportsmanagementHelper::$_success_text);

for($a=0; $a < sizeof($array_schluessel); $a++ )
{
?>    
<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#accordion" href="#<?php echo JText::_($array_schluessel[$a]); ?>"><?php echo JText::_($array_schluessel[$a]); ?></a>
</h4>
</div>
<?PHP    
foreach (sportsmanagementHelper::$_success_text[$array_schluessel[$a] ] as $row)
{
?>
<div id="<?php echo JText::_($array_schluessel[$a]); ?>" class="panel-collapse collapse">
<div class="panel-body">
<table class="adminlist"><tr><td><?php echo $row->line; ?></td><td><?php echo $row->text; ?></td></tr></table>
</div>
</div>

<?php
}
?>
</div>

<?PHP		  
}
?>
</div>


<!--
<div class="panel-group" id="accordion">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">1. What is HTML?</a>
                </h4>
            </div>
            <div id="collapseOne" class="panel-collapse collapse in">
                <div class="panel-body">
                    <p>HTML stands for HyperText Markup Language. HTML is the main markup language for describing the structure of Web pages. <a href="http://www.tutorialrepublic.com/html-tutorial/" target="_blank">Learn more.</a></p>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">2. What is Bootstrap?</a>
                </h4>
            </div>
            <div id="collapseTwo" class="panel-collapse collapse">
                <div class="panel-body">
                    <p>Bootstrap is a powerful front-end framework for faster and easier web development. It is a collection of CSS and HTML conventions. <a href="http://www.tutorialrepublic.com/twitter-bootstrap-tutorial/" target="_blank">Learn more.</a></p>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">3. What is CSS?</a>
                </h4>
            </div>
            <div id="collapseThree" class="panel-collapse collapse">
                <div class="panel-body">
                    <p>CSS stands for Cascading Style Sheet. CSS allows you to specify various style properties for a given HTML element such as colors, backgrounds, fonts etc. <a href="http://www.tutorialrepublic.com/css-tutorial/" target="_blank">Learn more.</a></p>
                </div>
            </div>
        </div>
    </div>
-->
<?PHP

}




?>
</div>