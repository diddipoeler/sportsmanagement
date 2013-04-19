<?php
/**
* @version    $Id: default.php 4905 2010-01-30 08:51:33Z and_one $ 
* @package    JoomlaTracks
* @copyright  Copyright (C) 2008 Julien Vonthron. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* Joomla Tracks is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
// no direct access

defined('_JEXEC') or die('Restricted access');
?>
<form action="<?php JRequest::getURI(); ?>" method="post" id="adminForm">
<div class="imghead">

	<?php echo JText::_( 'JSEARCH_FILTER_LABEL' ).' '; ?>
	<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" onChange="document.getElementById('adminForm').submit();" />
	<button onclick="this.form.submit();"><?php echo JText::_( 'JSEARCH_FILTER_SUBMIT' ); ?></button>
	<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'JSEARCH_FILTER_CLEAR' ); ?></button>

</div>

<div class="imglist">

		<?php
		for ($i = 0, $n = count($this->images); $i < $n; $i++) :
			$this->setImage($i);
			echo $this->loadTemplate('image');
		endfor;
		?>
</div>

<div class="clr"></div>

<div class="pnav"><?php echo $this->pageNav->getListFooter(); ?></div>

	<input type="hidden" name="option" value="com_sportsmanagement" />
	<input type="hidden" name="view" value="imagehandler" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="imagehandler.select" />
	<input type="hidden" name="folder" value="<?php echo $this->folder; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>