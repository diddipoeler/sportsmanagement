<?php

/**
 * Rquotes default layout file
 * This file includes quotation marks before and after quote
 * @package    Joomla.Rquotes
 * @subpackage Modules
 * @link www.mytidbits.us
 * @license		GNU/GPL-2
 */

 //no direct access
 defined('_JEXEC') or die('Restricted access');
//$css = JURI::base().'modules/mod_rquotes/assets/rquote.css';
// $document =& JFactory::getDocument();
// $document->addStyleSheet($css); 


 foreach ($list as $rquote)
?>
<div class ="mod_rquote_style">
<?php	 modRquotesHelper::renderRquote($rquote, $params);?>
</div>