<?php 
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$mainframe = JFactory::getApplication();
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if ( $this->project_art_id != 3 )
{
//Ordering allowed ?
$ordering=($this->lists['order'] == 't.name');
echo $this->loadTemplate('teams');    

}
else
{
//Ordering allowed ?
$ordering=($this->lists['order'] == 't.lastname');    
echo $this->loadTemplate('persons');    
}

echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   