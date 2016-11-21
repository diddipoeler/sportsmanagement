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

<!-- content -->
<?php
foreach ( $this->currentRanking as $division => $cu_rk )
{
	if ($division)
	{
		
$jRegistry = new JRegistry;
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $jRegistry->loadString($this->divisions[$division]->rankingparams); 
        }
        else
        {
        $jRegistry->loadJSON($this->divisions[$division]->rankingparams);
        }
        $configvalues = $jRegistry->toArray();
        $colors = array();
for($a=1; $a <= sizeof($configvalues[rankingparams]); $a++ )
{
$colors[] = implode(",",$configvalues[rankingparams][$a] );
}   
$configvalues = implode(";",$colors);       

$this->colors = sportsmanagementModelProject::getColors($configvalues,sportsmanagementModelProject::$cfg_which_database);        			

?>
	<table class="<?PHP echo $this->config['table_class']; ?>">
		<tr>
			<td class="contentheading">
				<?php
					//get the division name from the first team of the division 
					foreach( $cu_rk as $ptid => $team )
					{
						echo $this->divisions[$division]->name;
						break;
					}
				?>
			</td>
		</tr>
	</table>
<div class="table-responsive">
	<table class="<?PHP echo $this->config['table_class']; ?>">
	<?php
		foreach( $cu_rk as $ptid => $team )
		{
			echo $this->loadTemplate('rankingheading');
			break;
		}
		$this->division = $division;
		$this->current  = &$cu_rk;
        $this->teamrow  = 'tr';
		echo $this->loadTemplate('rankingrows');
	?>
	</table>
    </div>
	<?php
	}
	else
	{
	?>
    <div class="table-responsive">
	<table class="<?PHP echo $this->config['table_class']; ?>">
		<?php
			echo $this->loadTemplate('rankingheading');
			$this->division = $division;
			$this->current  = &$cu_rk;
            $this->teamrow  = 'tr';
			echo $this->loadTemplate('rankingrows');
		?>
	</table>
    </div>
	<br />
	<?php
	}
}
	?>
<!-- ranking END -->



