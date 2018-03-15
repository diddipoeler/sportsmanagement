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

//JHTML::script('JL_matchdetailsediting.js','administrator/components/com_joomleague/assets/js/');
//JHTML::script('JL_matcheventsediting.js','administrator/components/com_joomleague/assets/js/');

/*
$massadd=JFactory::getApplication()->input->getVar('massadd');

// Set toolbar items for the page
JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE'));

if (!$massadd)
{
	JToolbarHelper::publishList();
	JToolbarHelper::unpublishList();
	JToolbarHelper::divider();

	JToolbarHelper::apply('saveshort');
	JToolbarHelper::divider();

	//JToolbarHelper::custom('massadd','new.png','new_f2.png',JText::_('JL_ADMIN_MATCHES_MASSADD_MATCHES'),false);
	JToolbarHelper::addNewX('addmatch',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_ADD_MATCH'));
	JToolbarHelper::deleteList(JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_WARNING'));
	JToolbarHelper::divider();

	JToolbarHelper::back('Back','index.php?option=com_joomleague&controller=match&view=matches');
}
else
{
	JToolbarHelper::custom('cancelmassadd','cancel.png','cancel_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CANCEL_MATCHADD'),false);
}
JToolbarHelper::help('screen.joomleague',true);
*/

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>
<script type="text/javascript">
<!--
	window.addEvent('domready',function()
	{
		$$('tr.row-result').each(function(row)
		{
			var matchid=row.id.substr(7);
			var cb=row.getElement('input[id^=cb]');
			if (cb)
			{
				// item is not checked out
				row.getElements('select').addEvent('change',function() { cb.checked=true; });

				row.getElements('input').addEvent('change',function() { if (this.id != cb.id) { cb.checked=true; } });

				//special for calendar
				// row.getElements('.calendar').addEvent('click',function() { cb.checked=true; });

				//special for roster selection
				row.getElements('select[id^=team]').addEvent('change',function()
				{
					// handles the link ref for starting lineup window
					var matchid=this.id.substr(10);
					$E('a.openroster-'+this.id).href="index.php?option=com_sportsmanagement&tmpl=component&controller=match&task=editlineup&cid[]="+matchid+"&team="+this.value;
				});
			}
			else
			{
				//item is checked out
			}
			//alert(matchid);
			// we should replace all the inline 'onchange' with code here.

		});
	});

	function switchMenu(obj)
	{
		var el=document.getElementById(obj);
		if (el.style.display != "none")
		{
			el.style.display='none';
		}
		else
		{
			el.style.display='block';
		}
	}

	function copymatches ()
	{
		document.getElementById('addtype').value=2;
		document.copyform.submit();
		return true;
	}

	function addmatches ()
	{
		document.getElementById('addmatchescount').value=document.getElementById('tempaddmatchescount').value;
		document.getElementById('addtype').value=1;
		return true;
	}

	function displayTypeView()
	{
		if (document.getElementById('ct').value==0)
		{
			document.getElementById('massadd_standard').style.display='none';
			document.getElementById('massadd_type2').style.display='none';
		}
		else if (document.getElementById('ct').value==1)
		{
			document.getElementById('massadd_standard').style.display='block';
			document.getElementById('massadd_type2').style.display='none';
		}
		else if (document.getElementById('ct').value==2)
		{
			document.getElementById('massadd_standard').style.display='none';
			document.getElementById('massadd_type2').style.display='block';
		}
	}
	
	function SaveMatch(a,b)
	{
		var f = document.matrixForm;
		if(f)
		{  
			f.elements['teamplayer1_id'].value = a;  
			f.elements['teamplayer2_id'].value = b;  
			f.submit();  
		}  
	}	
//-->
</script>
<style>
.subsequentdecision {
	background-color: #BBB;
}
</style>
<div id="alt_decision_enter" style="display:<?php echo ($massadd == 0) ? 'none' : 'block'; ?>">





<?php 
//echo $this->loadTemplate('massadd'); ?>
</div>
<?php echo $this->loadTemplate('matches'); ?>
<?php echo $this->loadTemplate('matrix'); ?>
