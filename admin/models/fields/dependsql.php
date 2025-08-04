<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       dependsql.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'ajax.php';
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';

jimport('joomla.form.helper');

if (version_compare(JVERSION, '4.0.0', 'ge'))
{
    HTMLHelper::_('jquery.framework');
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.13.1/alertify.min.js"></script>


<script>
$(document).ready(function() {
        var $progControl = $(".progControlSelect2").select2({
             //   placeholder: "Neue Mannschaft",
            //maximumSelectionLength: 2
        });
        $(".AGSPlan").on("click", function() {
                $progControl.val(["aa", "ac", "ae"]).trigger("change");
        });
        $(".TradPlan").on("click", function() {
                $progControl.val(["aa", "ab", "af"]).trigger("change");
        });
        $(".RegStudent").on("click", function() {
                $progControl.val(["ag", "ah", "ai", "aj"]).trigger("change");
        });
        $(".Other").on("click", function() {
                $progControl.val(["ak"]).trigger("change");
        });
        $(".clearSelect2").on("click", function() {
                $progControl.val(null).trigger("change");
        });
        $(".Submit").on("click", function(event) {
                alertify.alert(
                        '<strong class="purple">Silly guy</strong>, This is a fake button.'
                );
        });

        alertify.defaults = {
                glossary: {
                        title: "Nothing to see here...",
                        ok: "I AM a silly guy"
                },
                theme: {
                        input: "ajs-input",
                        ok: "ajs-ok",
                        cancel: "ajs-cancel"
                }
        };

        //Disable select open on option remove
        $("select").on("select2:unselect", function (evt) {
                if (!evt.params.originalEvent) {
                        return;
                }
                evt.params.originalEvent.stopPropagation();
        });


});
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- <link rel='stylesheet' https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.13.1/css/alertify.css'> -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/default.min.css'> -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/semantic.min.css'> -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/bootstrap.min.css'>    -->


<style>
.container {
    //font-size: 20px;
    //width: 1000px;
}
.title {
    font-size: 16px;
    background: black;
    width: 100%;
    margin: 0;
    padding: 8px;
    color: white;
    text-align: center;
}
.content {
    padding-top: 20px;
}
.full {
    text-align: center;
}
.bg {
    padding: 15px;
    background: #bfe6e4;
}
input[type="button"] {
    display: block;
    width: 200px;
    margin-bottom: 4px;
    cursor: pointer;
}
.btn-success {
    color: #fff;
    background-color: #246b24;
    border-color: #1e5f1e;
}

.btn.btn-default.btn-sm.Other {
    border: 1px solid #292b2c;
    border: 1px solid rgba(0, 0, 0, 0.17);
    background: #464646;
    color: white;
}
.btn.btn-default.btn-sm.Other:hover {
    background: #333333;
}
input.clearSelect2, input.Submit {
    margin-top: 15px;
    width: 99px;
    display: inline-block;
}
.clearSelect2 {
    background: red;
}
.progControlSelect2 {
    width: 350px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    border-radius: 0;
}
.purple {
    color: #980798;
}
.select2-selection__choice__remove {
    color: #5a5a5a !important;
    position: relative;
    top: -1px;
    font-size: 13px;
}
.ajs-button {
    cursor: pointer;
}
@media (min-width: 576px)
_grid.scss:24
.container {
    width: 100%;
    max-width: 100%;
}
</style>




<?php
/**
 * Renders a Dynamic SQL field
 *
 * in the xml field, the following fields must be defined:
 * - depends: list of fields name this field depends on, separated by comma (e.g: "p, tid")
 * - task: the task used to return the query, using defined depends field names as parameters for query (=> 'index.php?option=com_sportsmanagement&controller=ajax&task=<task>&p=1&tid=34')
 *
 * @package             sportsmanagement
 * @subpackageParameter
 * @since1              .5
 */
class JFormFieldDependSQL extends FormField
{
    /**
     * field name
     *
     * @access protected
     * @var    string
     */
    protected $type = 'dependsql';

    /**
     * FormFieldDependSQL::getInput()
     *
     * @return
     */
    protected function getInput()
    {
        $app = Factory::getApplication();
        $jinput = $app->input;
        $view   = $jinput->getCmd('view');
        $option = $jinput->getCmd('option');
      $select2 = "progControlSelect2";

        //Factory::getApplication()->enqueueMessage('<pre>'.print_r($view,true)      .'</pre>', 'error');

        $lang = Factory::getLanguage();
        $lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR);

        $attribs   = '';
        $norequest = 0;
        $required  = $this->element['required'] == "true" ? true : false;
        $key       = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
        $val       = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);

        $key_clubid       = ($this->element['club_id'] ? $this->element['club_id'] : 'value');
        $val_clubid       = ($this->element['club_ids'] ? $this->element['club_ids'] : $this->name);

        $ajaxtask  = $this->element['task'];
        $depends   = $this->element['depends'];
        $depends2   = $this->element['depends2'];
        $slug      = $this->element['slug'] == "true" ? true : false;
        $query     = (string) $this->element['query'];
        $norequest = $this->element['norequest'];

        $project_id = $this->form->getValue('id');

        $attribs   .= ' class="form-select select2-container progControlSelect2"';
        if ( $v = $this->element['size'] )
        {
            $attribs .= ' size="' . $v . '"';
        }

        if ($v = $this->element['multiple'])
        {
            $attribs .= ' multiple="' . $v . '"';
        }

        switch ( $depends )
        {
            case 'search_nation':
            $attribs .= ' onchange="this.form.submit();"';
            break;
        }

        switch ($option)
        {
        case 'com_modules':
        $div = 'params';
        break;
        case 'com_sportsmanagement':
        if ($norequest)
        {
            $div = '';
        }
        else
        {
            $div = 'request';
        }
        break;
        default:
        $div = 'request';
        break;
        }

    switch ($view)
    {
    case 'predictiongame':
    $div = '';
    break;
    }

        $value     = $this->form->getValue($val, $div);
        $key_value = $this->form->getValue($key, $div);

        $value_clubid     = $this->form->getValue($val_clubid, $div);
        $key_value_clubid = $this->form->getValue($key_clubid, $div);

        $cfg_which_database = $this->form->getValue('cfg_which_database', $div);

        $ctrl = $this->name;
        $id   = $this->id;

        $options = array();
        $result  = '';

        $script = array();

        $script[] = "\n";
        $script[] = "jQuery(document).ready(function ($){";
      switch ($view)
        {
        case 'predictiongame':
        $script[] = "				$('#jform" . $div . '_' . $depends . "').change(function(){";
        $script[] = "					var value = $('#jform" . $div . '_' . $depends . "').val();";
          break;
        default:
        $script[] = "				$('#jform_" . $div . '_' . $depends . "').change(function(){";
        $script[] = "					var value = $('#jform_" . $div . '_' . $depends . "').val();";
          break;
      }

        $script[] = "if (window.console) console.log('json value -> ' + value);";

        switch ($ajaxtask)
        {
        case 'projectteamoptions':
$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&club_id=".$value_clubid."&task=ajax." . $ajaxtask . "&" . $depends . "=' + value;";
        break;
        default:
$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&task=ajax." . $ajaxtask . "&" . $depends . "=' + value;";
        break;
        }


        switch ($ajaxtask)
        {
        case 'personagegroupoptions':
        $script[] = "					var valuecountry = $('#jform_country').val();";
        $script[] = "if (window.console) console.log('json valuecountry -> ' + valuecountry);";
        $script[] = " url = url + '&country=' + valuecountry;";
        break;
        }

        $script[] = "if (window.console) console.log('json url -> ' + url);";
        $script[] = "					$.ajax({";

        switch ($view)
        {
        case 'project':
        $script[] = "	url: 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&task=ajax." . $ajaxtask . "&project=" . $project_id . "&" . $depends . "=' + value,";
        break;
        case 'club':
        $script[] = "	url: 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&task=ajax." . $ajaxtask . "&country=" . $key_value . "&" . $depends . "=' + value,";
        break;
        default:
        $script[] = "	url: url,";
        break;
        }

        $script[] = "						dataType: 'json'";
        $script[] = "					}).done(function(r) {";
        $script[] = "						$('#" . $this->id . " option').each(function() {";
        $script[] = "								jQuery('select#" . $this->id . " option').remove();";
        $script[] = "						});";
        $script[] = "";
        $script[] = "						$.each(r.data, function (i, val) {";
        $script[] = "if (window.console) console.log('json value-> ' + val.value);";
        $script[] = "if (window.console) console.log('json text-> ' + val.text);";
        $script[] = "						});";
        $script[] = "if (r.messages)";
        $script[] = "		{";
        $script[] = "			// All the enqueued messages of the app object can simple be";
        $script[] = "			// rendered by the respective helper function of Joomla!";
        $script[] = "			// They will automatically be displayed at the messages section of the template";
        $script[] = "			Joomla.renderMessages(r.messages);";
        $script[] = "		}";
        $script[] = "						$.each(r.data, function (i, val) {";
        $script[] = "							var option = $('<option>');";
        $script[] = "							option.text(val.text).val(val.value);";
        $script[] = "							$('#" . $this->id . "').append(option);";
        $script[] = "						});";
        $script[] = "						$('#" . $this->id . "').trigger('liszt:updated');";
        $script[] = "					});";
        $script[] = "				});";
        $script[] = "});";

        /** Add the script to the document head. */
        Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

        $ajaxtask = 'get' . $ajaxtask;
        switch($ajaxtask)
        {
            case 'getprojects':
            case 'getprojectdivisionsoptions':
            case 'getprojectstatsoptions':
                $result = sportsmanagementModelAjax::$ajaxtask($value, $required, $slug, $cfg_which_database);
                break;
            case 'getprojectroundoptions':
                $result = sportsmanagementModelAjax::$ajaxtask($value, $required, $slug, null, null, $cfg_which_database);
                break;
            default:
                $result = sportsmanagementModelAjax::$ajaxtask($value, $required, $slug);
                break;
        }

        if ($result)
        {
            $options = array_merge($options, $result);
        }

        return HTMLHelper::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $this->value, $this->id);
    }


}
