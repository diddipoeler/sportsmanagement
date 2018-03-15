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

// check if any results returned
$items = count($list['ranking']);
if (!$items) {
   echo '<p class="modjlgranking">' . JText::_('NO ITEMS') . '</p>';
   return;
}

$columns     = explode(',', $params->get('columns', 'JL_PLAYED, JL_POINTS'));
$column_names = explode(',', $params->get('column_names', 'MP, PTS'));

if (count($columns) != count($column_names)) {
	JError::raiseWarning(1, JText::_('MOD_SPORTSMANAGEMENT_RANKING_COLUMN_NAMES_COUNT_MISMATCH'));
	$columns     = array();
	$column_name = array();
}

$nametype = $params->get('nametype', 'short_name');
$colors = $list['colors'];

//echo ' colors<br><pre>'.print_r($colors,true).'</pre>';


?>

<div class="container-fluid">
<div class="row-fluid">

<?php if ($params->get('show_project_name', 0)):?>
<p class="projectname"><?php echo $list['project']->name; ?></p>
<?php endif; ?>

<table class="<?php echo $params->get('table_class', 'table'); ?>">
	<thead>
		<tr class="sectiontableheader">
			<?php if( $params->get('showRankColumn') == 1 ) { ?>
			<th class="rank"><?php echo JText::_('MOD_SPORTSMANAGEMENT_RANKING_COLUMN_RANK')?></th>
			<?php } ?>
			<th class="team"><?php echo JText::_('MOD_SPORTSMANAGEMENT_RANKING_COLUMN_TEAM')?></th>
			<?php foreach ($column_names as $col): ?>
			<th class="rankcolval"><?php echo JText::_(trim($col)); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
	<?php
		$k = 0;
		$exParam = explode(':',$params->get('visible_team'));
		$favTeamId = $exParam[0];
		$projfavTeamId = $list['project']->fav_team;
		$favEntireRow = $params->get('fav_team_highlight_type', 0);

		$i = 0;
	?>
	<?php foreach (array_slice($list['ranking'], 0, $params->get('limit', 5)) as $item) :  ?>
		<?php
			//$class = $params->get('style_class2', 0);
			//if ( $k == 0 ) { $class = $params->get('style_class1', 0); }
			
            $i++;
			$color = "";
            
            //echo $item->rank.'<br>';
            
			if ( $params->get('show_rank_colors', 0) )
			{
			  foreach ($colors as $colorItem) 
              {
			    if ($item->rank >= $colorItem['from'] && $item->rank <= $colorItem['to']) 
                {
						$color = $colorItem['color'];
					}
				}
			}
			if (!$favTeamId) $favTeamId = $projfavTeamId;
			$rowStyle = ' style="';
			$spanStyle = '';
			if ( $item->team->id == $favTeamId)
			{
				if( trim( $list['project']->fav_team_color ) != "" )
				{
					if ($favEntireRow == 1) 
                    {
						$color = $list['project']->fav_team_color;
					}
				}
				if ($favEntireRow) 
                {
				  $rowStyle .= ($params->get('fav_team_bold', 0) != 0) ? 'font-weight:bold;' : '';
				  $rowStyle .= ($list['project']->fav_team_text_color != '') ? 'color:' . $list['project']->fav_team_text_color . ';' : '';
				}
				$spanStyle = '<span style="padding:2px;';
				$spanStyle .= ($params->get('fav_team_bold', 0) != 0) ? 'font-weight:bold;' : '';
				$spanStyle .= ($list['project']->fav_team_text_color != '') ? 'color:'.$list['project']->fav_team_text_color.';' : '';
				$spanStyle .= ($list['project']->fav_team_color != '') ? 'background-color:'.$list['project']->fav_team_color.';' : '';
				$spanStyle .= '">';

			}
			$rowStyle .= 'background-color:' . $color . ';';
			$rowStyle .= '"';

		?>
		<tr class="">
			<?php if( $params->get('showRankColumn') == 1) { ?>
			<td class="rank"<?php if ($color != '') echo $rowStyle; ?>><?php echo $item->rank; ?></td>
			<?php } ?>
			<td class="team"<?php if ($color != '') echo $rowStyle; ?>>
				<?php if ($params->get('show_logo', 0)): ?>
				<?php echo modJSMRankingHelper::getLogo($item, $params->get('show_logo', 0) ); ?>
				<?php endif; ?>
				<?php if ($spanStyle != '') echo $spanStyle; ?>
				<?php if ($params->get('teamlink', 'none') != 'none'): ?>
				<?php echo JHtml::link(modJSMRankingHelper::getTeamLink($item, $params, $list['project']), $item->team->$nametype); ?>
				<?php else: ?>
				<?php echo $item->team->$nametype; ?>
				<?php endif; ?>
				<?php if ($spanStyle != '') echo '</span>'; ?>
			</td>
			<?php foreach ($columns as $col): ?>
			<td class="rankcolval"<?php if ($color != '') echo $rowStyle; ?>>
			<?php echo modJSMRankingHelper::getColValue(trim($col), $item); ?>
			</td>
			<?php endforeach; ?>
		</tr>
	<?php $k = 1 - $k; ?>
	<?php endforeach; ?>
	</tbody>
</table>

<?php if ( $params->get('show_ranking_link', 1) ):?>
<p class="fulltablelink"><?php 
	$divisionid = explode(':', $params->get('division_id', 0));
	$divisionid = $divisionid[0];
    $routeparameter = array();
$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
$routeparameter['s'] = $params->get('s');
$routeparameter['p'] = $list['project']->slug;
$routeparameter['type'] = 0;
$routeparameter['r'] = $list['project']->round_slug;
$routeparameter['from'] = 0;
$routeparameter['to'] = 0;
$routeparameter['division'] = $divisionid;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking',$routeparameter);    
	echo JHtml::link($link,JText::_('MOD_SPORTSMANAGEMENT_RANKING_VIEW_FULL_TABLE')); ?></p>
<?php endif; ?>
</div>
</div>
