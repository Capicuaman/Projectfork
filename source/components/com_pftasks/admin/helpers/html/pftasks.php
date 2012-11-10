<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


abstract class JHtmlPFtasks
{
    static public function assignedLabel($id, $i = 0, $users = null)
    {
        if (!is_array($users) || !count($users)) {
            return '<span id="assigned_' . $i . '_label"></span>'
                 . '<input type="hidden" id="assigned' . $i . '" name="assigned[' . $id . ']" />';
        }

        $html  = array();
        $count = count($users);

        if ($count == 1) {
            $html[] = '<span id="assigned_' . $i . '_label" class="label user">';
            $html[] = '<i class="icon-user icon-white"></i> ';
            $html[] = htmlspecialchars($users[0]->name, ENT_COMPAT, 'UTF-8');
            $html[] = '</span>';
        }
        else {
            $count = $count - 1;
            $first = array_pop(array_reverse($users));
            $names = array();

            foreach ($users AS $user)
            {
                $names[] = htmlspecialchars($user->name, ENT_COMPAT, 'UTF-8');
            }

            $tooltip = '::' . htmlspecialchars(implode('<br/>', $names), ENT_COMPAT, 'UTF-8');

            $html[] = '<span id="assigned_' . $i . '_label" class="label user hasTip" title="' . $tooltip . '" style="cursor: help">';
            $html[] = '<i class="icon-user icon-white"></i> ';
            $html[] = htmlspecialchars($first->name, ENT_COMPAT, 'UTF-8') . ' +' . $count;
            $html[] = '</span>';
        }

        $html[] = '<input type="hidden" id="assigned' . $i . '" name="assigned[' . $id . ']" />';

        return implode('', $html);
    }


    static public function priorityLabel($id, $i = 0, $value = null)
    {
        switch((int) $value)
        {
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
            case 1:
                return '<span id="priority_' . $i . '_label"></span>'
                     . '<input type="hidden" name="priority[' . $id . ']" id="priority' . $i . '" value="1"/>';
                break;
        }

        $html = '<span id="priority_' . $i . '_label" class="label ' . $class . '"><i class="icon-warning icon-white"></i> ' . $text . '</span>'
              . '<input type="hidden" name="priority[' . $id . ']" id="priority' . $i . '" value="' . (int) $value . '"/>';

        return $html;
    }


    public static function complete($i, $complete = 0, $can_change = false, $parents = array(), $users = array(), $start = null)
    {
        $html = array();
        $uid  = JFactory::getUser()->get('id');
        $nd   = JFactory::getDbo()->getNullDate();

        $p_tooltip = null;
        $u_tooltip = null;
        $s_tooltip = null;

        if ($can_change) {
            // Check if the user is assigned to the task
            if (count($users)) {
                $can_change = false;

                foreach ($users AS $user)
                {
                    if ((int) $user->user_id == $uid) {
                        $can_change = true;
                    }
                }

                if (!$can_change) {
                    $u_tooltip = JText::_('COM_PROJECTFORK_TASKS_NOT_ASSIGNED');
                }
            }

            // Check if all dependencies are completed
            if ($can_change && $complete == 0) {
                if (count($parents)) {
                    $req = array();

                    foreach ($parents AS $parent)
                    {
                        if ($parent->complete != '1') {
                            $can_change = false;
                            $req[] = htmlspecialchars($parent->title, ENT_COMPAT, 'UTF-8');
                        }
                    }

                    if (!$can_change) {
                        $p_tooltip = JText::_('COM_PROJECTFORK_TASKS_DEPENDS_ON') . '::' . implode('<br/>', $req);
                    }
                }
            }

            // Check if the task can be started
            if ($can_change && $complete == 0) {
                if ($start && $start != $nd) {
                    $now = time();
                    $ts  = strtotime($start);

                    if ($ts > $now) {
                        $can_change = false;
                        $s_tooltip  = JText::_('COM_PROJECTFORK_TASKS_NOT_STARTED') . '::' . PFDate::relative($start);
                    }
                }
            }
        }

        if ($can_change) {
            $class = ($complete ? ' btn-success active' : '');

            $html[] = '<div class="btn-group">';
            $html[] = '<a id="complete-btn-' . $i . '" class="btn btn-mini' . $class . '" href="javascript:void(0);" onclick="PFtask.complete(' . $i . ');">';
            $html[] = '<i class="icon-ok"></i>';
            $html[] = '</a>';
            $html[] = '</div>';
            $html[] = '<input type="hidden" id="complete' . $i . '" value="' . (int) $complete . '"/>';
        }
        else {
            $class = ($complete ? ' label-success' : '');
            $title = '';

            if ($p_tooltip || $u_tooltip || $s_tooltip) {
                $class .= ' hasTip';

                if ($p_tooltip) $title = ' title="' . $p_tooltip . '"';
                if ($u_tooltip) $title = ' title="' . $u_tooltip . '"';
                if ($s_tooltip) $title = ' title="' . $s_tooltip . '"';
            }

            $html[] = '<div class="btn-group">';
            $html[] = '<a id="complete-btn-' . $i . '" class="btn btn-mini disabled' . $class . '"' . $title . '>';
            $html[] = '<i class="icon-lock"></i>';
            $html[] = '</a>';
            $html[] = '</div>';
            $html[] = '<input type="hidden" id="complete' . $i . '" value="' . (int) $complete . '"/>';
        }

        return implode('', $html);
    }
}