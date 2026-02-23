<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Process support ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $priority = $_POST['priority'];
    
    // Insert support ticket
    $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, subject, message, priority) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $_SESSION['user_id'], $subject, $message, $priority);
    
    if ($stmt->execute()) {
        $success_message = "Support ticket submitted successfully! We'll respond as soon as possible.";
    } else {
        $error_message = "Error submitting support ticket: " . $conn->error;
    }
}

// Fetch user's support tickets
$tickets_sql = "SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($tickets_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tickets_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Support Center</h1>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="support-container">
                <div class="support-intro">
                    <p>Need help? Submit a support ticket and our team will assist you as soon as possible.</p>
                </div>
                
                <div class="support-options">
                    <div class="option-card">
                        <div class="option-icon">📞</div>
                        <h3>Call Support</h3>
                        <p>1-800-JT-EXPRESS</p>
                        <button class="btn btn-secondary">Call Now</button>
                    </div>
                    
                    <div class="option-card">
                        <div class="option-icon">✉️</div>
                        <h3>Email Support</h3>
                        <p>support@jntexpress.com</p>
                        <button class="btn btn-secondary">Send Email</button>
                    </div>
                    
                    <div class="option-card">
                        <div class="option-icon">💬</div>
                        <h3>Live Chat</h3>
                        <p>Chat with our support team</p>
                        <button class="btn btn-secondary">Start Chat</button>
                    </div>
                </div>
                
                <div class="submit-ticket">
                    <div class="ticket-card">
                        <h3>Submit a Ticket</h3>
                        <form method="POST">
                            <div class="form-group full-width">
                                <label for="subject">Subject *</label>
                                <input type="text" id="subject" name="subject" placeholder="Briefly describe your issue" required>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" placeholder="Describe your issue in detail..." rows="5" required></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <select id="priority" name="priority">
                                        <option value="Low">Low</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Ticket</button>
                        </form>
                    </div>
                </div>
                
                <div class="my-tickets">
                    <h3>My Support Tickets</h3>
                    <?php if ($tickets_result->num_rows > 0): ?>
                        <div class="tickets-list">
                            <?php while($ticket = $tickets_result->fetch_assoc()): ?>
                            <div class="ticket-item">
                                <div class="ticket-header">
                                    <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>"><?php echo $ticket['status']; ?></span>
                                </div>
                                <p><?php echo htmlspecialchars(substr($ticket['message'], 0, 100)) . (strlen($ticket['message']) > 100 ? '...' : ''); ?></p>
                                <div class="ticket-meta">
                                    <span class="ticket-id">#<?php echo $ticket['id']; ?></span>
                                    <span class="ticket-date"><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></span>
                                    <span class="ticket-priority priority-<?php echo strtolower($ticket['priority']); ?>"><?php echo $ticket['priority']; ?></span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>No support tickets found.</p>
                    <?php endif; ?>
                </div>
                
                <div class="faq-support">
                    <h3>Frequently Asked Questions</h3>
                    <div class="faq-item">
                        <h4>How do I track my shipment?</h4>
                        <p>You can track your shipment using the tracking number on our website or mobile app.</p>
                    </div>
                    <div class="faq-item">
                        <h4>What are your operating hours?</h4>
                        <p>Our offices operate from 8:00 AM to 8:00 PM daily. Delivery hours may vary by location.</p>
                    </div>
                    <div class="faq-item">
                        <h4>How much does shipping cost?</h4>
                        <p>Shipping costs vary based on weight, distance, and service type. Use our shipping calculator to estimate costs.</p>
                    </div>
                    <div class="faq-item">
                        <h4>Can I schedule a package pickup?</h4>
                        <p>Yes, you can schedule a package pickup through our website or mobile app.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>