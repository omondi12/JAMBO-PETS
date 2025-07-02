<?php
// Start the session
session_start();

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'buyer') {
    // Redirect to login page if not logged in or not a buyer
    header("Location: ../auth/login.php");
    exit();
}

// Include database connection
require_once '../config/db.php';
require_once '../includes/functions.php';

$user_id = $_SESSION['user_id'];
$page_title = "My Messages";

// Handle sending new message
if (isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    $related_to_item_type = isset($_POST['related_to_item_type']) ? sanitize($_POST['related_to_item_type']) : 'general';
    $related_to_item_id = isset($_POST['related_to_item_id']) ? (int)$_POST['related_to_item_id'] : null;

    // Validate receiver exists and is a seller
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND user_type = 'seller'");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Insert the message
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message, related_to_item_type, related_to_item_id) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssi", $user_id, $receiver_id, $subject, $message, $related_to_item_type, $related_to_item_id);
        
        if ($stmt->execute()) {
            $success_message = "Message sent successfully!";
        } else {
            $error_message = "Failed to send message. Please try again.";
        }
    } else {
        $error_message = "Invalid recipient.";
    }
}

// Mark message as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = (int)$_GET['mark_read'];
    
    // Make sure the message belongs to the current user
    $stmt = $conn->prepare("UPDATE messages SET read_status = 1 
                           WHERE message_id = ? AND receiver_id = ?");
    $stmt->bind_param("ii", $message_id, $user_id);
    $stmt->execute();
    
    // Redirect to remove the query parameter
    header("Location: messages.php");
    exit();
}

// Delete message
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $message_id = (int)$_GET['delete'];
    
    // Make sure the message belongs to the current user (either as sender or receiver)
    $stmt = $conn->prepare("DELETE FROM messages 
                           WHERE message_id = ? AND (sender_id = ? OR receiver_id = ?)");
    $stmt->bind_param("iii", $message_id, $user_id, $user_id);
    $stmt->execute();
    
    $success_message = "Message deleted successfully!";
    
    // Redirect to remove the query parameter
    header("Location: messages.php");
    exit();
}

// Get conversations (grouped by the other person)
$conversations = [];

