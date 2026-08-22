<?php
namespace App\Services;

final class DemoContent {
    public static function services(): array {return [
        ['id'=>1,'title'=>'eBook Writing & Ghostwriting','slug'=>'ebook-writing','summary'=>'Turn a promising idea into a clear, compelling manuscript.','description'=>'We help clients develop an idea, establish a strong voice and complete a polished manuscript that feels natural, useful and reader-ready.','benefits'=>"Idea development\nCollaborative ghostwriting\nClear chapter milestones\nEditorial direction",'timeline'=>'8-16 weeks','starting_price'=>1500,'display_order'=>1,'is_active'=>1],
        ['id'=>2,'title'=>'eBook Editing & Manuscript Polish','slug'=>'ebook-editing','summary'=>'Give your draft clearer structure, stronger flow and cleaner language.','description'=>'We review your manuscript for structure, clarity, tone and reader experience, then polish the writing so the finished eBook feels professional.','benefits'=>"Developmental review\nLine editing and polish\nReader-focused revisions",'timeline'=>'2-8 weeks','starting_price'=>500,'display_order'=>2,'is_active'=>1],
        ['id'=>3,'title'=>'Business & Thought Leadership eBooks','slug'=>'business-thought-leadership-ebooks','summary'=>'Turn frameworks, training material or specialist knowledge into a useful authority-building eBook.','description'=>'We help founders, consultants and experts convert their ideas into structured, persuasive eBooks for clients, audiences and professional credibility.','benefits'=>"Idea extraction\nAuthority positioning\nClear chapter architecture",'timeline'=>'6-14 weeks','starting_price'=>1500,'display_order'=>3,'is_active'=>1],
        ['id'=>4,'title'=>'Memoir & Personal Story Writing','slug'=>'memoir-personal-story-writing','summary'=>'Shape lived experience into a compelling, respectful narrative.','description'=>'We help clients organize memories, interviews and personal material into a clear story while protecting the voice and emotional truth of the author.','benefits'=>"Story interviews\nNarrative structure\nSensitive editorial care",'timeline'=>'8-18 weeks','starting_price'=>1500,'display_order'=>4,'is_active'=>1],
        ['id'=>5,'title'=>'eBook Formatting & Publishing Preparation','slug'=>'ebook-formatting-publishing-preparation','summary'=>'Prepare a completed manuscript for a polished digital reading experience.','description'=>'After writing and editing, we help prepare the file structure, formatting and finishing details needed for the next publishing step.','benefits'=>"Clean formatting\nFront and back matter guidance\nPublishing-ready preparation",'timeline'=>'1-3 weeks','starting_price'=>350,'display_order'=>5,'is_active'=>1],
        ['id'=>6,'title'=>'Author Brand & Book Positioning','slug'=>'author-brand-book-positioning','summary'=>'Clarify what the eBook should say, who it serves and how it supports your larger goals.','description'=>'We help connect the book idea with your audience, expertise and brand so the project has a clear purpose before and after publication.','benefits'=>"Audience clarity\nPositioning strategy\nBook promise refinement",'timeline'=>'1-4 weeks','starting_price'=>500,'display_order'=>6,'is_active'=>1],
    ];}

    public static function authors(): array {return [
        ['name'=>'Aisha Morgan','slug'=>'aisha-morgan','bio'=>'A leadership coach who wanted her workshop framework shaped into a concise eBook for clients and speaking audiences.','tagline'=>'Leadership and coaching','website'=>''],
        ['name'=>'Daniel Reyes','slug'=>'daniel-reyes','bio'=>'A founder with a practical business philosophy that needed structure, editorial clarity and a confident long-form voice.','tagline'=>'Business strategy','website'=>''],
        ['name'=>'Maya Chen','slug'=>'maya-chen','bio'=>'A wellness educator whose notes and session material were developed into an approachable reader-first guide.','tagline'=>'Wellness education','website'=>''],
        ['name'=>'Omar Haleem','slug'=>'omar-haleem','bio'=>'A first-time author who brought a memoir concept, interview notes and family history to be shaped into a coherent manuscript plan.','tagline'=>'Personal story and memoir','website'=>''],
        ['name'=>'Priya Kapoor','slug'=>'priya-kapoor','bio'=>'A consultant whose client education system needed to become a practical eBook with a confident advisory voice.','tagline'=>'Client education and consulting','website'=>''],
        ['name'=>'Marcus Bennett','slug'=>'marcus-bennett','bio'=>'A speaker and trainer who wanted his signature framework shaped into a readable eBook for event audiences and prospects.','tagline'=>'Speaking and training','website'=>''],
    ];}

    public static function posts(): array {return [
        ['title'=>'How to Turn an Idea Into an eBook Outline','slug'=>'turn-idea-into-ebook-outline','excerpt'=>'A strong eBook starts with a promise, a reader and a structure before it becomes a manuscript.','body'=>'Begin by naming the reader and the change the book should create for them. Collect your notes, stories and examples, then group them into a simple chapter journey. A clear outline gives the writing room to breathe.','published_at'=>'2026-01-15 09:00:00'],
        ['title'=>'What a Professional eBook Writer Actually Does','slug'=>'professional-ebook-writer-role','excerpt'=>'The best writing support turns raw expertise into a manuscript that still sounds like the client.','body'=>'A professional writer listens for the client’s voice, organizes the material, asks better questions and keeps the reader’s experience in view. The goal is not to replace the author. The goal is to make the author’s knowledge readable, useful and complete.','published_at'=>'2026-02-04 09:00:00'],
        ['title'=>'Preparing Your Material Before You Request a Quote','slug'=>'prepare-material-before-quote','excerpt'=>'You do not need a finished manuscript, but a few clear details help us recommend the right scope.','body'=>'Helpful starting material can include a short project summary, audience notes, chapter ideas, voice recordings, workshop slides, articles, transcripts or a partial draft. Even rough material is useful when the purpose of the eBook is clear.','published_at'=>'2026-03-01 09:00:00'],
    ];}

    public static function testimonials(): array {return [
        ['name'=>'Aisha N.','role'=>'Leadership coach','quote'=>'Archon turned my scattered workshop material into a book that finally felt clear and complete.'],
        ['name'=>'M. Carter','role'=>'Consultant','quote'=>'The writing process was calm, structured and deeply respectful of my voice.'],
        ['name'=>'Leah D.','role'=>'First-time author','quote'=>'I arrived with notes and a nervous idea. Archon helped me see the shape of the whole book.'],
        ['name'=>'R. Stone','role'=>'Founder','quote'=>'An unusually thoughtful writing partner from the first conversation through the final manuscript.'],
        ['name'=>'Nadia F.','role'=>'Course creator','quote'=>'They turned years of lessons and worksheets into a book my clients can actually use.'],
        ['name'=>'Thomas K.','role'=>'Executive coach','quote'=>'The finished manuscript sounded like me, only clearer, sharper and far better organized.'],
    ];}

    public static function faqs(string $title): array {return [
        ['question'=>'Is '.$title.' tailored to my project?','answer'=>'Yes. We begin with a conversation about your idea, goals, audience, source material and preferred timeline before recommending a clear scope.'],
        ['question'=>'Do I need a finished manuscript before contacting Archon?','answer'=>'No. Many clients begin with an idea, outline, voice notes, training material, interviews or a partial draft.'],
        ['question'=>'How will we work together?','answer'=>'You receive an agreed schedule, straightforward milestones and a single point of contact throughout the writing or editorial process.'],
    ];}
}
