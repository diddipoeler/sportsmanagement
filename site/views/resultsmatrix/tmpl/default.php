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

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews', 'results', 'matrix');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<style>
/*** PANEL PRIMARY ***/
.with-nav-tabs.panel-primary .nav-tabs > li > a,
.with-nav-tabs.panel-primary .nav-tabs > li > a:hover,
.with-nav-tabs.panel-primary .nav-tabs > li > a:focus {
    color: #fff;
}
.with-nav-tabs.panel-primary .nav-tabs > .open > a,
.with-nav-tabs.panel-primary .nav-tabs > .open > a:hover,
.with-nav-tabs.panel-primary .nav-tabs > .open > a:focus,
.with-nav-tabs.panel-primary .nav-tabs > li > a:hover,
.with-nav-tabs.panel-primary .nav-tabs > li > a:focus {
	color: #fff;
	background-color: #3071a9;
	border-color: transparent;
}
.with-nav-tabs.panel-primary .nav-tabs > li.active > a,
.with-nav-tabs.panel-primary .nav-tabs > li.active > a:hover,
.with-nav-tabs.panel-primary .nav-tabs > li.active > a:focus {
	color: #428bca;
	background-color: #fff;
	border-color: #428bca;
	border-bottom-color: transparent;
}

</style>
<div class="row">
<a name="jl_top" id="jl_top"></a>
	<?php 
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
    echo $this->loadTemplate('debug');
}
	echo $this->loadTemplate('projectheading');
		
	echo $this->loadTemplate('selectround');


?>    
  
<div role="tabpanel" data-example-id="togglable-tabs" class="panel with-nav-tabs panel-primary">

  <!-- Tabs-Navs -->
  <ul class="nav nav-tabs" role="tablist" data-tabs="tabs" id="tabs">
    <li role="presentation" class="active">
    <a href="#results" role="tab" data-toggle="tab" id="results-tab" ><?PHP echo JText::_('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'); ?>
    </a>
    </li>
    <li role="presentation">
    <a href="#matrix" role="tab" data-toggle="tab"  id="matrix-tab" >
    <?PHP echo JText::_('COM_SPORTSMANAGEMENT_MATRIX'); ?>
    </a>
    </li>

  </ul>

  <!-- Tab-Inhalte -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade in active" id="results">
    <?PHP   
    echo $this->loadTemplate('results');
    ?>
    </div>
    <div role="tabpanel" class="tab-pane fade" id="matrix">
    <?PHP   
    if(isset($this->divisions) && count($this->divisions) > 1) 
  {
	echo $this->loadTemplate('matrix_division');
    }
    else
    {
	echo $this->loadTemplate('matrix');
    }
    ?>
    </div>
  </div>


</div>

      
<?PHP    

	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
