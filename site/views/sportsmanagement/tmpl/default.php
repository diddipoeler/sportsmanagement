<?php

defined('_JEXEC') or die('Restricted access');
?>
<h1><?php echo $this->item->greeting.(($this->item->category and $this->item->params->get('show_category')) ? (' ('.$this->item->category.')') : ''); ?></h1>
