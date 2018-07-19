<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_show_tabs.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage globalviews
 */ 

defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<div class="row-fluid" id="show_tabs">
<?php
if( version_compare(JSM_JVERSION,'4','eq') ) 
{
$idxTab = 0;    
echo JHtml::_('bootstrap.startTabSet', 'myTab4', array('active' => 'name'));
foreach ($this->output as $key => $templ) 
{
$template = $templ;
$text = $key;
$active = ($idxTab==0) ? JHtml::_('bootstrap.startTabSet', 'myTab4', array('active' => $text)) : ''; 

echo JHtml::_('bootstrap.addTab', 'myTab4', $text, JText::_($text));
?>
<div class="container">
<div class="row">
<?PHP
echo $this->loadTemplate($template);
?>
</div>
</div>
<?PHP
echo JHtml::_('bootstrap.endTab');
$idxTab++;
}    
echo JHtml::_('bootstrap.endTabSet');    
}
elseif(version_compare(JSM_JVERSION,'3','eq')) 
{
// Joomla! 3.0 code here
$idxTab = 0;    
echo JHtml::_('bootstrap.startTabSet', 'myTab4', array('active' => 'name'));
foreach ($this->output as $key => $templ) 
{
$template = $templ;
$text = $key;
$active = ($idxTab==0) ? JHtml::_('bootstrap.startTabSet', 'myTab4', array('active' => $text)) : ''; 

echo JHtml::_('bootstrap.addTab', 'myTab4', $text, JText::_($text));
?>
<div class="container">
<div class="row">
<?PHP
echo $this->loadTemplate($template);
?>
</div>
</div>
<?PHP
echo JHtml::_('bootstrap.endTab');
$idxTab++;
}    
echo JHtml::_('bootstrap.endTabSet');  



   
        
}
elseif(version_compare(JSM_JVERSION,'2','eq'))
{
// Joomla! 2.5 code here
$view = JFactory::getApplication()->input->getCmd('view');
?>

<div class="panel with-nav-tabs panel-default">
<div class="panel-heading">

<!-- Tabs-Navs -->
<ul class="nav nav-tabs" >
<?PHP
$count = 0;

foreach ($this->output as $key => $templ)
{
$active = ($count==0) ? 'active' : '';   

switch ($view)
{
case 'player':
$template = $templ['template'];
$text = $templ['text'];   
break;
default:
$template = $templ;
$text = $key;
break;
}
?>  
<li class="<?PHP echo $active; ?>"><a href="#<?PHP echo $template; ?>" data-toggle="tab"><?PHP echo JText::_($text); ?></a></li>
<?PHP
$count++;
}
?>
</ul>
</div>
<!-- Tab-Inhalte -->
<div class="panel-body">
<div class="tab-content">
<?PHP
$count = 0;

foreach ($this->output as $key => $templ)
{
$active = ($count==0) ? 'in active' : '';
switch ($view)
{
case 'player':
$template = $templ['template'];
$text = $templ['text'];      
break;
default:
$template = $templ;
$text = $key;
break;
}
?>
<div class="tab-pane fade <?PHP echo $active; ?>" id="<?PHP echo $template; ?>">
<?PHP   
switch ($template)
{
case 'previousx':
$this->currentteam = $this->match->projectteam1_id;
echo $this->loadTemplate($template);
$this->currentteam = $this->match->projectteam2_id;
echo $this->loadTemplate($template);
break;
default:
echo $this->loadTemplate($template);
break;
}  
?>
</div>
<?PHP
$count++;
}
?>
</div>
</div>
</div>
<?PHP
} 
elseif(version_compare(JVERSION,'1.7.0','ge')) 
{
// Joomla! 1.7 code here
} 
elseif(version_compare(JVERSION,'1.6.0','ge')) 
{
// Joomla! 1.6 code here
} 
else 
{
// Joomla! 1.5 code here
}
?>
</div>