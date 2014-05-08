<?php
defined('_JEXEC') or die('Restricted access');


if ( $this->show_debug_info )
{
echo 'this->config<br /><pre>~' . print_r($this->config,true) . '~</pre><br />';    
echo 'this->tableconfig<br /><pre>~' . print_r($this->tableconfig,true) . '~</pre><br />';
}   
 
// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array(
    'projectheading',
    'backbutton',
    'footer');
JoomleagueHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div class="joomleague">
	<?php

//echo $this->loadTemplate('projectheading');
//echo $this->loadTemplate('ranking');

?>
</div>