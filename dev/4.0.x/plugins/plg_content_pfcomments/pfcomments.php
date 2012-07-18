<?php
/**
* @package   Projectfork Comments
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
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
 * Projectfork Comments plugin class.
 *
 */
class plgContentPfcomments extends JPlugin
{
    /**
     * The current item context
     *
     * @var    string
     */
    protected $item_context;

    /**
     * The current item id
     *
     * @var    integer
     */
    protected $item_id;

    /**
     * The title of the current item
     *
     * @var    string
     */
    protected $title;

    /**
     * The comments on the current item
     *
     * @var    array
     */
    protected $items;


    /**
     * Triggers the comment plugin after/below the item output.
     *
     * @param     string     $context       The current item context
     * @param     object     $item          The actual item data
     * @param     object     $params        The item parameters
     * @param     integer    $limitstart    Optional list limit (not being used though)
     *
     * @return    string                    The comment form and user comments HTML
     */
    public function onContentAfterDisplay($context, &$item, &$params, $ls = 0)
    {
        // List of valid contexts.
        // The context tells us which kind of data we're dealing with.
        $context_items = array('com_projectfork.project',
                               'com_projectfork.milestone',
                               'com_projectfork.task'
                              );

        // Check if the context is supported. Return empty string if its not.
        if (!in_array($context, $context_items)) {
            return '';
        }

        // Check if the plugin is disabled. Return empty string if it is.
        if (!JPluginHelper::isEnabled('content', 'pfcomments')) {
            return '';
        }

        // Dont show comments through the plugin if the output is not in HTML
        if (JFactory::getDocument()->getType() != 'html') {
            return '';
        }

        // Assign protected vars
        $this->item_context = $context;
        $this->item_id      = (int) $item->id;
        $this->title        = $item->title;

        // Get the comments
        JLoader::import('joomla.application.component.model');
        JLoader::import('items', JPATH_BASE . '/components/com_projectfork/models');

        $this->items = $this->getItems();


        // Gracefully return if something went wrong. Dont want this to be a showstopper.
        if ($this->items === false) {
            return '';
        }


        return $this->display();
    }


    public static function renderItem(&$item, $i = 0, $new_node = false)
    {
        $avatar = JFactory::getURI()->base(true) . '/components/com_projectfork/assets/projectfork/images/icons/avatar.jpg';

        if(!isset($item->author_name)) {
            $user = JFactory::getUser($item->created_by);
            $item->author_name = $user->name;
        }

        $html   = array();

        if($new_node) $html[] = '<ul class="well" id="comment-node-' . $item->parent_id . '">';

        $html[] = '<li id="comment-item-' . $item->id . '">'
                . '    <div class="comment-item">'
				. '        <a class="pull-left thumbnail" href="#">'
                . '            <img width="90" src="' . $avatar . '" alt="" />'
                . '        </a>'
				. '        <span class="item-title">'
				. '            <a href="#" id="comment-' . ($i + 1) . '">' . $item->author_name . '</a>'
				. '        </span>'
				. '        <span class="item-date">'
				. '            ' . JHtml::date($item->created)
				. '        </span>'
				. '        <div class="comment-content">'
				. '            <p>' . nl2br($item->description) . '</p>'
				. '            <div class="btn-group comment-item-actions">'
				. '                <a class="btn" href="javascript:void(0)"><i class="icon-edit"></i>' . JText::_('COM_PROJECTFORK_ACTION_EDIT') . '</a>'
				. '                <a class="btn" href="javascript:void(0)" onclick="Projectfork.showEditor(' . $item->id . ');"><i class="icon-comment"></i> '
                .                      JText::_('COM_PROJECTFORK_ACTION_REPLY')
                . '                </a>'
				. '            </div>'
				. '        </div>'
				. '        <hr />'
				. '    </div>'
                . '</li>';

        if($new_node) $html[] = '</ul>';


        return implode("\n", $html);
    }


