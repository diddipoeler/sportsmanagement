<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_editdata.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fieldsets
 */

defined('_JEXEC') or die('Restricted access');

/**
 * welche joomla version ?
 */
if( version_compare(JSM_JVERSION,'4','eq') ) 
{
// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');    
JHtml::_('jquery.framework');
}    
// No direct access
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
try{
// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
}
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}
$view = $this->jinput->getCmd('view', 'cpanel');


/**
 * welche joomla version ?
 */
if( version_compare(JSM_JVERSION,'4','eq') ) 
{
?>
<div>
<?php
echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));

foreach ($fieldsets as $fieldset) 
{
//echo JText::_(__METHOD__.' '.__LINE__.' fieldset<br><pre>'.print_r($fieldset,true).'</pre>');
    
echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset->name, JText::_($fieldset->label, true));
?>
<div class="row">
<div class="col-md-12">
<?PHP
switch ($fieldset->name)
{
    case 'details':
    ?>
    <div class="row-fluid">
					<div class="span6">
    <?PHP
    foreach( $this->form->getFieldset($fieldset->name) as $field ) 
    {
        ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						
                        <?PHP
                        $suchmuster = array ("jform[","]","request[");
                $ersetzen = array ('', '', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
                        switch ($var_onlinehelp)
                {
                    case 'ids':
                    break;
                    default:
                ?>
<a href="#<?php echo $var_onlinehelp;?>" title="<?php echo $var_onlinehelp;?>" class="" data-toggle="modal">
<?php
echo JHtml::_(	'image','media/com_sportsmanagement/jl_images/help.png',
JText::_('COM_SPORTSMANAGEMENT_HELP_LINK'),'title= "' .
JText::_('COM_SPORTSMANAGEMENT_HELP_LINK').'"');
	
echo JHtml::_('bootstrap.renderModal',
	$var_onlinehelp,
	array(
	'title' => JText::_('COM_SPORTSMANAGEMENT_HELP_LINK'),
	'url' => COM_SPORTSMANAGEMENT_HELP_SERVER.'SM-Backend-Felder:'.$this->jinput->getVar( "view").'-'.$var_onlinehelp,
    'width' => COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH,
    'height' => COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT
	)
	);	
?>									
</a>                
                
                <?PHP
                if ( $field->name == 'jform[country]' )
                {
                echo JSMCountries::getCountryFlag($field->value);    
                }
                
                if ( $field->name == 'jform[standard_playground]' )
                {
                $picture = sportsmanagementHelper::getPicturePlayground($field->value);
?>
<a href="<?php echo JURI::root().$picture;?>" title="<?php echo 'Playground';?>" class="modal">
<img src="<?php echo JURI::root().$picture;?>" alt="<?php echo 'Playground';?>" width="50" />
</a>
<?PHP                   
                }
                
                if ( $field->name == 'jform[website]' )
                {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=t&url='.$field->value.'">'; 
                }
                if ( $field->name == 'jform[twitter]' )
                {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=t&url='.$field->value.'">'; 
                }
                if ( $field->name == 'jform[facebook]' )
                {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=t&url='.$field->value.'">'; 
                }
                break;
                }
                        ?>
                        </div>
					</div>
				<?php

    }
    ?>
    </div>
             <div class="span6">
						<div class="control-group">
							<style type="text/css">.map_canvas{width:100%;height:400px;}</style>
							<div id="map_canvas"  class="map_canvas"></div>
						</div>
					</div>
            </div>
    <?PHP
    break;
    default:
    $this->fieldset = $fieldset->name;
    echo $this->loadTemplate('fieldsets_4');
    break;
}    
?>
</div>
</div>
<?PHP
echo JHtml::_('bootstrap.endTab');
}

/**
 * bei den positionen müssen noch zusätzliche templates 
 * eingebunden werden
 */

