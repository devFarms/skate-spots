<?php
/**
 * Admin template for scenes list
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Skate Scenes</h1>
    
    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Status updated successfully!</p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Entry deleted successfully!</p>
        </div>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <ul class="subsubsub">
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-scenes'); ?>" <?php echo !isset($_GET['status']) ? 'class="current"' : ''; ?>>
                All <span class="count">(<?php echo Skate_Scenes::count(); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-scenes&status=0'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '0' ? 'class="current"' : ''; ?>>
                Pending <span class="count">(<?php echo Skate_Scenes::count(0); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-scenes&status=1'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '1' ? 'class="current"' : ''; ?>>
                Approved <span class="count">(<?php echo Skate_Scenes::count(1); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-scenes&status=2'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '2' ? 'class="current"' : ''; ?>>
                Rejected <span class="count">(<?php echo Skate_Scenes::count(2); ?>)</span>
            </a>
        </li>
    </ul>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Scene Title</th>
                <th>Movie</th>
                <th>Spot</th>
                <th>Timestamp</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($scenes)): ?>
                <?php foreach ($scenes as $scene): ?>
                    <tr>
                        <td><?php echo esc_html($scene->id); ?></td>
                        <td>
                            <?php if ($scene->scene_title): ?>
                                <strong><?php echo esc_html($scene->scene_title); ?></strong>
                            <?php else: ?>
                                <em>No title</em>
                            <?php endif; ?>
                            <?php if ($scene->scene_description): ?>
                                <br><small><?php echo esc_html(wp_trim_words($scene->scene_description, 10)); ?></small>
                            <?php endif; ?>
                            <?php if ($scene->video_url): ?>
                                <br><a href="<?php echo esc_url($scene->video_url); ?>" target="_blank">View Video</a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($scene->movie_title); ?></td>
                        <td><?php echo esc_html($scene->spot_title); ?></td>
                        <td><?php echo esc_html($scene->timestamp); ?></td>
                        <td>
                            <span class="status-<?php echo esc_attr($scene->status); ?>">
                                <?php echo esc_html(Skate_Spots_Database::get_status_label($scene->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($scene->created_at))); ?></td>
                        <td>
                            <?php if ($scene->status != 1): ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('skate_update_status'); ?>
                                    <input type="hidden" name="action" value="skate_update_status">
                                    <input type="hidden" name="type" value="scene">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($scene->id); ?>">
                                    <input type="hidden" name="status" value="1">
                                    <input type="hidden" name="redirect" value="admin.php?page=skate-spots-scenes">
                                    <button type="submit" class="button button-small">Approve</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($scene->status != 2): ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('skate_update_status'); ?>
                                    <input type="hidden" name="action" value="skate_update_status">
                                    <input type="hidden" name="type" value="scene">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($scene->id); ?>">
                                    <input type="hidden" name="status" value="2">
                                    <input type="hidden" name="redirect" value="admin.php?page=skate-spots-scenes">
                                    <button type="submit" class="button button-small">Reject</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this scene?');">
                                <?php wp_nonce_field('skate_delete_entry'); ?>
                                <input type="hidden" name="action" value="skate_delete_entry">
                                <input type="hidden" name="type" value="scene">
                                <input type="hidden" name="id" value="<?php echo esc_attr($scene->id); ?>">
                                <input type="hidden" name="redirect" value="admin.php?page=skate-spots-scenes">
                                <button type="submit" class="button button-small button-link-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No scenes found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>