    public static function renderEditor($parent = 0, $close_list = true)
    {
        $user   = JFactory::getUser();
        $avatar = JFactory::getURI()->base(true) . '/components/com_projectfork/assets/projectfork/images/icons/avatar.jpg';
        $style  = ($parent > 0) ? ' style="display:none;"' : '';

        $html   = array();
        $html[] = '<ul class="well" id="comment-editor-' . $parent . '"><li><div class="comment-editor">'
				. '    <a class="pull-left thumbnail" href="#">'
                . '        <img width="90" src="' . $avatar . '" alt="" />'
                . '    </a>'
                . '    <span class="item-title editor-title">'
				. '        ' . JText::_('COM_PROJECTFORK_WRITE_COMMENT')
				. '    </span>'
				. '    <div class="comment-editor-input">'
				. '        <textarea id="jform_description_' . $parent . '" name="jform[description][' . $parent . ']"></textarea>'
				. '        <div class="btn-group comment-form-actions">'
				. '            <a class="btn btn-info" href="javascript:void(0);" onclick="Projectfork.postComment(' . $parent . ');"><i class="icon-ok"></i> '
                . '                ' . JText::_('COM_PROJECTFORK_ACTION_POST_COMMENT')
                . '            </a>'
				. '            <a class="btn" href="javascript:void(0);" onclick="Projectfork.cancelComment(' . $parent . ');"><i class="icon-remove"></i> '
                .                  JText::_('COM_PROJECTFORK_ACTION_CANCEL')
                . '            </a>'
				. '        </div>'
                . '    </div>'
                . '    <hr />'
				. '</div></li>';

        if ($close_list) $html[] = '</ul>';

        return implode("\n", $html);
    }


    /**
     * Loads a list of comments
     *
     * @return    array    The comments on success; otherwise False.
     */
    protected function getItems()
    {
        // Get the comments model from the component
        $comments = JModel::getInstance('Comments', 'ProjectforkModel', array('ignore_request' => true));

        if ($comments === false) {
            return false;
        }

        // Override model states
        $comments->setState('filter.published', 1);
        $comments->setState('filter.author', '');
        $comments->setState('filter.search', '');
        $comments->setState('filter.context', $this->item_context);
        $comments->setState('filter.id', $this->item_id);

        $items = (array) $comments->getItems();

        return $items;
    }


    protected function display()
    {
        //$form_action = htmlspecialchars(JFactory::getURI()->toString());
        $form_action = JRoute::_('index.php');
        $count       = count($this->items);
        $level       = 0;
        $ul_open     = false;

        $html = array();
        $html[] = '<div class="items-more" id="comments">';
        $html[] = '    <form class="form-validate" name="commentForm" method="post" action="' . $form_action . '">';
        $html[] = '        <h3>' . JText::_('COM_PROJECTFORK_COMMENTS') . '</h3>';
        $html[] = '        <ul class="well" id="comment-node-0">';

        // Render comments
        foreach($this->items AS $i => $item)
        {
            if($item->level > $level) {
                $html[]  = '<ul class="well" id="comment-node-' . $item->parent_id . '">';
                $ul_open = true;
                $level   = $item->level;
            }
            elseif($item->level < $level) {
                while($level > $item->level)
                {
                    $html[] = '</ul>';
                    $level --;
                }

                $ul_open = false;
                $level   = $item->level;
            }

            $html[] = '            ' . plgContentPfcomments::renderItem($item, $i, false);
        }

        if($ul_open) {
            while($level > 0)
            {
                $html[] = '</ul>';
                $level --;
            }

            $ul_open = false;
        }

        $html[] = '        </ul>';
        $html[] = plgContentPfcomments::renderEditor(0);
        $html[] = '        <input type="hidden" id="jform_context" name="jform[id]" value="0" />';
        $html[] = '        <input type="hidden" id="jform_context" name="jform[context]" value="' . $this->item_context . '" />';
        $html[] = '        <input type="hidden" id="jform_item_id" name="jform[item_id]" value="' . $this->item_id . '" />';
        $html[] = '        <input type="hidden" id="jform_title" name="jform[title]" value="' . htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8') . '" />';
        $html[] = '        <input type="hidden" id="jform_parent_id" name="jform[parent_id]" value="0" />';
        $html[] = '        <input type="hidden" name="option" value="' . htmlspecialchars(JRequest::getVar('option'), ENT_QUOTES, 'UTF-8') . '" />';
        $html[] = '        <input type="hidden" name="task" value="commentform.apply" />';
        $html[] = '        ' . JHtml::_('form.token');
        $html[] = '        <input type="hidden" name="tmpl" value="component" />';
        $html[] = '        <input type="hidden" name="format" value="json" />';
        $html[] = '    </form>';
        $html[] = '</div>';


        return implode("\n", $html);
    }
}
