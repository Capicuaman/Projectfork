<?php
/**
* @package      Projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Abstract class for Repository HTML elements
 *
 */
abstract class ProjectforkRepository
{
    /**
     * Displays a batch widget for moving or copying items.
     *
     * @param     string    $project    The project id
     * @param     string    $dir        The current browsing directory
     *
     * @return    string                The necessary HTML for the widget.
     */
    public static function batchItem($project, $dir)
    {
        // Create the copy/move options.
        $options = array(
            JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
            JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
        );

        $paths = self::pathOptions($project, $dir);

        // Create the batch selector to change select the category by which to move or copy.
        $lines = array(
            '<label id="batch-choose-action-lbl" for="batch-choose-action">',
            JText::_('COM_PROJECTFORK_REPO_BATCH_MENU_LABEL'),
            '</label>',
            '<fieldset id="batch-choose-action" class="combo">',
            '<select name="batch[parent_id]" class="inputbox" id="batch-parent-id">',
            '<option value="">' . JText::_('JSELECT') . '</option>',
            JHtml::_('select.options', $paths),
            '</select>',
            JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'),
            '</fieldset>'
        );

        return implode("\n", $lines);
    }


    /**
     * Build a list of directory paths
     *
     * @param     string    $project    The project id
     * @param     integer   $exclude    The directory id to exclude
     *
     * @return    array                 The path array
     */
    public static function pathOptions($project, $exclude = null)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if ((int) $project == 0) {
            return array();
        }

        // Construct the query
        $query->select('a.id AS value, a.path AS text')
              ->from('#__pf_repo_dirs AS a')
              ->where('a.project_id = ' . $project);

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        if (is_numeric($exclude)) {
            $query->where('a.id != ' . $db->quote((int) $exclude));
        }

        $query->order('a.path');

        // Setup the query
        $db->setQuery((string) $query);

        // Return the result
        $list = (array) $db->loadObjectList();

        echo $db->getErrorMsg();

        return $list;
    }
}
