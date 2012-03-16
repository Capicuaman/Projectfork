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

// No direct access
defined('_JEXEC') or die;


class ProjectforkHelperToolbar
{
    private $buttons;


    public function __construct()
    {
        $this->buttons = array();
    }


    public function button($text, $task = '', $list = false)
    {
        $this->buttons[] = $this->renderButton($text, $task, $list);
    }


    protected function renderButton($text, $task = '', $list = false)
    {
        $html = array();

        $html[] = '<input type="submit" class="button btn btn-info" ';
        $html[] = 'value="'.addslashes(JText::_($text)).'" ';

        if($task) {
            $html[] = 'onclick="';

            if($list) {
                $message = JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		        $message = addslashes($message);
                $html[] = "if(document.adminForm.boxchecked.value==0){alert('$message');}else{Joomla.submitbutton('$task')}";
            }
            else {
                $html[] = "Joomla.submitbutton('$task');";
            }

            $html[] = '" ';
        }

        $html[] = '/>';

        return implode('', $html);
    }


    public function __toString()
    {
        return implode('', $this->buttons);
    }
}