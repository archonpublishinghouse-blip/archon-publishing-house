<section class="page-hero">
    <p class="eyebrow">START YOUR EBOOK</p>
    <h1>Tell us what you want to <em>bring to life.</em></h1>
    <p>Use this form to share your idea, outline, manuscript or business concept. We will review it and suggest the right writing path.</p>
</section>

<section class="section">
    <form method="post" action="/quote" enctype="multipart/form-data" class="panel quote-form">
        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
        <?php if (!empty($selectedPackage)): ?>
            <input type="hidden" name="package_id" value="<?=Security::e($selectedPackage['id'])?>">
            <p><strong>Selected package: <?=Security::e($selectedPackage['name'])?></strong><br><?=Security::e($selectedPackage['price'])?> &mdash; <?=Security::e($selectedPackage['tagline'])?></p>
            <p><a class="text-link" href="/#writing-packages">Compare packages</a> &middot; <a class="text-link" href="/quote">Request a custom quote instead</a></p>
        <?php endif; ?>
        <div class="form-grid">
            <label>Full name<input name="name" required></label>
            <label>Email address<input name="email" type="email" required></label>
            <label>Phone / WhatsApp<input name="phone"></label>
            <label>Service<select name="service_id"><option value="">Select a service</option><?php foreach($services as $service):?><option value="<?=$service['id']?>"><?=Security::e($service['title'])?></option><?php endforeach;?></select></label>
            <label>Working title<input name="book_title" placeholder="Optional"></label>
            <label>Genre or subject area<input name="genre" placeholder="Business, memoir, self-help, education..."></label>
            <label>Estimated word count<input name="word_count" inputmode="numeric" placeholder="If known"></label>
            <label>Project stage<select name="project_stage"><option>Idea</option><option>Outline or notes ready</option><option>Draft in progress</option><option>Draft complete</option><option>Needs editing or polish</option></select></label>
            <label>Ideal completion date<input type="date" name="completion_date"></label>
            <label>Budget range<select name="budget_range"><option>Not sure yet</option><option>Under $1,000</option><option>$1,000-$3,000</option><option>$3,000+</option></select></label>
        </div>
        <label>Project description<textarea name="description" rows="7" minlength="20" required placeholder="Tell us the purpose of the eBook, who it is for, and what material you already have."></textarea></label>
        <label>Optional outline, notes or manuscript sample (PDF, DOC or DOCX; maximum 10 MB)<input name="attachment" type="file" accept=".pdf,.doc,.docx"></label>
        <label class="consent"><input name="consent" type="checkbox" required> I consent to Archon using these details to review and discuss my request.</label>
        <button class="button">Request my consultation</button>
    </form>
</section>