echo JHtml::_('bootstrap.endTabSet'); 
?>
</div>
<?php
}	
elseif( version_compare(JSM_JVERSION,'3','eq') ) 
{
?> 
<div class="form-horizontal">
<fieldset>
<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

<?PHP    
foreach ($fieldsets as $fieldset) 
{
echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset->name, JText::_($fieldset->label, true));    

switch ($fieldset->name)
{
    case 'details':
    ?>
    <div class="row-fluid">
		<!--	<div class="span9"> -->
		<!--		<div class="row-fluid form-horizontal-desktop"> -->
					<div class="span6">
    <?PHP
    foreach( $this->form->getFieldset($fieldset->name) as $field ) 
    {
        ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						
                        <?PHP
                        $suchmuster = array ("jform[","]","request[");
                $ersetzen = array ('', '', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
                        //echo 'field_name -> '.$field->name;
//                        echo 'var_onlinehelp -> '.$var_onlinehelp;
                        switch ($var_onlinehelp)
                {
                    case 'id':
                    break;
                    default:
                ?>
                <a	rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
									href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER.'SM-Backend-Felder:'.$this->jinput->getVar( "view").'-'.$var_onlinehelp; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','media/com_sportsmanagement/jl_images/help.png',
													JText::_('COM_SPORTSMANAGEMENT_HELP_LINK'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_HELP_LINK').'"');
									?>
								</a>
                
                <?PHP
                if ( $field->name == 'jform[country]' )
                {
                echo JSMCountries::getCountryFlag($field->value);    
                }
                
                if ( $field->name == 'jform[standard_playground]' )
                {
                //echo sportsmanagementHelper::getPicturePlayground($field->value);
                $picture = sportsmanagementHelper::getPicturePlayground($field->value);
                //echo $picture;
                //echo JHtml::image($picture, 'Playground', array('title' => 'Playground','width' => '50' )); 
                //echo JHtml::_('image', $picture, 'Playground',array('title' => 'Playground','width' => '50' )); 
?>
<a href="<?php echo JURI::root().$picture;?>" title="<?php echo 'Playground';?>" class="modal">
<img src="<?php echo JURI::root().$picture;?>" alt="<?php echo 'Playground';?>" width="50" />
</a>
<?PHP                   
                }
                
                if ( $field->name == 'jform[website]' )
                {
                //echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                echo '<img style="" src="http://api.thumbsniper.com/api_free.php?size=13&effect=1&url='.$field->value.'">'; 
                }
                if ( $field->name == 'jform[twitter]' )
                {
                //echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                echo '<img style="" src="http://api.thumbsniper.com/api_free.php?size=13&effect=1&url='.$field->value.'">'; 
                }
                if ( $field->name == 'jform[facebook]' )
                {
                //echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                echo '<img style="" src="http://api.thumbsniper.com/api_free.php?size=13&effect=1&url='.$field->value.'">'; 
                }
                break;
                }
                        ?>
                        </div>
					</div>
				<?php

    }
    ?>
    </div>
		<!--		</div> -->
		<!--	</div> -->
             <div class="span6">
						<div class="control-group">
							<style type="text/css">.map_canvas{width:100%;height:400px;}</style>
							<div id="map_canvas"  class="map_canvas"></div>
						</div>
					</div>
            </div>
    <?PHP
    break;
    default:
    $this->fieldset = $fieldset->name;
    echo $this->loadTemplate('fieldsets_3');
    break;
}    
echo JHtml::_('bootstrap.endTab');    
}    

/**
 * bei den positionen müssen noch zusätzliche templates 
 * eingebunden werden
 */

switch ($view)
{
    case 'position':
    echo JHtml::_('bootstrap.addTab', 'myTab', 'COM_SPORTSMANAGEMENT_TABS_EVENTS', JText::_('COM_SPORTSMANAGEMENT_TABS_EVENTS', true));
    echo $this->loadTemplate('position_events');
    echo JHtml::_('bootstrap.endTab');
    echo JHtml::_('bootstrap.addTab', 'myTab', 'COM_SPORTSMANAGEMENT_TABS_STATISTICS', JText::_('COM_SPORTSMANAGEMENT_TABS_STATISTICS', true));
    echo $this->loadTemplate('position_statistics');
    echo JHtml::_('bootstrap.endTab');  
    break;
    
} 
 
 
?>    
	
<?php echo JHtml::_('bootstrap.endTabSet'); ?>
</fieldset>
</div> 

<?PHP
}
else
{
?>                

<div class="width-40 fltrt">
	
<div class="control-group">
<style type="text/css">.map_canvas{width:100%;height:400px;}</style>
<div id="map_canvas"  class="map_canvas"></div>
</div>

		<?php
		echo JHtml::_('sliders.start');
		foreach ($fieldsets as $fieldset) :
        
        //echo 'fieldset name'.$fieldset->name.'<br>';
        
			if ($fieldset->name == 'details') :
				continue;
			endif;
			echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
		if (isset($fieldset->description) && !empty($fieldset->description)) :
				echo '<p class="tab-description">'.JText::_($fieldset->description).'</p>';
			endif;
		//echo $this->loadTemplate($fieldset->name);
        $this->fieldset = $fieldset->name;
        echo $this->loadTemplate('fieldsets');
		endforeach; ?>
		<?php echo JHtml::_('sliders.end'); ?>

	
	</div>
<?PHP
}
?>
    
<div class="clr"></div>
<div>
<input type="hidden" name="task" value="<?php echo $view; ?>.edit" />
<?php 
if ( $view == 'teamperson' )
{
?>    
<input type="hidden" name="persontype" value="<?php echo $this->_persontype; ?>" />
<input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>" />
<input type="hidden" name="pid" value="<?php echo $this->project_id; ?>" />	
<?php    
}
	
if ( $view == 'treetonode' )
{
?>    
<input type="hidden" name="project_id" value="<?php echo $this->projectws->id; ?>" />
<input type="hidden" name="pid" value="<?php echo $this->projectws->id; ?>" />
<input type="hidden" name="tid" value="<?php echo $this->item->treeto_id; ?>" />
<?php    
}

	
echo JHtml::_('form.token'); 
?>
</div>
</form>
<div>
<?PHP
echo $this->loadTemplate('footer');
?>   
</div>
