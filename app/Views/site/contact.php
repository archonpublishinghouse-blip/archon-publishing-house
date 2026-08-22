<section class="page-hero">
    <p class="eyebrow">LET’S TALK</p>
    <h1>Start a thoughtful <em>eBook conversation.</em></h1>
    <p>Have an idea, outline, manuscript or business concept? Share a few details and the Archon team will help you understand the next sensible step.</p>
</section>

<section class="section form-layout">
    <form method="post" action="/contact" class="panel">
        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
        <label>Full name<input name="name" required></label>
        <label>Email address<input name="email" type="email" required></label>
        <label>Phone / WhatsApp<input name="phone"></label>
        <label>Subject<input name="subject" required placeholder="eBook writing enquiry"></label>
        <label>How can we help?<textarea name="message" rows="6" required minlength="10" placeholder="Tell us what you want to write, who it is for, and where you are in the process."></textarea></label>
        <label class="consent"><input name="consent" type="checkbox" required> I consent to Archon using these details to reply to my enquiry.</label>
        <button class="button">Send message</button>
    </form>

    <aside>
        <p class="eyebrow">CONTACT</p>
        <h2>Project enquiries</h2>
        <p>For eBook writing, editing, structure, portfolio and consultation questions.</p>
        <p>Mon–Fri<br>09:00–17:00</p>
        <p>hello@archonpublishinghouse.com<br>+1 (555) 014-2026</p>
        <a class="button outline" href="/quote">Request a quote</a>
    </aside>
</section>
