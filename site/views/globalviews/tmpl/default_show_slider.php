<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<div class="row" id="show_slider">
<?php

if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
$idxTab = 1;
$view = Jrequest::getCmd('view');
foreach ($this->output as $key => $templ) 
{
if ( $idxTab == 1 )
{
echo JHtml::_('bootstrap.startAccordion', $view, array('active' => 'slide'.$idxTab));
}
echo JHtml::_('bootstrap.addSlide', $view, JText::_($key), 'slide'.$idxTab++);
echo $this->loadTemplate($templ);
echo JHtml::_('bootstrap.endSlide');
}

echo JHtml::_('bootstrap.endAccordion');
        
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here    
?>

<div class="panel-group" id="accordion-nextmatch">
<?PHP    
foreach ( $this->output as $key => $templ ) 
    {
?>    
<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#accordion-nextmatch" href="#<?php echo $key; ?>"><?php echo JText::_($key); ?></a>
</h4>
</div>

<div id="<?php echo $key; ?>" class="panel-collapse collapse">
<div class="panel-body">
<?PHP  

switch ($templ)
{
    case 'previousx':
    $this->currentteam = $this->match->projectteam1_id;
echo $this->loadTemplate($templ);
$this->currentteam = $this->match->projectteam2_id;
echo $this->loadTemplate($templ);
    break;
    default:
    echo $this->loadTemplate($templ);
    break;
}  
 
?>
</div>
</div>
</div>

<?PHP	    
    }         
        
    ?>
    </div>
<?PHP
} 
elseif(version_compare(JVERSION,'1.7.0','ge')) 
{
// Joomla! 1.7 code here
} 
elseif(version_compare(JVERSION,'1.6.0','ge')) 
{
// Joomla! 1.6 code here
} 
else 
{
// Joomla! 1.5 code here
}
?>    