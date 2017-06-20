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

// No direct access to this file
defined('_JEXEC') or die('Restricted access'); 

//FIXME not functional ? 
// this code was copied from results model, editablerow function

//JHTML::_('behavior.modal', 'a.user-modal');

?>
<style>
/*  #myModal1 .modal-dialog {
    width: 80%;
  }
*/  

/*
.modaljsm {
    width: 80%;
    height: 60%;
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


<script type="text/javascript" language="javascript" >



      
function fillContainer(site)
      { 
        var string = "echo $this->loadTemplate('edit');";
        // Speichert den Inhalt des Attributes in der Variablen site.
        //var site = $(this).data('site'); 
        alert(site);
        document.getElementById('bigcontent').innerHTML = string;
        // Seite laden und in .content einsetzen.
        //jQuery("#bigcontent").load('edit');
        //jQuery('#bigcontent').load("http://www.google.de");
        jQuery('#content').load(site); 
      }      
      
      function fillContainer2()
      { var string = '<div class="container"><div class="header"><img src="images/logo.png" width="100" height="200" alt="Logo" title="Logo" /></div><div class="content"><h1>Titel</h1><div class="text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse aliquet, justo eget adipiscing suscipit, mauris nisl dapibus magna, eget gravida enim est faucibus dolor. Donec at leo vitae metus tempus consequat. Aliquam erat volutpat. Vestibulum eu leo tortor, eget mattis lorem. Sed pharetra turpis sit amet massa mattis dignissim.</div></div><div class="footer">&copy; Copyright 2001</div></div>';
        document.getElementById('bigcontent').innerHTML = string;
      }
 
      function fillContainer3()
      { var string = '<div class="container">'+
                     '  <div class="header">'+
                     '    <img src="images/logo.png" width="100" height="200" alt="Logo" title="Logo" />'+
                     '  </div>'+
                     '  <div class="content">'+
                     '    <h1>Titel</h1>'+
                     '    <div class="text">'+
                     '      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse aliquet, justo eget adipiscing suscipit, mauris nisl dapibus magna, eget gravida enim est faucibus dolor. Donec at leo vitae metus tempus consequat. Aliquam erat volutpat. Vestibulum eu leo tortor, eget mattis lorem. Sed pharetra turpis sit amet massa mattis dignissim.'+
                     '    </div>'+
                     '  </div>'+
                     '  <div class="footer">'+
                     '    &copy; Copyright 2001'+
                     '  </div>'+
                     '</div>';
        document.getElementById('bigcontent').innerHTML = string;
      }
    </script>

<?php 
		$match = $this->game;
		$i = $this->i;
		$thismatch = JTable::getInstance('Match','sportsmanagementTable');
		$thismatch->bind(get_object_vars($match));

//echo ' thismatch<br><pre>'.print_r($thismatch,true).'</pre>';

		list($datum,$uhrzeit)=explode(' ',$thismatch->match_date);

		if (isset($this->teams[$thismatch->projectteam1_id])){$team1=$this->teams[$thismatch->projectteam1_id];}
		if (isset($this->teams[$thismatch->projectteam2_id])){$team2=$this->teams[$thismatch->projectteam2_id];}
		//echo '<br /><pre>~'.print_r($team1,true).'~</pre><br />';
		$user = JFactory::getUser();

		if (isset($team1) && isset($team2))
		{
			$userIsTeamAdmin = ($user->id==$team1->admin || $user->id==$team2->admin);
		}
		else
		{
			$userIsTeamAdmin = $this->isAllowed;
		}
		$teams = $this->teams;
		$teamsoptions[] = JHtml::_('select.option','0','- '.JText::_('Select Team').' -');
		foreach ($teams AS $team)
        {
            $teamsoptions[] = JHtml::_('select.option',$team->projectteamid,$team->name,'value','text');
        }
        $user = JFactory::getUser();
        $canEdit = $user->authorise('core.edit','com_sportsmanagement');
        $canCheckin = $user->authorise('core.manage','com_checkin') || $thismatch->checked_out == $user->get ('id') || $thismatch->checked_out == 0;
        $checked = JHtml::_('jgrid.checkedout', $i, $user->get ('id'), $thismatch->checked_out_time, 'matches.', $canCheckin);
        
		//$checked	= JHtml::_('grid.checkedout',$match,$i,'id');
		$published	= JHtml::_('grid.published',$match,$i);

		list($date,$time) = explode(" ",$match->match_date);
		$time = strftime("%H:%M",strtotime($time));
		?>
<tr id="result-<?php echo $match->id; ?>" class="row-result">
	<td valign="top"><?php
	
	if ($thismatch->checked_out && $thismatch->checked_out != $my->id)
	{
		$db= JFactory::getDBO();
		$query="	SELECT username
				FROM #__users
				WHERE id=".$match->checked_out;
		$db->setQuery($query);
		$username=$db->loadResult();
		?>
		<acronym title="CHECKED OUT BY <?php echo $username; ?>">X</acronym>';
		<?php
	}
	else
	{
		?>
		<input type='checkbox' id='cb<?php echo $i; ?>' name='cid[]' value='<?php echo $thismatch->id; ?>' />
	</td>
	<!-- Edit match details -->
	<td valign="">
		<?php
        
        // über das backend/administrator bearbeiten
		//JHtml::_('behavior.modal','a.mymodal');
		$url = sportsmanagementHelperRoute::getEditMatchRoute(sportsmanagementModelResults::$projectid,$thismatch->id,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,sportsmanagementModelResults::$divisionid,'form');
		$imgTitle = JText::_('COM_SPORTSMANAGEMENT_EDIT_MATCH_DETAILS_BACKEND');
		$desc = JHtml::image(JURI::root().'media/com_sportsmanagement/jl_images/edit.png',$imgTitle, array('id' => 'edit'.$thismatch->id,'border' => 0,'width' => 20,'title' => $imgTitle));
        
        ?>
<!-- Button HTML (to Trigger Modal) -->
<a href="<?php echo $url; ?>" rel="modaljsm:open"><img src="<?php echo JURI::root().'media/com_sportsmanagement/jl_images/edit.png'; ?>" > </a>

   
    </td>
    
    <?PHP
    $append=' class="inputbox" size="1" onchange="document.getElementById(\'cb<?php echo $i; ?>\').checked=true; " style="font-size:9px;" ';
    ?>
    <td style="text-align:center; " >
    <?PHP
    echo JHtml::_('select.genericlist', $this->roundsoption, 'round_id'.$thismatch->id, $append, 'value', 'text', $thismatch->round_id);
    //echo JHtml::_('select.genericlist', $this->roundsoption, 'round_id'.$thismatch->id, $append, 'value', 'text', $thismatch->round_slug);
    ?>
    </td>
		<?php 
		if( $this->project->project_type == 'DIVISIONS_LEAGUE' ) 
        {
		?>
	<td style="text-align:center; " >
		<?php echo $match->divhome; ?>
	</td>
		<?php 
		} 
		?>
	<td align='center' valign='top'>
    <input type='text' style='font-size: 9px;' class='inputbox' size='3' name='match_number<?php echo $thismatch->id; ?>'
		value="<?php echo $thismatch->match_number;?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
	</td>
	<!-- Edit date -->
	<td nowrap='nowrap' align='center' valign='top'>
	<?php
if(version_compare(JVERSION,'3.0.0','ge')) 
{
?> 
<!--   
<div class="well form-inline">
			  <div class="input-append date" id="<?php echo 'match_date'.$thismatch->id;?>" data-date="12-02-2012" data-date-format="dd-mm-yyyy">
				<input class="span2" size="16" type="text" value="<?php echo sportsmanagementHelper::convertDate($datum,1);?>" readonly >
				<span class="add-on"><i class="icon-th"></i></span>
			  </div>
          </div>
-->
<div class="well">
<input type="text" class="span2" name='match_date<?php echo $thismatch->id; ?>' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " value="<?php echo sportsmanagementHelper::convertDate($datum,1);?>" data-date-format="dd-mm-yyyy" id="<?php echo 'match_date'.$thismatch->id;?>" >
</div>
                    
<script>
jQuery('#<?php echo 'match_date'.$thismatch->id;?>').datepicker();
</script>        
<?PHP            
/*
echo JHtml::calendar(sportsmanagementHelper::convertDate($datum,1),
					'match_date'.$thismatch->id,
					'match_date'.$thismatch->id,
					'%d-%m-%Y',
					'size="9"  style="font-size:9px;" onchange="document.getElementById(\'cb'.$i.'\').checked=true; "');
*/
}
else
{     
    echo JHtml::calendar(sportsmanagementHelper::convertDate($datum,1),
					'match_date'.$thismatch->id,
					'match_date'.$thismatch->id,
					'%d-%m-%Y',
					'size="9"  style="font-size:9px;" onchange="document.getElementById(\'cb'.$i.'\').checked=true; "'); 
}                    
                    ?>
	</td>
	<!-- Edit start time -->
	<td align='center' nowrap='nowrap' valign='top'>
		<input type='text' style='font-size: 9px;' size='3' name='match_time<?php echo $thismatch->id; ?>' value='<?php echo substr($uhrzeit,0,5); ?>'
			class='inputbox' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
	</td>
	<!-- Edit time present -->
	<td align='center' nowrap='nowrap' valign='top'>
		<input type='text' style='font-size: 9px;' size='3' name='time_present<?php echo $thismatch->id; ?>' value='<?php echo substr($thismatch->time_present,0,5); ?>'
			class='inputbox' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
	</td>
	<!-- Edit home team -->
	<td align="center" nowrap="nowrap" valign="top">
		<!-- Edit home line-up -->
		<?php
        
        // über das backend/administrator bearbeiten
