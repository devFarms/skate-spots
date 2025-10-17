<?php
/**
 * Admin template for movies list
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Skate Movies</h1>
    
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
            <a href="<?php echo admin_url('admin.php?page=skate-spots-movies'); ?>" <?php echo !isset($_GET['status']) ? 'class="current"' : ''; ?>>
                All <span class="count">(<?php echo Skate_Movies::count(); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-movies&status=0'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '0' ? 'class="current"' : ''; ?>>
                Pending <span class="count">(<?php echo Skate_Movies::count(0); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-movies&status=1'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '1' ? 'class="current"' : ''; ?>>
                Approved <span class="count">(<?php echo Skate_Movies::count(1); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-movies&status=2'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '2' ? 'class="current"' : ''; ?>>
                Rejected <span class="count">(<?php echo Skate_Movies::count(2); ?>)</span>
            </a>
        </li>
    </ul>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Director</th>
                <th>Release Date</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($movies)): ?>
                <?php foreach ($movies as $movie): ?>
                    <tr>
                        <td><?php echo esc_html($movie->id); ?></td>
                        <td>
                            <strong><?php echo esc_html($movie->title); ?></strong>
                            <?php if ($movie->description): ?>
                                <br><small><?php echo esc_html(wp_trim_words($movie->description, 15)); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($movie->director); ?></td>
                        <td>
                            <?php if ($movie->release_date): ?>
                                <?php echo esc_html(date('Y-m-d', strtotime($movie->release_date))); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-<?php echo esc_attr($movie->status); ?>">
                                <?php echo esc_html(Skate_Spots_Database::get_status_label($movie->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($movie->created_at))); ?></td>
                        <td>
                            <?php if ($movie->status != 1): ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('skate_update_status'); ?>
                                    <input type="hidden" name="action" value="skate_update_status">
                                    <input type="hidden" name="type" value="movie">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($movie->id); ?>">
                                    <input type="hidden" name="status" value="1">
                                    <input type="hidden" name="redirect" value="admin.php?page=skate-spots-movies">
                                    <button type="submit" class="button button-small">Approve</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($movie->status != 2): ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('skate_update_status'); ?>
                                    <input type="hidden" name="action" value="skate_update_status">
                                    <input type="hidden" name="type" value="movie">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($movie->id); ?>">
                                    <input type="hidden" name="status" value="2">
                                    <input type="hidden" name="redirect" value="admin.php?page=skate-spots-movies">
                                    <button type="submit" class="button button-small">Reject</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this movie?');">
                                <?php wp_nonce_field('skate_delete_entry'); ?>
                                <input type="hidden" name="action" value="skate_delete_entry">
                                <input type="hidden" name="type" value="movie">
                                <input type="hidden" name="id" value="<?php echo esc_attr($movie->id); ?>">
                                <input type="hidden" name="redirect" value="admin.php?page=skate-spots-movies">
                                <button type="submit" class="button button-small button-link-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No movies found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>