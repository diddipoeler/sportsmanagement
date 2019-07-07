<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      edit_list_imports.tpl.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage jlextdfbkeyimport
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

?>

<div id="right">
	<div id="rightpad">
	
<!-- Header START -->
		<div id="step">
			<div class="t">
        <div class="t">
          <div class="t"></div>
        </div>
      </div>
      <div class="m" align="left">
				<div class="far-right">
							<div class="button1-left"><div class="help"><a href="http://www.joomleague.de/wiki" alt="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_MANUAL;?>" title="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_MANUAL;?>" target="blank"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_MANUAL;?></a></div></div>
							<div class="button1-left"><div class="forum"><a href="http://www.joomleague.de/forum/" alt="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_FORUM;?>" title="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_FORUM;?>" target="blank"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_FORUM;?></a></div></div>
							<div class="button1-left"><div class="about"><a href="http://www.joomleague.de" alt="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_INFO;?>" title="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_INFO;?>" target="blank"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_INFO;?></a></div></div>
				</div>
				<div class="button1-left"><div class="blank"><a href="index2.php?option=com_joomleague"></a></div></div><span class="step"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_IMPORTS_LIST_IMPORTS;?></span>
			</div>
      <div class="b">
        <div class="b">
          <div class="b"></div>
        </div>
      </div>
    </div>
<!-- Header END -->     

    <div id="installer">
    
<!-- Title START -->    
			<div class="t">
        <div class="t">
          <div class="t"></div>
        </div>
      </div>
      <div class="m" align="left">
      	<h2><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_IMPORTS_TITLE;?></h2>		
<!-- Titel END -->

<!-- Info START -->	 			
		<div id="step">
			<div class="t">
        <div class="t">
          <div class="t"></div>
        </div>
      </div>
      <div class="m" align="left">
<table class="content" cellpadding="4">
 <tr>
  <td><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_IMPORTS_HINT1;?>  
  </td>
 </tr>
</table>
			</div>
      <div class="b">
        <div class="b">
          <div class="b"></div>
        </div>
      </div>
    </div>
<!-- Info END -->				

<!-- Content START -->		
				<div class="install-text2">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist2">
  	<thead>
    <tr>  		
    <th width="20"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_IMPORTS_DATE;?></th>
    <th width="15%"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_IMPORTS_PROJECT;?></th>
    <th width="15%"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_IMPORTS_LINK;?></th>
    <th width="70%"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_IMPORTS_INFO;?></th>
  	</tr>
  	</thead>
    <tr>
    <td width="20" nowrap="nowrap">20.05.2007</td>
    <td width="15%" nowrap="nowrap">Fussball Bundesliga Saison 2006/2007 *</td>
    <td width="15%" nowrap="nowrap"><a href="<?php echo sprintf(COM_SPORTSMANAGEMENT_ADMIN_COMPONENT_LINK."&act=%s&id=1", $act);?>">jl_buli_06_07</a></td>
    <td width="70%" >Die erste Fussball Bundesliga mit allen Vereinen, Mannschaften, Spieltagen und Ergebnissen (bis zum Erstellungsdatum)<br />
    <b>Folgende Daten werden ersetzt:</b> Projekt mit ID1, Liga mit ID1, Saison mit ID1, Vereine und Mannschaften mit den IDs 1-18 und alle Spieltage und Spiele
    die Projekt ID1 zugewiesen sind. Wenn Du bereits eigene Daten angelegt hast, diesen Import bitte nicht verwenden!</td>
    </tr>
    <tr>
    <td width="20" nowrap="nowrap">28.06.2007</td>
    <td width="15%" nowrap="nowrap">Fussball Bundesliga Saison 2007/2008 *</td>
    <td width="15%" nowrap="nowrap"><a href="<?php echo sprintf(COM_SPORTSMANAGEMENT_ADMIN_COMPONENT_LINK."&act=%s&id=4", $act);?>">jl_buli_07_08</a></td>
    <td width="70%" >Die erste Fussball Bundesliga mit allen Vereinen, Mannschaften, Spieltagen und Ergebnissen.<br />
    <b>Voraussetzungen:</b> Import der Saison 2006/2007 <b>und</b> Import der Spielorte der 1. Fussball Bundesliga!<br />
    Dieser Import kann auch verwendet werden wenn schon eigene Projekte angelegt wurden, da die IDs vorher kontrolliert werden.</td>
    </tr>    
    <tr>
    <td width="20" nowrap="nowrap">23.02.2007</td>
    <td width="15%" nowrap="nowrap">Standard Ereignisse für Fussball Projekte</td>
    <td width="15%" nowrap="nowrap"><a href="<?php echo sprintf(COM_SPORTSMANAGEMENT_ADMIN_COMPONENT_LINK."&act=%s&id=2", $act);?>">jl_events</a></td>
    <td width="70%" nowrap="nowrap">Mit diesem Import werden einige Ereignistypen hinzugefügt z.B. gelbe Karte, rote Karte usw.</td>
    </tr> 
    <tr>
    <td width="20" nowrap="nowrap">23.02.2007</td>
    <td width="15%" nowrap="nowrap">Schiedsrichter der 1. Fussball Bundesliga</td>
    <td width="15%" nowrap="nowrap"><a href="<?php echo sprintf(COM_SPORTSMANAGEMENT_ADMIN_COMPONENT_LINK."&act=%s&id=3", $act);?>">jl_referees</a></td>
    <td width="70%" nowrap="nowrap">Alle Schiedsrichter der Fussball Bundesliga (Stand: Februar 2007)</td>
    </tr>
    <tr>
    <td width="20" nowrap="nowrap">25.06.2007</td>
    <td width="15%" nowrap="nowrap">Spielorte der 1. Fussball Bundesliga</td>
    <td width="15%" nowrap="nowrap"><a href="<?php echo sprintf(COM_SPORTSMANAGEMENT_ADMIN_COMPONENT_LINK."&act=%s&id=5", $act);?>">jl_playgrounds</a></td>
    <td width="70%" nowrap="nowrap">Alle Spielorte der Fussball Bundesliga (Stand: Juni 2007)</td>
    </tr>
    <tr>
    <td width="20" nowrap="nowrap">13.07.2007</td>
    <td width="15%" nowrap="nowrap">Länderdatenbank</td>
    <td width="15%" nowrap="nowrap"><a href="<?php echo sprintf(COM_SPORTSMANAGEMENT_ADMIN_COMPONENT_LINK."&act=%s&id=6", $act);?>">jl_countries</a></td>
    <td width="70%" nowrap="nowrap">Alle Länder für die Verwendung in JoomLeague (Stand: Juni 2007)</td>
    </tr>

<tr>
    <td width="20" nowrap="nowrap">28.09.2007</td>
    <td width="15%" nowrap="nowrap">DFB-Schlüssel</td>
    <td width="15%" nowrap="nowrap"><a href="<?php echo sprintf("index2.php?option=%s&act=%s&id=7",$option, $act);?>">jl_dfbkeys</a></td>
    <td width="70%" nowrap="nowrap">DFB-Schlüssel für die Verwendung in JoomLeague </td>
    </tr>
    
    
    
		</table>
				</div>
<!-- Content END -->	
			
        <div class="clr"></div>
      </div>
      <div class="b">
        <div class="b">
          <div class="b"></div>
        </div>
      </div>
		</div>
	</div>
</div>
<div class="clr"></div>
