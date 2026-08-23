<?php
use App\Core\Security;

$activeAdmin = 'book-contact';
$email = $settings['book.contact.email'] ?? 'hello@archonpublishinghouse.com';
$phone = $settings['book.contact.phone'] ?? '+1 (555) 014-2026';
?>
<section class="admin-shell">
    <?php require dirname(__DIR__).'/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow">PUBLIC BOOK SETTINGS</p>
        <h1>Book contact details</h1>

        <section class="panel">
            <div class="crm-heading">
                <div>
                    <p class="eyebrow">CONTACT PAGE INSIDE BOOK</p>
                    <h2>Email and phone</h2>
                </div>
            </div>
            <p>These values appear on the contact chapter inside the interactive book. Use the public email and phone/WhatsApp number you want visitors to see.</p>

            <form method="post" action="/admin/book-contact" class="admin-form">
                <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                <label>Public email address
                    <input name="book_contact_email" type="email" value="<?=Security::e($email)?>" required>
                </label>
                <label>Public phone / WhatsApp
                    <input name="book_contact_phone" value="<?=Security::e($phone)?>" required>
                </label>
                <button class="button">Save book contact</button>
                <a class="button outline" href="/">View website</a>
            </form>
        </section>
    </main>
</section>
