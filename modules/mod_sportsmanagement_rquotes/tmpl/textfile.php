<?php 
$css = JURI::base().'modules/mod_sportsmanagement_rquotes/assets/rquote.css';
 $document = JFactory::getDocument();
 $document->addStyleSheet($css); 
 
echo '<span class="mod_rquote_quote_text_file">'. $rows[$num].'</span>';
 
  
//echo( $rows[$num]);

?>
