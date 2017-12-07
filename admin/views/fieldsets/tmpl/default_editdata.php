<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/ 
defined('_JEXEC') or die('Restricted access');

// No direct access
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
$view = $this->jinput->getCmd('view', 'cpanel');


/**
 * welche joomla version ?
 */
if( version_compare(JSM_JVERSION,'4','eq') ) 
{

echo JText::_(__METHOD__.' '.__LINE__.' fieldsets<br><pre>'.print_r($fieldsets,true).'</pre>');
    
?>
<div>
<?php
echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));

foreach ($fieldsets as $fieldset) 
{
echo JText::_(__METHOD__.' '.__LINE__.' fieldset<br><pre>'.print_r($fieldset,true).'</pre>');
    
echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset->name, JText::_($fieldset->label, true));

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
                $picture = sportsmanagementHelper::getPicturePlayground($field->value);
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



echo JHtml::_('bootstrap.endTab');
}

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
<?php echo JHtml::_('form.token'); ?>
</div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