// First, get all unique users this buyer has exchanged messages with
$stmt = $conn->prepare("
    SELECT DISTINCT 
        IF(m.sender_id = ?, m.receiver_id, m.sender_id) as other_user_id,
        u.first_name, 
        u.last_name,
        u.profile_image,
        u.user_type,
        (SELECT COUNT(*) FROM messages 
         WHERE receiver_id = ? AND sender_id = other_user_id AND read_status = 0) as unread_count,
        (SELECT sent_at FROM messages 
         WHERE (sender_id = ? AND receiver_id = other_user_id) OR (sender_id = other_user_id AND receiver_id = ?)
         ORDER BY sent_at DESC LIMIT 1) as last_message_time
    FROM 
        messages m
    JOIN 
        users u ON u.user_id = IF(m.sender_id = ?, m.receiver_id, m.sender_id)
    WHERE 
        m.sender_id = ? OR m.receiver_id = ?
    ORDER BY 
        last_message_time DESC
");

$stmt->bind_param("iiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}

// If a conversation is selected, get all messages with that user
$selected_conversation = null;
$messages = [];

if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $other_user_id = (int)$_GET['user'];
    
    // Get user details
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, user_type, profile_image FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $other_user_id);
    $stmt->execute();
    $selected_conversation = $stmt->get_result()->fetch_assoc();
    
    if ($selected_conversation) {
        // Get all messages between the two users
        $stmt = $conn->prepare("
            SELECT 
                m.*, 
                CONCAT(u_sender.first_name, ' ', u_sender.last_name) as sender_name,
                u_sender.profile_image as sender_image
            FROM 
                messages m
            JOIN 
                users u_sender ON m.sender_id = u_sender.user_id
            WHERE 
                (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY 
                m.sent_at ASC
        ");
        
        $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
        $stmt->execute();
        $messages_result = $stmt->get_result();
        
        while ($row = $messages_result->fetch_assoc()) {
            $messages[] = $row;
            
            // Mark as read if the current user is the receiver
            if ($row['receiver_id'] == $user_id && $row['read_status'] == 0) {
                $msg_id = $row['message_id'];
                $update_stmt = $conn->prepare("UPDATE messages SET read_status = 1 WHERE message_id = ?");
                $update_stmt->bind_param("i", $msg_id);
                $update_stmt->execute();
            }
        }
    }
}

// Get seller list for new message
$sellers = [];
$stmt = $conn->prepare("
    SELECT u.user_id, u.first_name, u.last_name, sp.business_name 
    FROM users u
    JOIN seller_profiles sp ON u.user_id = sp.user_id
    WHERE u.user_type = 'seller' AND u.status = 'active'
    ORDER BY u.first_name
");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $sellers[] = $row;
}

$userId = $_SESSION['user_id'];

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];

// Include header
include_once '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo $page_title; ?></h5>
                    <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#newMessageModal">
                        <i class="fas fa-plus"></i> New Message
                    </button>
                </div>
                
                <div class="card-body p-0">
                    <!-- Display success/error messages -->
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success m-3"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger m-3"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="row g-0">
                        <!-- Conversation List -->
                        <div class="col-md-4 border-right">
                            <div class="list-group list-group-flush">
                                <?php if (empty($conversations)): ?>
                                    <div class="list-group-item text-center text-muted py-4">
                                        <i class="fas fa-comments fa-3x mb-3"></i>
                                        <p>No conversations yet</p>
                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newMessageModal">
                                            Start a conversation
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($conversations as $convo): ?>
                                        <a href="?user=<?php echo $convo['other_user_id']; ?>" 
                                           class="list-group-item list-group-item-action <?php echo (isset($_GET['user']) && $_GET['user'] == $convo['other_user_id']) ? 'active' : ''; ?>">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar mr-3">
                                                        <?php if ($convo['profile_image']): ?>
                                                            <img src="../uploads/<?php echo $convo['profile_image']; ?>" 
                                                                 alt="<?php echo htmlspecialchars($convo['first_name']); ?>" 
                                                                 class="rounded-circle" width="40" height="40">
                                                        <?php else: ?>
                                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px;">
                                                                <?php echo strtoupper(substr($convo['first_name'], 0, 1)); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <?php echo htmlspecialchars($convo['first_name'] . ' ' . $convo['last_name']); ?>
                                                            <span class="badge badge-pill badge-primary">
                                                                <?php echo ucfirst($convo['user_type']); ?>
                                                            </span>
                                                        </h6>
                                                        <?php if ($convo['unread_count'] > 0): ?>
                                                            <span class="badge badge-danger"><?php echo $convo['unread_count']; ?> new</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo time_elapsed_string($convo['last_message_time']); ?>
                                                </small>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Message Display -->
                        <div class="col-md-8">
                            <?php if ($selected_conversation): ?>
                                <div class="conversation-header border-bottom p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar mr-3">
                                            <?php if ($selected_conversation['profile_image']): ?>
                                                <img src="../uploads/<?php echo $selected_conversation['profile_image']; ?>"
                                                    alt="<?php echo htmlspecialchars($selected_conversation['first_name']); ?>"
                                                    class="rounded-circle"
                                                    width="40" height="40"
                                                    style="cursor: pointer;"
                                                    onclick="openLightbox('../uploads/<?php echo $selected_conversation['profile_image']; ?>', '<?php echo htmlspecialchars($selected_conversation['first_name']); ?>')">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <?php echo strtoupper(substr($selected_conversation['first_name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">
                                                <?php echo htmlspecialchars($selected_conversation['first_name'] . ' ' . $selected_conversation['last_name']); ?>
                                            </h5>
                                            <small class="text-muted"><?php echo ucfirst($selected_conversation['user_type']); ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="messages-container p-3" style="height: 400px; overflow-y: auto;">
                                    <?php if (empty($messages)): ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>No messages yet with this user</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($messages as $msg): ?>
                                            <div class="message mb-3 <?php echo ($msg['sender_id'] == $user_id) ? 'text-right' : ''; ?>">
                                                <div class="message-content <?php echo ($msg['sender_id'] == $user_id) ? 'sent' : 'received'; ?> d-inline-block">
                                                    <div class="message-body p-3 rounded">
                                                        <?php if ($msg['subject']): ?>
                                                            <h6 class="message-subject mb-2"><?php echo htmlspecialchars($msg['subject']); ?></h6>
                                                        <?php endif; ?>
                                                        
                                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                                        
                                                        <?php if ($msg['related_to_item_type'] != 'general' && $msg['related_to_item_id']): ?>
                                                            <div class="related-item small text-muted">
                                                                <i class="fas fa-link"></i> Related to: 
                                                                <?php 
                                                                    if ($msg['related_to_item_type'] == 'pet'): 
                                                                        $item_stmt = $conn->prepare("SELECT name FROM pets WHERE pet_id = ?");
                                                                        $item_stmt->bind_param("i", $msg['related_to_item_id']);
                                                                        $item_stmt->execute();
                                                                        $item_result = $item_stmt->get_result();
                                                                        if ($item = $item_result->fetch_assoc()):
                                                                            echo "Pet: " . htmlspecialchars($item['name']);
                                                                        endif;
                                                                    elseif ($msg['related_to_item_type'] == 'product'): 
                                                                        $item_stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
                                                                        $item_stmt->bind_param("i", $msg['related_to_item_id']);
                                                                        $item_stmt->execute();
                                                                        $item_result = $item_stmt->get_result();
                                                                        if ($item = $item_result->fetch_assoc()):
                                                                            echo "Product: " . htmlspecialchars($item['name']);
                                                                        endif;
                                                                    elseif ($msg['related_to_item_type'] == 'order'): 
                                                                        echo "Order #" . $msg['related_to_item_id'];
                                                                    endif;
                                                                    ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="message-meta small text-muted mt-2">
                                                            <span><?php echo date('M d, Y g:i A', strtotime($msg['sent_at'])); ?></span>
                                                            <?php if ($msg['sender_id'] == $user_id): ?>
                                                                <a href="?delete=<?php echo $msg['message_id']; ?>" 
                                                                   class="text-danger ml-2" 
                                                                   onclick="return confirm('Are you sure you want to delete this message?');">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Reply Form -->
                                <div class="reply-form p-3 border-top">
                                    <form method="post" action="">
                                        <input type="hidden" name="receiver_id" value="<?php echo $selected_conversation['user_id']; ?>">
                                        <div class="form-group">
                                            <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="subject" class="form-control" placeholder="Subject (optional)">
                                        </div>
                                        <button type="submit" name="send_message" class="btn btn-primary">Send</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-comments fa-4x mb-3"></i>
                                    <h4>No conversation selected</h4>
                                    <p>Select a conversation from the list or start a new one</p>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#newMessageModal">
                                        New Message
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div class="modal fade" id="newMessageModal" tabindex="-1" role="dialog" aria-labelledby="newMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newMessageModalLabel">New Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="receiver_id">Recipient</label>
                        <select name="receiver_id" id="receiver_id" class="form-control" required>
                            <option value="">-- Select a seller --</option>
                            <?php foreach ($sellers as $seller): ?>
                                <option value="<?php echo $seller['user_id']; ?>">
                                    <?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?>
                                    <?php if ($seller['business_name']): ?>
                                        (<?php echo htmlspecialchars($seller['business_name']); ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="Message subject">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
                    </div>
                    
                    <?php 
                    // Show related item options if coming from a pet or product page
                    if (isset($_GET['item_type']) && isset($_GET['item_id'])): 
                        $item_type = sanitize($_GET['item_type']);
                        $item_id = (int)$_GET['item_id'];
                        $item_name = '';
                        
                        if ($item_type == 'pet') {
                            $stmt = $conn->prepare("SELECT name FROM pets WHERE pet_id = ?");
                            $stmt->bind_param("i", $item_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($item = $result->fetch_assoc()) {
                                $item_name = $item['name'];
                            }
                        } elseif ($item_type == 'product') {
                            $stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
                            $stmt->bind_param("i", $item_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($item = $result->fetch_assoc()) {
                                $item_name = $item['name'];
                            }
                        }
                    ?>
                        <div class="form-group">
                            <label>Related to</label>
                            <div class="form-control bg-light">
                                <?php echo ucfirst($item_type) . ': ' . htmlspecialchars($item_name); ?>
                                <input type="hidden" name="related_to_item_type" value="<?php echo $item_type; ?>">
                                <input type="hidden" name="related_to_item_id" value="<?php echo $item_id; ?>">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .border-right {
        border-right: 1px solid #dee2e6;
    }
    
    .message-content {
        max-width: 80%;
    }
    
    .message-body {
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .sent .message-body {
        background-color: #dcf8c6;
        text-align: left;
    }
    
    .received .message-body {
        background-color: #f1f0f0;
    }
    
    .messages-container {
        display: flex;
        flex-direction: column;
    }
</style>

<script>
    // Scroll to bottom of messages container on load
    document.addEventListener('DOMContentLoaded', function() {
        const messagesContainer = document.querySelector('.messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });
</script>

<?php include_once '../includes/footer.php'; ?>