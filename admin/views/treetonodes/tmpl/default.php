<?php defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');

$istree=$this->treetows->tree_i;
$isleafed=$this->treetows->leafed;

//load navigation menu
$this->addTemplatePath(JPATH_COMPONENT.DS.'views'.DS.'joomleague');

// Set toolbar items for the page
JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_TREETONODES_TITLE'));
	if($isleafed==1)
	{
	JLToolBarHelper::apply('treetonode.saveshort', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_SAVE_APPLY' ), false);
	JLToolBarHelper::custom( 'treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_DELETE_ALL' ), false );
	}
	elseif($isleafed)
	{
	JLToolBarHelper::apply('treetonode.saveshortleaf',JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_TEST_SHOW' ), false);
	if($isleafed==3)
	{
		JLToolBarHelper::apply('treetonode.savefinishleaf', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_SAVE_LEAF' ), false);
	}
	JLToolBarHelper::custom( 'treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_DELETE' ), false );
	}
JToolBarHelper::help('screen.joomleague',true);
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	<div id="editcell">
		<fieldset class="adminform">
			<legend><?php echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_TREETONODES_LEGEND','<i>'.$this->projectws->name.'</i>'); ?></legend>
<?php
$style=$this->style;
$path=$this->path;

$dl=JHtml::_('image', $path.'treedl.gif', '^|', 'width="16px"', 'height="18px"');
$ul=JHtml::_('image', $path.'treeul.gif', '_|', 'width="16px"', 'height="18px"');
$cl=JHtml::_('image', $path.'treecl.gif', '|-', 'width="16px"', 'height="18px"');
$dr=JHtml::_('image', $path.'treedr.gif', '|^', 'width="16px"', 'height="18px"');
$ur=JHtml::_('image', $path.'treeur.gif', '|_', 'width="16px"', 'height="18px"');
$cr=JHtml::_('image', $path.'treecr.gif', '-|', 'width="16px"', 'height="18px"');
$p=JHtml::_('image', $path.'treep.gif', '|', 'width="16px"', 'height="18px"');
$h=JHtml::_('image', $path.'treeh.gif', '-', 'width="16px"', 'height="18px"');

$i=$this->treetows->tree_i;		//depth
$r=2*(pow(2,$i)); 			//rows
$c=2*$i+1;        			//columns
$col_hide=$c-2*($this->treetows->hide);	//tournament with multiple winners
echo '<table>';

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
					echo "$style";
				}
			}
			echo ' >';
			
			for($w=0;$w<=$i;$w++)
			{
				if(( $k == (1+($w*2)) ) && ( $j % (2*(pow(2,$w))) == (pow(2,$w))))
				{
// node __________________________________________________________________________________________________
$checked=JHtml::_('grid.checkedout',$this->node[$j-1], $j-1);
if($isleafed==1)
{
	echo $this->node[$j-1]->node;
	if($this->node[$j-1]->team_id)
	{
			$link = JRoute::_( 'index.php?option=com_joomleague&task=treetonode.edit&cid[]=' . $this->node[$j-1]->id );
			$ednode='<a href='. $link . '>';
			$ednode.=JHtml::_('image', 'administrator/components/com_joomleague/assets/images/edit.png','edit');
			$ednode.='</a>';
			echo $ednode;
			echo $this->node[$j-1]->team_name;
			$link3 = JRoute::_( 'index.php?option=com_joomleague&task=treetomatch.display&view=treetomatchs&nid[]=' . $this->node[$j-1]->id . '&tid[]='.$this->treetows->id );
			$match='<a href='. $link3 . '>';
			$match.=JHtml::_('image', 'administrator/components/com_joomleague/assets/images/matches.png','edit');
			$match.='</a>';
			echo $match;
			$link4 = JRoute::_( 'index.php?option=com_joomleague&task=treetomatch.editlist&nid[]=' . $this->node[$j-1]->id );
			$matchas='<a href='. $link4 . '>';
			$matchas.=JHtml::_('image', 'administrator/components/com_joomleague/assets/images/import.png','assign');
			$matchas.='</a>';
			echo $matchas;
	}
	else{
			echo $checked;
			$append='';
			if ($this->node[$j-1]->team_id == 0)
			{
				$append=' style="background-color:#bbffff"';
			}
			echo JHtml::_(	'select.genericlist',$this->lists['team'],'team_id'.$this->node[$j-1]->id,
			'class="inputbox select-hometeam" size="1"'.$append,'value','text',$this->node[$j-1]->team_id);
	}
}
else
{
	if ($this->node[$j-1]->is_leaf == 1)
	{
		echo JHtml::_('image', 'administrator/components/com_joomleague/assets/images/settings.png','leaf');;
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
		</fieldset>
	</div>
	<input type="hidden" name="project_id" value="<?php echo $this->projectws->id; ?>" />
	<input type="hidden" name="roundcode" value="<?php echo $this->roundcode; ?>" />
	<input type="hidden" name="tree_i" value="<?php echo $this->treetows->tree_i; ?>" />
	<input type="hidden" name="treeto_id" value="<?php echo $this->treetows->id; ?>" />
	<input type="hidden" name="global_fake" value="<?php echo $this->treetows->global_fake; ?>" />
	<input type="hidden" name="global_known" value="<?php echo $this->treetows->global_known; ?>" />
	<input type="hidden" name="global_matchday" value="<?php echo $this->treetows->global_matchday; ?>" />
	<input type="hidden" name="global_bestof" value="<?php echo $this->treetows->global_bestof; ?>" />
	<input type="hidden" name="task" value="treetonode.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>
