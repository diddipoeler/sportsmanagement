<?php 

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_( 'behavior.tooltip' );

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<!-- import the functions to move the events between selection lists  -->
<?php
//$version = urlencode(sportsmanagementHelper::getVersion());

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