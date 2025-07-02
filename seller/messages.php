<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'seller') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$seller_id = getSellerInfo($user_id);

// Get sellers contacts (people who have messaged this seller)
$contacts_query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.profile_image, 
                   (SELECT COUNT(*) FROM messages WHERE sender_id = u.user_id AND receiver_id = ? AND read_status = 0) as unread_count,
                   (SELECT sent_at FROM messages WHERE (sender_id = u.user_id AND receiver_id = ?) 
                    OR (sender_id = ? AND receiver_id = u.user_id) ORDER BY sent_at DESC LIMIT 1) as last_message_time
                   FROM users u
                   JOIN messages m ON (m.sender_id = u.user_id AND m.receiver_id = ?)
                   OR (m.sender_id = ? AND m.receiver_id = u.user_id)
                   WHERE u.user_id != ?
                   ORDER BY last_message_time DESC";
                   
$stmt = $conn->prepare($contacts_query);
$stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$contacts_result = $stmt->get_result();

// Handle selecting a specific conversation
$selected_user = null;
$messages = [];

if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $selected_user_id = $_GET['user'];
    
    // Get user details
    $user_query = "SELECT user_id, first_name, last_name, profile_image FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $selected_user = $user_result->fetch_assoc();
        
        // Mark all messages from this user as read
        $mark_read_query = "UPDATE messages SET read_status = 1 
                           WHERE sender_id = ? AND receiver_id = ? AND read_status = 0";
        $stmt = $conn->prepare($mark_read_query);
        $stmt->bind_param("ii", $selected_user_id, $user_id);
        $stmt->execute();
        
        // Get messages between the users
        $messages_query = "SELECT m.*, 
                          CASE WHEN m.sender_id = ? THEN 'sent' ELSE 'received' END as message_type,
                          u.first_name, u.last_name, u.profile_image
                          FROM messages m
                          JOIN users u ON m.sender_id = u.user_id
                          WHERE (m.sender_id = ? AND m.receiver_id = ?)
                          OR (m.sender_id = ? AND m.receiver_id = ?)
                          ORDER BY m.sent_at ASC";
        $stmt = $conn->prepare($messages_query);
        $stmt->bind_param("iiiii", $user_id, $user_id, $selected_user_id, $selected_user_id, $user_id);
        $stmt->execute();
        $messages_result = $stmt->get_result();
        
        while ($row = $messages_result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message_text = trim($_POST['message_text']);
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : null;
    $related_type = isset($_POST['related_type']) ? $_POST['related_type'] : 'general';
    $related_id = isset($_POST['related_id']) ? $_POST['related_id'] : null;
    
    if (!empty($message_text) && !empty($receiver_id)) {
        $insert_query = "INSERT INTO messages (sender_id, receiver_id, subject, message, 
                        related_to_item_type, related_to_item_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iisssi", $user_id, $receiver_id, $subject, $message_text, $related_type, $related_id);
        
        if ($stmt->execute()) {
            // Redirect to refresh the page and show the new message
            header("Location: messages.php?user=$receiver_id");
            exit();
        }
    }
}

// Get total number of unread messages for notification display
$unread_query = "SELECT COUNT(*) as unread_total FROM messages WHERE receiver_id = ? AND read_status = 0";
$stmt = $conn->prepare($unread_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_result = $stmt->get_result();
$unread_count = $unread_result->fetch_assoc()['unread_total'];

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
    <div class="col-md-3">
            <!-- Seller sidebar -->
            <?php include_once 'seller_sidebar.php'; ?>
        </div>
        <main class="col-md-9 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Messages <?php if($unread_count > 0): ?><span class="badge bg-danger"><?php echo $unread_count; ?></span><?php endif; ?></h1>
            </div>
            
            <div class="row">
                <!-- Contacts List -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Conversations</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if ($contacts_result->num_rows > 0): ?>
                                <?php while ($contact = $contacts_result->fetch_assoc()): ?>
                                    <a href="messages.php?user=<?php echo $contact['user_id']; ?>" 
                                       class="list-group-item list-group-item-action <?php echo (isset($selected_user) && $selected_user['user_id'] == $contact['user_id']) ? 'active' : ''; ?>">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <?php if ($contact['profile_image']): ?>
                                                        <img src="../uploads/<?php echo $contact['profile_image']; ?>" 
                                                             alt="Profile" class="rounded-circle" width="40" height="40">
                                                    <?php else: ?>
                                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <?php echo substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo $contact['first_name'] . ' ' . $contact['last_name']; ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($contact['last_message_time'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php if ($contact['unread_count'] > 0): ?>
                                                <span class="badge bg-primary rounded-pill"><?php echo $contact['unread_count']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="list-group-item">
                                    <p class="mb-0">No conversations yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Message Display and Form -->
                <div class="col-md-8">
                    <?php if (isset($selected_user)): ?>
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="d-flex align-items-center">
                                    <?php if ($selected_user['profile_image']): ?>
                                        <img src="../uploads/<?php echo $selected_user['profile_image']; ?>"
                                            alt="Profile" class="rounded-circle me-2" width="40" height="40"
                                            style="cursor: pointer;"
                                            onclick="openLightbox('../uploads/<?php echo $selected_user['profile_image']; ?>', '<?php echo htmlspecialchars($selected_user['first_name'] . ' ' . $selected_user['last_name']); ?>')">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2"
                                            style="width: 40px; height: 40px;">
                                            <?php echo substr($selected_user['first_name'], 0, 1) . substr($selected_user['last_name'], 0, 1); ?>
                                        </div>
                                    <?php endif; ?>
                                    <h5 class="mb-0"><?php echo $selected_user['first_name'] . ' ' . $selected_user['last_name']; ?></h5>
                                </div>
                            </div>
                            
                            <div class="card-body" style="height: 400px; overflow-y: auto;">
                                <?php if (count($messages) > 0): ?>
                                    <?php foreach ($messages as $message): ?>
                                        <div class="mb-3 <?php echo $message['message_type'] == 'sent' ? 'text-end' : ''; ?>">
                                            <div class="d-inline-block <?php echo $message['message_type'] == 'sent' ? 'bg-primary text-white' : 'bg-light'; ?> 
                                                  rounded p-2" style="max-width: 75%;">
                                                <?php if ($message['subject']): ?>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($message['subject']); ?></div>
                                                <?php endif; ?>
                                                <div><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                                <div class="text-muted small <?php echo $message['message_type'] == 'sent' ? 'text-white-50' : ''; ?>">
                                                    <?php echo date('M d, Y g:i A', strtotime($message['sent_at'])); ?>
                                                </div>
                                                <?php if ($message['related_to_item_type'] != 'general'): ?>
                                                    <div class="mt-1 small">
                                                        <span class="badge bg-secondary">
                                                            Re: <?php echo ucfirst($message['related_to_item_type']); ?> #<?php echo $message['related_to_item_id']; ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-5">
                                        <p>No messages yet. Start a conversation!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer">
                                <form method="post" action="messages.php?user=<?php echo $selected_user['user_id']; ?>">
                                    <input type="hidden" name="receiver_id" value="<?php echo $selected_user['user_id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="message_text" class="form-label">Message</label>
                                        <textarea class="form-control" id="message_text" name="message_text" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <h5 class="mb-3">Select a conversation to view messages</h5>
                                <p class="text-muted">Choose a contact from the list to start chatting</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>