//$url = sportsmanagementHelperRoute::getEditMatchRoute(sportsmanagementModelResults::$projectid,$thismatch->id,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,sportsmanagementModelResults::$divisionid,'form');        
		$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid,$thismatch->id,null,$team1->projectteamid,$datum,null,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,0,'form');
		$imgTitle = JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_HOME');
		//$desc = JHtml::image(	JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png', $imgTitle,array(' title' => $imgTitle,' border' => 0));
		$desc = JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png';
        
        //echo sportsmanagementHelperHtml::getBootstrapModalImage('home_lineup'.$team1->projectteamid,$desc,$imgTitle,'20',$url);
?>
			
				<?php
                        
        ?>
<!--
 <a href="#" data-toggle="modal" data-target-color="lightblue" data-target="#bannerformmodal<?php echo $team1->projectteamid; ?>">Load me <?php echo $team1->projectteamid; ?></a>
-->
<!--		
<a href="<?php echo $url; ?>" rel="modaljsm:open">
<img src="<?php echo JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png'; ?>" >
</a>
-->		
<!--		
<a href="#home_lineup<?php echo $thismatch->id; ?>" data-toggle="modal" data-target-color="lightblue" ><img src="<?php echo JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png'; ?>" ></a>
-->
<a data-target="#home_lineup<?php echo $thismatch->id; ?>"  data-toggle="modal" data-target-color="lightblue" ><img src="<?php echo JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png'; ?>" ></a>		
<div class="modal fade" 

