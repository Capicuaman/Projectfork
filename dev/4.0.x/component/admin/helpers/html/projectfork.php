<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// no direct access
defined('_JEXEC') or die;


/**
 * Abstract class for Projectfork HTML elements
 *
 */
abstract class JHtmlProjectfork
{
    /**
     * Renders an input field with a select button for choosing a project
     *
	 * @param    int     $value         The state value
     * @param    bool    $can_change
	 */
    static function filterProject($value = 0, $can_change = true)
    {
        JHtml::_('behavior.modal', 'a.modal');

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();


        // Get currently active project data
        $active_id    = (int) $app->getUserState('com_projectfork.project.active.id', 0);
        $active_title = $app->getUserState('com_projectfork.project.active.title', '');

        if(!$active_title) $active_title = JText::_('COM_PROJECTFORK_SELECT_PROJECT');


        // Set the JS functions
        $link = 'index.php?option=com_projectfork&amp;view=projects&amp;layout=modal&amp;tmpl=component&amp;function=pfSelectActiveProject';
        $rel  = "{handler: 'iframe', size: {x: 800, y: 450}}";

        $js_clear = 'document.id(\'filter_project_title\').value = \'\';'
                  . 'document.id(\'filter_project\').value = \'0\';'
                  . 'this.form.submit();';

        $js_select = 'SqueezeBox.open(\''.$link.'\', '.$rel.');';

        $js_head = "
		function pfSelectActiveProject(id, title) {
			document.getElementById('filter_project').value = id;
			document.getElementById('filter_project_title').value = title;
			SqueezeBox.close();
            Joomla.submitbutton('');
		}";
		$doc->addScriptDeclaration($js_head);


        // Setup the buttons
        $btn_clear = '';
        if($active_id && $can_change) {
            $btn_clear = '<button type="button" class="btn" onclick="'.$js_clear.'"><i class="icon-remove"></i> '.JText::_('JSEARCH_FILTER_CLEAR').'</button>';
        }

        $btn_select = '';
        if($can_change) {
            $btn_select = '<button type="button" class="btn" onclick="'.$js_select.'" title="'.JText::_('JSELECT').'"><i class="icon-briefcase"></i> '.$active_title.'</button>';
        }


        // HTML output
	    $html = '<span class="btn-group">'
				. $btn_select
				. $btn_clear
				. '</span>'
				.'<span class="btn-group">'
				. '<input type="hidden" name="filter_project_title" id="filter_project_title" class="btn disabled input-small" readonly="readonly" value="'.$active_title.'" />'
				. '<input type="hidden" name="filter_project" id="filter_project" value="'.$active_id.'" />'
				. '</span>';

		return $html;
    }


    /**
     * Translates a numerical priority value to a string label
     *
	 * @param    int      $value         The priority
     * @return   string   $html          The corresponding string label
	 */
    static function priorityToString($value = 0)
    {
        switch((int) $value)
        {
            case 1:
                $class = 'label-success very-low-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_LOW');
                break;

            case 2:
                $class = 'label-success low-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_LOW');
                break;

            case 3:
                $class = 'label-info medium-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_MEDIUM');
                break;

            case 4:
                $class = 'label-warning high-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_HIGH');
                break;

            case 5:
                $class = 'label-important very-high-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_HIGH');
                break;

            default:
                $class = 'label-success very-low-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_LOW');
                break;
        }


        $html = '<span class="label '.$class.'">'.$text.'</span>';

        return $html;
    }


    /**
     * Returns priority select list option objects
     *
     * @return   array   $options    The object list
	 */
    static function priorityOptions()
    {
        $options = array();

        $options[] =  JHtml::_('select.option', '1', JText::_('COM_PROJECTFORK_PRIORITY_VERY_LOW'));
        $options[] =  JHtml::_('select.option', '2', JText::_('COM_PROJECTFORK_PRIORITY_LOW'));
        $options[] =  JHtml::_('select.option', '3', JText::_('COM_PROJECTFORK_PRIORITY_MEDIUM'));
        $options[] =  JHtml::_('select.option', '4', JText::_('COM_PROJECTFORK_PRIORITY_HIGH'));
        $options[] =  JHtml::_('select.option', '5', JText::_('COM_PROJECTFORK_PRIORITY_VERY_HIGH'));

        return $options;
    }


    static function truncate($text = '', $chars = 40)
    {
        $truncated = strip_tags($text);
        $length    = strlen($truncated);

        if(($length + 3) < $chars) return $truncated;

        return substr($truncated, 0, $chars).'...';
    }


    static function ajaxReorder($list, $view, $i = 0)
    {
        $doc = JFactory::getDocument();
        $js  = array();

        $js[] = "window.addEvent('domready', function() {";
        $js[] = "    new Sortables('$list', {";
        $js[] = "        clone:false,";
        $js[] = "        constrain:true,";
        $js[] = "        revert: true,";
        $js[] = "        handle:'.icon-move',";
        $js[] = "        onStart: function(el, clone) {el.erase('style'); el.set('class', 'alert-info')},";
        $js[] = "        onComplete: function(el) {";
        $js[] = "            var order_array = new Array();";
        $js[] = "            var cid_array   = new Array();";
        $js[] = "            var i  = 0;";
        $js[] = "            $$('#$list li').each(function(li) {";
        $js[] = "                if(li.get('alt')) {";
        $js[] = "                    cid_array[i]   = 'cid[]=' + li.get('alt');";
        $js[] = "                    order_array[i] = 'order[]=' + i;";
        $js[] = "                    i++;";
        $js[] = "                }";
        $js[] = "            });";
        $js[] = "            var order = order_array.join('&');";
        $js[] = "            var cid   = cid_array.join('&');";
        $js[] = "            var token = '".JSession::getFormToken()."=1'";
        $js[] = "            ";
        $js[] = "            var req = new Request({";
        $js[] = "                url:'".htmlspecialchars(JFactory::getURI()->toString())."',";
        $js[] = "                method:'post',";
        $js[] = "                autoCancel:true,";
        $js[] = "                format:'json',";
        $js[] = "                data:'option=com_projectfork&task=".$view.".saveorder&' + cid + '&' + order + '&tmpl=component&' + token,";
        $js[] = "                onSuccess: function(responseText, responseXML) {";
        $js[] = "                    var resp = JSON.decode(responseText);";
        $js[] = "                    if(resp.success == true) {";
        $js[] = "                        el.morph('.alert-success', {duration: 500});";
        $js[] = "                    } else {";
        $js[] = "                        el.morph('.alert-error', {duration: 500});";
        $js[] = "                        alert(resp.message);";
        $js[] = "                    }";
        $js[] = "                },";
        $js[] = "                onFailure: function(xhr) {";
        $js[] = "                    el.morph('.alert-error', {duration: 500});";
        $js[] = "                    alert('Reorder failed. Request returned: ' + xhr);";
        $js[] = "                },";
        $js[] = "                onException: function(headerName, value) {";
        $js[] = "                    el.morph('.alert-error', {duration: 500});";
        $js[] = "                    alert('Reorder failed. Header exception: ' + headerName + ' - ' + value);";
        $js[] = "                }";
        $js[] = "            }).send();";
        $js[] = "        }";
        $js[] = "    })";
        $js[] = "});";


        $doc->addScriptDeclaration(implode("\n", $js));
    }
}
