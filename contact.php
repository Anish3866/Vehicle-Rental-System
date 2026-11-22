<?php
session_start();
$page_title = 'Contact Us - Vehicle Rental';
$current_page = 'contact';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            setFlash('success', 'Thank you for your message. We will get back to you shortly.');
        } else {
            setFlash('error', 'Something went wrong. Please try again later.');
        }
        $stmt->close();
    } else {
        setFlash('error', 'Please fill all the fields.');
    }
    redirect('contact.php');
}

require_once 'includes/header.php';
?>

<div class="page-header" style="background: #f8f9fa; padding: 3rem 0; text-align: center;">
    <div class="container">
        <h1>Contact Us</h1>
    </div>
</div>

<div class="container contact-grid" style="padding: 3rem 0; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
    <div>
        <h2>Get in Touch</h2>
        <?php
        $flash = getFlash();
        if ($flash) {
            echo '<div class="alert alert-' . sanitize($flash['type']) . '">' . sanitize($flash['message']) . '</div>';
        }
        ?>
        <form action="contact.php" method="POST" onsubmit="return validateContactForm(this)">
            <div class="form-group">
                <label class="form-label" for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="subject">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="message">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
    
    <div>
        <h2>Contact Information</h2>
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-body">
                <p><strong>Address:</strong> 123 Rental Street, Kathmandu, Nepal</p>
                <p><strong>Phone:</strong> +977 1234567890</p>
                <p><strong>Email:</strong> info@vehiclerental.com</p>
                <p><strong>Business Hours:</strong></p>
                <ul>
                    <li>Sunday - Friday: 9:00 AM - 6:00 PM</li>
                    <li>Saturday: Closed</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function validateContactForm(form) {
    if(form.name.value.trim() === '' || form.email.value.trim() === '' || form.subject.value.trim() === '' || form.message.value.trim() === '') {
        alert('Please fill all the fields.');
        return false;
    }
    return true;
}
</script>

<?php require_once 'includes/footer.php'; ?>
