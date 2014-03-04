<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access');
//echo '<br /><pre>~' . print_r($this->model->predictionProject,true) . '~</pre><br />';
?>
<?php
if (!empty($this->model->_projectRoundsCount))
{
	?><br /><table width='96%' align='center' cellpadding='0' cellspacing='0' border='0'><tr><td><div class='pagenav'><?php
								
				$pageNavigation  = "<div class='pagenav'>";
				$pageNavigation .= JoomleaguePagination::pagenav($this->model->getPredictionProject($this->model->predictionProject->project_id));
				$pageNavigation .= "</div>";
				echo $pageNavigation;
			
		
	?></div></td></tr></table><?php
}
?>