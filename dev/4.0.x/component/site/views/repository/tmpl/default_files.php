<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$user     = JFactory::getUser();
$uid      = $user->get('id');
$x        = count($this->items['directories']) + count($this->items['notes']);

foreach ($this->items['files'] as $i => $item) :
    $edit_link = 'task=file.edit&filter_project=' . $item->project_id . 'filter_parent_id=' . $item->dir_id . '&id=' . $item->id;
    $access    = ProjectforkHelperAccess::getActions('file', $item->id);
    $icon      = $this->escape(strtolower($item->file_extension));

    $can_create   = $access->get('file.create');
    $can_edit     = $access->get('file.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('file.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('file.edit.state') && $can_checkin);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center">
            <?php echo JHtml::_('grid.id', $x, $item->id, false, 'fid'); ?>
        </td>
        <td>
            <i class="icon-file icon-<?php echo $icon;?>"></i>
            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
            <a href="<?php echo JRoute::_('index.php?option=com_projectfork&' . $edit_link);?>">
                <?php echo JText::_($this->escape($item->title)); ?>
            </a>
        </td>
        <td>
            <?php
                $this->menu->start(array('class' => 'btn-mini'));
                $this->menu->itemEdit('directoryform', $item->id, ($can_edit || $can_edit_own));
                $this->menu->itemDelete('repository', $x, ($can_edit || $can_edit_own));
                $this->menu->end();

                echo $this->menu->render();
            ?>
        </td>
        <td>
            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
        </td>
        <td>
            <?php echo JHtml::_('projectfork.truncate', $item->description); ?> <i class="icon-user"></i> <?php echo $this->escape($item->author_name); ?>
        </td>
    </tr>
<?php $x++; endforeach; ?>
