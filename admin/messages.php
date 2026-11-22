<?php
$page_title = 'Messages - Admin';
$current_page = 'messages';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'read') {
        $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Message marked as read.');
    } elseif ($action === 'unread') {
        $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Message marked as unread.');
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Message deleted.');
    }
    redirect('messages.php');
}

$filter = $_GET['filter'] ?? 'all';
$where = '';
if ($filter === 'unread') $where = 'WHERE is_read = 0';
elseif ($filter === 'read') $where = 'WHERE is_read = 1';

$messages = $conn->query("SELECT * FROM contact_messages $where ORDER BY created_at DESC");

$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM contact_messages WHERE is_read = 0")->fetch_assoc()['cnt'];
$total_count = $conn->query("SELECT COUNT(*) as cnt FROM contact_messages")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .message-row.unread { background: #eef3ff; font-weight: 500; }
        .message-row.unread td:first-child { border-left: 3px solid var(--primary); }
        .filter-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
        .filter-tabs a { padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; color: var(--gray-700); background: var(--gray-100); text-decoration: none; }
        .filter-tabs a.active { background: var(--primary); color: white; }
        .filter-tabs .badge-count { background: var(--danger); color: white; border-radius: 10px; padding: 1px 7px; font-size: 0.75rem; margin-left: 4px; }
        .message-detail { background: var(--gray-50); padding: 1rem 1.5rem; border-radius: var(--border-radius); margin-top: 0.5rem; display: none; }
        .message-detail.show { display: block; }
        .msg-toggle { cursor: pointer; }
        .msg-toggle:hover { background: var(--gray-100); }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php require_once 'includes/admin-sidebar.php'; ?>
        <main class="admin-content">
            <?php $flash = getFlash(); if($flash): ?>
            <div class="alert alert-<?php echo sanitize($flash['type']); ?>">
                <?php echo sanitize($flash['message']); ?>
                <button class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h1>Contact Messages</h1>
                <p><?php echo $total_count; ?> total messages, <?php echo $unread_count; ?> unread</p>
            </div>

            <div class="filter-tabs">
                <a href="messages.php" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All (<?php echo $total_count; ?>)</a>
                <a href="messages.php?filter=unread" class="<?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread<?php if($unread_count > 0) echo '<span class="badge-count">'.$unread_count.'</span>'; ?></a>
                <a href="messages.php?filter=read" class="<?php echo $filter === 'read' ? 'active' : ''; ?>">Read</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($messages && $messages->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($msg = $messages->fetch_assoc()): ?>
                                <tr class="message-row <?php echo $msg['is_read'] ? 'read' : 'unread'; ?> msg-toggle" onclick="toggleMessage(<?php echo $msg['id']; ?>)">
                                    <td><?php echo sanitize($msg['name']); ?></td>
                                    <td><?php echo sanitize($msg['email']); ?></td>
                                    <td><?php echo sanitize($msg['subject']); ?></td>
                                    <td><?php echo formatDate($msg['created_at']); ?></td>
                                    <td>
                                        <?php if ($msg['is_read']): ?>
                                            <span class="badge badge-secondary">Read</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($msg['is_read']): ?>
                                            <a href="messages.php?action=unread&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-secondary" onclick="event.stopPropagation();">Mark Unread</a>
                                        <?php else: ?>
                                            <a href="messages.php?action=read&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-primary" onclick="event.stopPropagation();">Mark Read</a>
                                        <?php endif; ?>
                                        <a href="messages.php?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger" onclick="event.stopPropagation(); return confirm('Delete this message?');">Delete</a>
                                    </td>
                                </tr>
                                <tr id="msg-detail-<?php echo $msg['id']; ?>" style="display: none;">
                                    <td colspan="6">
                                        <div class="message-detail show">
                                            <p><strong>From:</strong> <?php echo sanitize($msg['name']); ?> (<?php echo sanitize($msg['email']); ?>)</p>
                                            <p><strong>Subject:</strong> <?php echo sanitize($msg['subject']); ?></p>
                                            <p><strong>Date:</strong> <?php echo $msg['created_at']; ?></p>
                                            <hr style="margin: 0.75rem 0; border-color: var(--gray-200);">
                                            <p><strong>Message:</strong></p>
                                            <p style="white-space: pre-wrap; line-height: 1.6;"><?php echo sanitize($msg['message']); ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: var(--gray-600);">No messages found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    function toggleMessage(id) {
        var row = document.getElementById('msg-detail-' + id);
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
            fetch('messages.php?action=read&id=' + id);
        } else {
            row.style.display = 'none';
        }
    }
    </script>
</body>
</html>

