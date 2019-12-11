<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage treetonodes
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;


?>
<div id="editcell">
<legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_LEGEND','<i>'.$this->projectws->name.'</i>'); ?></legend>
<?php
$attribs['width'] = '16px';
$attribs['height'] = '18px';
$dl = HTMLHelper::_('image', $this->path.'treedl.gif','', $attribs);
$ul = HTMLHelper::_('image', $this->path.'treeul.gif','', $attribs);
$cl = HTMLHelper::_('image', $this->path.'treecl.gif','', $attribs);
$dr = HTMLHelper::_('image', $this->path.'treedr.gif','', $attribs);
$ur = HTMLHelper::_('image', $this->path.'treeur.gif','', $attribs);
$cr = HTMLHelper::_('image', $this->path.'treecr.gif','', $attribs);
$p = HTMLHelper::_('image', $this->path.'treep.gif','', $attribs);
$h = HTMLHelper::_('image', $this->path.'treeh.gif','', $attribs);

$i = $this->treetows->tree_i;		//depth
$r = 2*(pow(2,$i)); 			//rows
$c = 2*$i+1;        			//columns
$col_hide = $c-2*($this->treetows->hide);	//tournament with multiple winners
echo '<table class="table">';

	for($j=1;$j<$r;$j++)
	{
	if($this->node[$j-1]->published ==0) //hide rows
	{
		;
	}
	else
	{
		echo '<tr>';
		echo '<td height=18px></td>';
		for($k=1;$k<=$c;$k++)
		{
			if($k > $col_hide ) //hide columns
			{
				;
			}
			else
			{
			echo '<td ';
			for($w=0;$w<=$i;$w++)
			{
				if(( $k == (1+($w*2)) ) && ( $j % (2*(pow(2,$w))) == (pow(2,$w)) ))
				{
					echo "$this->style";
				}
			}
			echo ' >';
			
			for($w=0;$w<=$i;$w++)
			{
				if(( $k == (1+($w*2)) ) && ( $j % (2*(pow(2,$w))) == (pow(2,$w))))
				{
// node __________________________________________________________________________________________________
$checked = HTMLHelper::_('grid.checkedout',$this->node[$j-1], $j-1);
if( $this->treetows->leafed == 1 )
{
	echo $this->node[$j-1]->node;
	if($this->node[$j-1]->team_id)
	{
echo $checked;
$marker = $j - 1;
?>
<script type="text/javascript">
document.getElementById('cb<?php echo $marker;?>').checked=true;
</script>
<input type="hidden" id="team_id<?php echo $this->node[$j-1]->id;?>" name="team_id<?php echo $this->node[$j-1]->id;?>" value="<?php echo $this->node[$j-1]->team_id;?>" >
<?php		
		
			$link = Route::_( 'index.php?option=com_sportsmanagement&task=treetonode.edit&id='.$this->node[$j-1]->id.'&tid='.$this->jinput->get('tid').'&pid='.$this->jinput->get('pid') );
			$ednode ='<a href='. $link . '>';
			$ednode .=HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/edit.png','edit');
			$ednode .='</a>';
			echo $ednode;
			echo $this->node[$j-1]->team_name;
			$link3 = Route::_( 'index.php?option=com_sportsmanagement&view=treetomatchs&layout=default&nid=' . $this->node[$j-1]->id . '&tid='.$this->treetows->id .'&pid='.$this->jinput->get('pid'));
            $match ='<a href='. $link3 . '>';
			$match .=HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/matches.png','edit');
			$match .='</a>';
			echo $match;
			$link4 = Route::_( 'index.php?option=com_sportsmanagement&view=treetomatchs&layout=editlist&nid=' . $this->node[$j-1]->id.'&tid='.$this->jinput->get('tid').'&pid='.$this->jinput->get('pid') );
			$matchas ='<a href='. $link4 . '>';
			$matchas .=HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/import.png','assign');
			$matchas .='</a>';
			echo $matchas;
	}
	else
    {
                
			echo $checked;
            $marker = $j - 1;
			$append = 'onchange="document.getElementById(\'cb'.$marker.'\').checked=true" ';
			if ($this->node[$j-1]->team_id == 0)
			{
				$append .= ' style="background-color:#bbffff"';
			}
			echo HTMLHelper::_(	'select.genericlist',$this->lists['team'],'team_id'.$this->node[$j-1]->id,
			'class="inputbox select-hometeam" size="1"'.$append,'value','text',$this->node[$j-1]->team_id);
	}
}
else
{
	if ($this->node[$j-1]->is_leaf == 1)
	{
		echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/settings.png','leaf');;
	}
	else
	{
		echo $checked;
	}
}
// node end_________________________________________________________________________________________________
				}
				elseif(( $k == (2+($w*2)) ) && ( $j % (4*(pow(2,$w))) == (pow(2,$w)) ))
				{
					echo "$dl";
				}
				elseif(( $k == (2+($w*2)) ) && ( $j % (4*(pow(2,$w))) == (2*(pow(2,$w))) ))
				{
					if($this->node[$j-1]->is_leaf == 1)
					{
						;
					}
					else
					{
						echo "$cl";
					}
				}
				elseif(( $k == (2+($w*2)) ) && ( $j % (4*(pow(2,$w))) == (3*(pow(2,$w))) ))
				{
					echo "$ul";
				}
				elseif(( $k == (2+($w*2)) ) && ( ( $j % (4*(pow(2,$w))) > (pow(2,$w)) ) && ( $j % (4*(pow(2,$w))) < (3*(pow(2,$w))) ) ))
				{
					echo "$p";
				}
				else
				{
					;
				}
			}
			//}
			echo '</td>';
		}
		}
		echo '</tr>';
	}
	}
?>
</table>

</div>
	