tabindex="-1" 
role="dialog" 
aria-labelledby="home_lineup" 
aria-hidden="true"  
id="home_lineup<?php echo $thismatch->id; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
     <!-- <div class="modal-content"> -->
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel">Contact Form</h4>
        </div>
        <div class="modal-body">
<iframe scrolling="yes" allowtransparency="true" src="<?php echo $url; ?>" height="500" frameborder="0" width="99.6%"></iframe>                       
          
          
        </div>
        <div class="modal-footer">

          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        </div>        
     <!-- </div> -->
    </div>
  </div>
</div>

<!-- Button HTML (to Trigger Modal) -->
<!--
<a href="<?php echo $url; ?>" rel="modaljsm:open"><img src="<?php echo JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png'; ?>" > </a>
-->


		<!-- Edit home team -->
			<?php
		$append=' class="inputbox" size="1" onchange="document.getElementById(\'cb'.$i.'\').checked=true; " style="font-size:9px;" ';
		if ((!$userIsTeamAdmin) and (!$match->allowed)){$append .= ' disabled="disabled"';}
		if (!isset($team1->projectteamid)){$team1->projectteamid=0;}
		echo JHtml::_('select.genericlist', $teamsoptions, 'projectteam1_id'.$thismatch->id, $append, 'value', 'text', $team1->projectteamid);
		if ($this->config['results_below'])
		{
			?><br />
			<?php
		}
		else
		{
			?>
	</td>
	<!-- Edit away team -->
	<td nowrap='nowrap' align='center' valign='top'>
		<!-- Edit away team -->
		<?php
		}
		if (!isset($team2->projectteamid)){$team2->projectteamid=0;}
		echo JHtml::_('select.genericlist', $teamsoptions, 'projectteam2_id'.$thismatch->id, $append, 'value', 'text', $team2->projectteamid);
		?>
		<!-- Edit away line-up -->
		<?php
        
        // über das backend/administrator bearbeiten
		$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid,$thismatch->id,null,$team2->projectteamid,$datum,null,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,0,'form');
		$imgTitle = JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_AWAY');
		//$desc = JHtml::image(	JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png', $imgTitle,array(' title' => $imgTitle,' border' => 0));
        $desc = JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png';
        
