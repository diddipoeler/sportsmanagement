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

//// welche joomla version ?
//if(version_compare(JVERSION,'3.0.0','ge')) 
//{
////JHtml::_('jquery.framework');
//}

//JHtml::_('behavior.tooltip');
//JHtml::_('behavior.formvalidation');
//$params = $this->form->getFieldsets('params');


//echo 'sportsmanagementViewMatch _displayEditReferees project_id<br><pre>'.print_r($this->project_id,true).'</pre>';
//echo 'sportsmanagementViewMatch _displayEditReferees item->id<br><pre>'.print_r($this->item->id,true).'</pre>';
//echo 'sportsmanagementViewMatch _displayEditReferees lists<br><pre>'.print_r($this->lists,true).'</pre>';

?>
<style>
/*  #myModal1 .modal-dialog {
    width: 80%;
  }
*/  
.modal-dialog {
    width: 80%;
  }  
.modal-dialog,
.modal-content {
    /* 95% of window height */
    height: 95%;
}  
</style>

<body>
<!--
<div id="helloworld">
  <h1>Hello <?php //echo $displayData['name']; ?>!</h1>
</div>
-->

<div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title">Modal title</h4>
            </div>			<!-- /modal-header -->
            <div class="modal-body">
            <p>Excitavit hic ardor milites per municipia plurima, quae isdem conterminant, dispositos et castella, sed quisque serpentes latius pro viribus repellere moliens, nunc globis confertos, aliquotiens et dispersos multitudine superabatur ingenti.</p>
            </div>			<!-- /modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>			<!-- /modal-footer -->
        </div>         <!-- /modal-content -->
    </div>     <!-- /modal-dialog -->
    
</body>    