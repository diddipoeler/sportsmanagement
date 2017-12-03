<?php 

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');

//$istree=$this->treetows->tree_i;
//$isleafed=$this->treetows->leafed;

//load navigation menu
//$this->addTemplatePath(JPATH_COMPONENT.DS.'views'.DS.'joomleague');

// Set toolbar items for the page
//JToolbarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_TREETONODES_TITLE'));
	//if($isleafed==1)
//	{
//	JLToolBarHelper::apply('treetonode.saveshort', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_SAVE_APPLY' ), false);
//	JLToolBarHelper::custom( 'treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_DELETE_ALL' ), false );
//	}
//	elseif($isleafed)
//	{
//	JLToolBarHelper::apply('treetonode.saveshortleaf',JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_TEST_SHOW' ), false);
//	if($isleafed==3)
//	{
//		JLToolBarHelper::apply('treetonode.savefinishleaf', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_SAVE_LEAF' ), false);
//	}
//	JLToolBarHelper::custom( 'treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_DELETE' ), false );
//	}
//JToolbarHelper::help('screen.joomleague',true);
?>


	<div id="editcell">
	
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_LEGEND','<i>'.$this->projectws->name.'</i>'); ?></legend>
<?php
//$style = $this->style;
//$path=$this->path;

$attribs['width'] = '16px';
$attribs['height'] = '18px';
$dl = JHtml::_('image', $this->path.'treedl.gif','', $attribs);

$ul = JHtml::_('image', $this->path.'treeul.gif','', $attribs);
$cl = JHtml::_('image', $this->path.'treecl.gif','', $attribs);
$dr = JHtml::_('image', $this->path.'treedr.gif','', $attribs);
$ur = JHtml::_('image', $this->path.'treeur.gif','', $attribs);
$cr = JHtml::_('image', $this->path.'treecr.gif','', $attribs);
$p = JHtml::_('image', $this->path.'treep.gif','', $attribs);
$h = JHtml::_('image', $this->path.'treeh.gif','', $attribs);

$i = $this->treetows->tree_i;		//depth
$r = 2*(pow(2,$i)); 			//rows
$c = 2*$i+1;        			//columns
$col_hide = $c-2*($this->treetows->hide);	//tournament with multiple winners
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
					echo "$this->style";
				}
			}
			echo ' >';
			
			for($w=0;$w<=$i;$w++)
			{
				if(( $k == (1+($w*2)) ) && ( $j % (2*(pow(2,$w))) == (pow(2,$w))))
				{
// node __________________________________________________________________________________________________
$checked = JHtml::_('grid.checkedout',$this->node[$j-1], $j-1);
if( $this->treetows->leafed == 1 )
{
	echo $this->node[$j-1]->node;
	if($this->node[$j-1]->team_id)
	{
			$link = JRoute::_( 'index.php?option=com_sportsmanagement&task=treetonode.edit&id='.$this->node[$j-1]->id.'&tid='.$this->jinput->get('tid').'&pid='.$this->jinput->get('pid') );
			$ednode ='<a href='. $link . '>';
			$ednode .=JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/edit.png','edit');
			$ednode .='</a>';
			echo $ednode;
			echo $this->node[$j-1]->team_name;
//			$link3 = JRoute::_( 'index.php?option=com_sportsmanagement&view=treetomatchs&task=treetomatch.display&nid=' . $this->node[$j-1]->id . '&tid='.$this->treetows->id .'&pid='.$this->jinput->get('pid'));
			$link3 = JRoute::_( 'index.php?option=com_sportsmanagement&view=treetomatchs&layout=default&nid=' . $this->node[$j-1]->id . '&tid='.$this->treetows->id .'&pid='.$this->jinput->get('pid'));
            $match ='<a href='. $link3 . '>';
			$match .=JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/matches.png','edit');
			$match .='</a>';
			echo $match;
			$link4 = JRoute::_( 'index.php?option=com_sportsmanagement&view=treetomatchs&layout=editlist&nid=' . $this->node[$j-1]->id.'&tid='.$this->jinput->get('tid').'&pid='.$this->jinput->get('pid') );
			$matchas ='<a href='. $link4 . '>';
			$matchas .=JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/import.png','assign');
			$matchas .='</a>';
			echo $matchas;
	}
	else
    {
//echo JHtml::_(	'select.genericlist',
//		$this->league,
//		'league'.$row->id,
//		$inputappend.'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
//		$i.'\').checked=true"'.$append,
//		'id','name',$row->league_id);
                
			echo $checked;
            $marker = $j - 1;
			$append = 'onchange="document.getElementById(\'cb'.$marker.'\').checked=true" ';
			if ($this->node[$j-1]->team_id == 0)
			{
				$append .= ' style="background-color:#bbffff"';
			}
			echo JHtml::_(	'select.genericlist',$this->lists['team'],'team_id'.$this->node[$j-1]->id,
			'class="inputbox select-hometeam" size="1"'.$append,'value','text',$this->node[$j-1]->team_id);
	}
}
else
{
	if ($this->node[$j-1]->is_leaf == 1)
	{
		echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/settings.png','leaf');;
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
	

