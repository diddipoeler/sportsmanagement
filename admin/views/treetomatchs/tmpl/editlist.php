<?php 

defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_( 'behavior.tooltip' );

// Set toolbar items for the page
//$edit = JFactory::getApplication()->input->getVar('edit',true);

//JToolbarHelper::title( JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETOMATCH_ASSIGN' ) );
//
//JLToolBarHelper::save( 'treetomatch.save_matcheslist' );
//
//// for existing items the button is renamed `close` and the apply button is showed
////JLToolBarHelper::cancel( 'cancel', 'COM_JOOMLEAGUE_GLOBAL_CLOSE' );
//JToolbarHelper::back('Back','index.php?option=com_joomleague&view=treetonodes&task=treetonode.display');

//JToolbarHelper::help( 'screen.joomleague', true );
//$uri = JFactory::getURI();

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<!-- import the functions to move the events between selection lists  -->
<?php
//$version = urlencode(sportsmanagementHelper::getVersion());
//echo JHtml::script( 'administrator/components/com_joomleague/assets/js/sm_functions.js');
?>
<script>
Joomla.submitbutton = function(task)    {
        
        //alert(task);
jQuery('select#node_matcheslist > option').prop('selected', 'selected');        
//        if (task == "simplelistitem.cancel"){
//           Joomla.submitform(task, thisForm );
//        }
//        else if (document.formvalidator.isValid(thisForm))
//        {
//            //add any additional validation here
//            if(jQuery('aa').val() === 'aaa'){
//                Joomla.submitform(task, thisForm );
//            }
//        }
Joomla.submitform(task);
 
            return true;
    };

//	function submitbutton(pressbutton)
//	{
//		var form = jQuery('adminForm');
//        
//        alert(form);
//        
//		if (pressbutton == 'cancel')
//		{
//			submitform( pressbutton );
//			return;
//		}
//		var mylist = document.getElementById('node_matcheslist');
//        
//        jQuery('select#node_matcheslist > option').prop('selected', 'selected');
//        
//		for(var i=0; i<mylist.length; i++)
//		{
//			  mylist[i].selected = true;
//		}
//		submitform( pressbutton );
//	}
</script>

<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<div class="col50">

<?php
if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}


echo $this->loadTemplate('data');
?>
	
<div class="clr"></div>

<input type="hidden" name="matcheschanges_check"	value="0"	id="matcheschanges_check" />
<input type="hidden" name="option"				value="com_sportsmanagement" />
<input type="hidden" name="cid[]"				value="<?php echo $this->nodews->id; ?>" />
<input type="hidden" name="task"				value="treetomatch.save_matcheslist" />
</div>
</form>