//        echo sportsmanagementHelperHtml::getBootstrapModalImage('away_lineup'.$team2->projectteamid,$desc,$imgTitle,'20',$url);
		?>

<!-- Button HTML (to Trigger Modal) -->
<!--
<a href="<?php echo $url; ?>" rel="modaljsm:open"><img src="<?php echo JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png'; ?>" > </a>
-->


<a data-target="#away_lineup<?php echo $thismatch->id; ?>"  data-toggle="modal" data-target-color="lightblue" ><img src="<?php echo JURI::root().'administrator/components/com_sportsmanagement/assets/images/players_add.png'; ?>" ></a>		
<div class="modal fade" 

tabindex="-1" 
role="dialog" 
aria-labelledby="away_lineup" 
aria-hidden="true"  
id="away_lineup<?php echo $thismatch->id; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
     <!-- <div class="modal-content"> -->
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel">Contact Form</h4>
        </div>
        <div class="modal-body">
<iframe scrolling="yes" allowtransparency="true" src="<?php echo $url; ?>" height="500" frameborder="0" width="99.6%"></iframe>                                 
          
          
        </div>
        <div class="modal-footer">

          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        </div>        
     <!-- </div> -->
    </div>
  </div>
</div>



	
    </td>
	<!-- Edit match results -->
	<?php
		if ($this->config['results_below'])
		{
			$partresults1=explode(';',$thismatch->team1_result_split);
			$partresults2=explode(';',$thismatch->team2_result_split);

			for ($x=0; $x<($this->project->game_parts); $x++)
			{
			?>
	<td align='center' valign='top'>
		<input type='text' style='font-size: 9px;' name='team1_result_split<?php echo $thismatch->id; ?>[]' size='2' tabindex='1' class='inputbox'
			value='<?php echo (isset($partresults1[$x])) ? $partresults1[$x] : ''; ?>' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " /> <br />
		<input type='text' style='font-size: 9px;' name='team2_result_split<?php echo $thismatch->id; ?>[]' size='2' tabindex='1' class='inputbox'
			value='<?php echo (isset($partresults2[$x])) ? $partresults2[$x] : ''; ?>' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
	</td>
			<?php
			}

			if ($this->project->allow_add_time)
			{
			?>
	<td valign='top' align='center'>
		<span	id="ot<?php echo $thismatch->id; ?>" style="visibility:<?php echo ($thismatch->match_result_type > 0) ? 'visible' : 'hidden'; ?>">
			<input type="text" style="font-size: 9px;" name="team1_result_ot<?php echo $thismatch->id; ?>"
				value="<?php echo (isset($thismatch->team1_result_ot)) ? $thismatch->team1_result_ot : ''; ?>"
				size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
			<br />
			<input type="text" style="font-size: 9px;" name="team1_result_ot<?php echo $thismatch->id; ?>"
				value="<?php echo (isset($thismatch->team2_result_ot)) ? $thismatch->team2_result_ot : ''; ?>"
				size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		</span>
	</td>
			<?php
			}
			?>
	<td class="nowrap" valign="top" align="center">
		<input type="text" style="font-size: 9px;" name="team1_result<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team1_result; ?>" size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		<br />
		<input type="text" style="font-size: 9px;" name="team2_result<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team2_result; ?>" size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
	</td>

			<?php
			if ($this->project->use_legs)
			{
			?>
	<td valign="top" align="center">
		<input type="text" style="font-size: 9px;" name="team1_legs<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team1_legs; ?>" size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		<br />
		<input type="text" style="font-size: 9px;" name="team2_legs<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team2_legs; ?>" size="2" tabindex="1" class="inputbox" />
	</td>
			<?php
			}
		}
		else
		{
		?>
	<td class="nowrap" align="right" valign="top">
		<input type="text" style="font-size: 9px;" name="team1_result<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team1_result; ?>" size="1" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		<b>:</b>
		<input type="text" style="font-size: 9px;" name="team2_result<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team2_result; ?>" size="1" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		&nbsp; <?php echo $this->editPartResults($i,$thismatch); ?>
	</td>
			<?php
			if ($this->project->use_legs)
			{
			?>
	<td valign="top" align="center">
		<input type="text" style="font-size: 9px;" name="team1_legs<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team1_legs; ?>" size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		<b>:</b>
		<input type="text" style="font-size: 9px;" name="team2_legs<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team2_legs; ?>" size="2" tabindex="1" class="inputbox" />
	</td>
			<?php
			}
			if ($this->project->allow_add_time)
			{
			?>
	<td align='center' valign='top'><?php
		$xrounds=array();
		$xrounds[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_RESULTS_REGULAR_TIME'));
		$xrounds[]=JHtml::_('select.option','1',JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME2'));
		$xrounds[]=JHtml::_('select.option','2',JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT2'));

		echo JHtml::_(	'select.genericlist', $xrounds, 'match_result_type'.$thismatch->id, 'class="inputbox" size="1" style="font-size:9px;"
				onchange="document.getElementById(\'cb'.$i.'\').checked=true;if (this.selectedIndex==0) $(\'ot'.$thismatch->id .
				'\').style.visibility=\'hidden\';else $(\'ot'.$thismatch->id.'\').style.visibility=\'visible\';"',
				'value', 'text', $thismatch->match_result_type);
		?>
	</td>
		<?php
		}
		?>
	<!-- Edit match events -->
	<td valign="top">
		<?php
        
        // über das backend/administrator bearbeiten
		$url = sportsmanagementHelperRoute::getEditEventsRoute(sportsmanagementModelResults::$projectid,$thismatch->id,null,null,null,null,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,sportsmanagementModelResults::$divisionid,'form');
		$imgTitle = JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS_BACKEND');
		//$desc = JHtml::image(JURI::root().'media/com_sportsmanagement/jl_images/events.png', $imgTitle,array(' title' => $imgTitle,' border' => 0));
        $desc = JURI::root().'media/com_sportsmanagement/jl_images/events.png';
        
        //echo sportsmanagementHelperHtml::getBootstrapModalImage('edit_events'.$thismatch->id,$desc,$imgTitle,'20',$url);
        
		?>
        
<!-- Button HTML (to Trigger Modal) -->
<!--
<a href="<?php echo $url; ?>" rel="modaljsm:open"><img src="<?php echo JURI::root().'media/com_sportsmanagement/jl_images/events.png'; ?>" > </a>
-->

<a href="#edit_events<?php echo $thismatch->id; ?>" data-toggle="modal" data-target-color="lightblue" ><img src="<?php echo JURI::root().'media/com_sportsmanagement/jl_images/events.png'; ?>" ></a>

<div class="modal fade edit_events<?php echo $thismatch->id; ?>" 
data-modal-color="" 
data-backdrop="static" 
data-keyboard="false"
tabindex="-1" 
role="dialog" 
aria-labelledby="edit_events" 
aria-hidden="true"  
id="edit_events<?php echo $thismatch->id; ?>">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
     <!-- <div class="modal-content"> -->
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel">Contact Form</h4>
        </div>
        <div class="modal-body">
          
          <form id="requestacallform" method="POST" name="requestacallform">

<?PHP
//echo $this->loadTemplate('editevents');
?>
          </form>
          
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-blue">Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        </div>        
     <!-- </div> -->
    </div>
  </div>
</div>


	</td>
	<!-- Edit match statistics -->
	<td valign="top">
		<?php
        
        // über das backend/administrator bearbeiten
		$url = sportsmanagementHelperRoute::getEditStatisticsRoute(sportsmanagementModelResults::$projectid,$thismatch->id,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,sportsmanagementModelResults::$divisionid,'form');
		$imgTitle = JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS_BACKEND');
		//$desc = JHtml::image(JURI::root().'administrator/components/com_sportsmanagement/assets/images/calc16.png', $imgTitle,array(' title' => $imgTitle,' border' => 0));
        $desc = JURI::root().'administrator/components/com_sportsmanagement/assets/images/calc16.png';
        
        //echo sportsmanagementHelperHtml::getBootstrapModalImage('edit_statistics'.$thismatch->id,$desc,$imgTitle,'20',$url);
		?>
        
<!-- Button HTML (to Trigger Modal) -->
<!--
<a href="<?php echo $url; ?>" rel="modaljsm:open"><img src="<?php echo JURI::root().'administrator/components/com_sportsmanagement/assets/images/calc16.png'; ?>" > </a>
-->

<a href="#edit_statistics<?php echo $thismatch->id; ?>" data-toggle="modal" data-target-color="lightblue" ><img src="<?php echo JURI::root().'administrator/components/com_sportsmanagement/assets/images/calc16.png'; ?>" ></a>

<div class="modal fade edit_statistics<?php echo $thismatch->id; ?>" 
data-modal-color="" 
data-backdrop="static" 
data-keyboard="false"
tabindex="-1" 
role="dialog" 
aria-labelledby="edit_statistics" 
aria-hidden="true"  
id="edit_statistics<?php echo $thismatch->id; ?>">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
     <!-- <div class="modal-content"> -->
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel">Contact Form</h4>
        </div>
        <div class="modal-body">
          
          <form id="requestacallform" method="POST" name="requestacallform">

<?PHP
echo $this->loadTemplate('editstats');
?>
          </form>
          
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-blue">Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        </div>        
     <!-- </div> -->
    </div>
  </div>
</div>


	</td>
	<!-- Edit referee -->
	<td valign="top">
		<?php
        
        // über das backend/administrator bearbeiten
		$url = sportsmanagementHelperRoute::getEditRefereesRoute($this->project->id,$thismatch->id);
		$imgTitle = JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_REFEREE_BACKEND');
		//$desc = JHtml::image(	JURI::root().'/administrator/components/com_sportsmanagement/assets/images/players_add.png', $imgTitle,array(' title' => $imgTitle,' border' => 0));
        $desc = JURI::root().'/administrator/components/com_sportsmanagement/assets/images/players_add.png';
        //echo $url; 
         //echo sportsmanagementHelperHtml::getBootstrapModalImage('edit_referees'.$thismatch->id,$desc,$imgTitle,'20',$url);
		?>
        
<!-- Button HTML (to Trigger Modal) -->
<!-- 
<a href="<?php echo $url; ?>" rel="modaljsm:open"><img src="<?php echo JURI::root().'media/com_sportsmanagement/jl_images/players_add.png'; ?>" ></a>
-->

<a href="#edit_referees<?php echo $thismatch->id; ?>" data-toggle="modal" data-target-color="lightblue" ><img src="<?php echo JURI::root().'media/com_sportsmanagement/jl_images/players_add.png'; ?>" ></a>

<div class="modal fade edit_referees<?php echo $thismatch->id; ?>" 
data-modal-color="" 
data-backdrop="static" 
data-keyboard="false"
tabindex="-1" 
role="dialog" 
aria-labelledby="edit_referees" 
aria-hidden="true"  
id="edit_referees<?php echo $thismatch->id; ?>">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
     <!-- <div class="modal-content"> -->
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel">Contact Form</h4>
        </div>
        <div class="modal-body">
          
          <form id='edit-referee-<?php echo $this->game->id;?>' method="POST" name="requestacallform" action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>">

<?PHP
echo $this->loadTemplate('editreferees');
?>
          </form>
          
        </div>
        <!--
        <div class="modal-footer">
          <button type="submit" class="btn btn-blue">Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        </div>   
        -->     
     <!-- </div> -->
    </div>
  </div>
</div>

	</td>
	<!-- Published -->
	<td valign='top' style='text-align: center;'>
		<input type='checkbox' name='published<?php echo $thismatch->id; ?>' id='cbp<?php echo $thismatch->id; ?>'
			value='<?php echo ((isset($thismatch->published)&&(!$thismatch->published)) ? 0 : 1); ?>'
			<?php if ($thismatch->published){echo ' checked="checked" '; } ?>
			onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; if(document.adminForm.cbp<?php echo $thismatch->id; ?>.value==0){document.adminForm.cbp<?php echo $thismatch->id; ?>.value=1;}else{document.adminForm.cbp<?php echo $thismatch->id; ?>.value=0;}" />
	</td>
	<?php
	}
	?>
<?php
}
?>
</tr>

