<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class PFcommentsHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_pfcomments';


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */
    public static function addSubmenu($view)
    {
        $components = PFApplicationHelper::getComponents();
        $option     = JFactory::getApplication()->input->get('option');

        foreach ($components AS $component)
        {
            if ($component->enabled == '0') {
                continue;
            }

            JSubMenuHelper::addEntry(
                JText::_($component->element),
                'index.php?option=' . $component->element,
                ($option == $component->element)
            );
        }
    }


    /**
     * Gets a list of actions that can be performed.
     *
     * @param     integer    $id         The item id
     *
     * @return    jobject
     */
    public static function getActions($id = 0)
    {
        $user   = JFactory::getUser();
        $result = new JObject;

        if ((empty($id) || $id == 0)) {
            $asset = self::$extension;
        }
        else {
            $asset = 'com_pfcomments.comment.' . (int) $id;
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $asset));
        }

        return $result;
    }
}
