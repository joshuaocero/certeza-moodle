<?php
/**
 * Creates "The Four Main Points of the Gospel"
 * Run: docker exec -i moodle-app php < setup_gospel_course.php
 */
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/lib/testing/generator/lib.php');

$generator = new testing_data_generator();

// ── Course ────────────────────────────────────────────────────────────────
echo "Creating course...\n";
$course = $generator->create_course([
    'fullname'         => 'The Four Main Points of the Gospel',
    'shortname'        => 'GOSPEL101',
    'category'         => 1,
    'summary'          => '<p>A clear, concise walk through the four essential truths of the Christian Gospel — GOD, MAN, CHRIST, and RESPONSE. Open to all, no account required.</p>',
    'summaryformat'    => FORMAT_HTML,
    'format'           => 'topics',
    'numsections'      => 4,
    'enablecompletion' => 1,
    'visible'          => 1,
    'startdate'        => time(),
]);
echo "Course ID: {$course->id}\n";

// ── Guest access (no login required) ─────────────────────────────────────
$guestplugin = enrol_get_plugin('guest');
$guestplugin->add_instance($course, ['status' => ENROL_INSTANCE_ENABLED, 'password' => '']);
echo "Guest access enabled.\n";

// ── Helpers ───────────────────────────────────────────────────────────────
function set_section(stdClass $course, int $num, string $name, string $summary = ''): void {
    global $DB;
    $s = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $num]);
    if ($s) {
        $s->name = $name;
        $s->summary = $summary;
        $s->summaryformat = FORMAT_HTML;
        $DB->update_record('course_sections', $s);
    }
}

function add_page(testing_data_generator $g, stdClass $course, int $section, string $name, string $html): stdClass {
    return $g->create_module('page', [
        'course'        => $course->id,
        'section'       => $section,
        'name'          => $name,
        'intro'         => '',
        'introformat'   => FORMAT_HTML,
        'content'       => $html,
        'contentformat' => FORMAT_HTML,
        'visible'       => 1,
        'completion'    => COMPLETION_TRACKING_MANUAL,
    ]);
}

function add_label(testing_data_generator $g, stdClass $course, int $section, string $html): stdClass {
    return $g->create_module('label', [
        'course'      => $course->id,
        'section'     => $section,
        'name'        => 'label',
        'intro'       => $html,
        'introformat' => FORMAT_HTML,
    ]);
}

function card(string $title, string $body, string $color = '#4f46e5'): string {
    return "<div style='background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid {$color};border-radius:8px;padding:16px;margin:12px 0;'>
        <h4 style='margin:0 0 8px;color:{$color};'>{$title}</h4><p style='margin:0;'>{$body}</p></div>";
}

function quote(string $text, string $ref, string $color = '#4f46e5'): string {
    $bg = ($color === '#10b981') ? '#f0fdf4' : '#f1f5f9';
    return "<blockquote style='border-left:4px solid {$color};padding:12px 20px;background:{$bg};border-radius:4px;margin:16px 0;'>
        <em>\"{$text}\"</em><br><strong>— {$ref}</strong></blockquote>";
}

function reflect(array $questions): string {
    $items = implode('', array_map(fn($q) => "<li>{$q}</li>", $questions));
    return "<div style='background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:16px;margin-top:20px;'>
        <h4 style='margin:0 0 8px;color:#92400e;'>&#x270F;&#xFE0F; Reflection Questions</h4><ol style='margin:0;padding-left:20px;'>{$items}</ol></div>";
}

function main_point(string $text): string {
    return "<div style='background:#e0e7ff;border:1px solid #c7d2fe;border-radius:8px;padding:16px 20px;margin-top:24px;'>
        <p style='margin:0;color:#3730a3;font-weight:600;'>&#x1F4CC; Main Point: {$text}</p></div>";
}

course_create_sections_if_missing($course, range(0, 4));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 0 — Course Introduction
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 0, 'Course Introduction');

add_label($generator, $course, 0,
    "<div style='background:#e0e7ff;border-left:4px solid #4f46e5;border-radius:8px;padding:20px 24px;'>
     <h2 style='color:#3730a3;margin:0 0 8px;'>The Four Main Points of the Gospel</h2>
     <p style='margin:0;color:#1e293b;'>A clear walk through the four essential truths at the heart of the Christian message — GOD, MAN, CHRIST, and RESPONSE.
     Open to all. No account required. Work at your own pace.</p></div>");

add_page($generator, $course, 0, 'Welcome & How to Use This Course',
    "<h2>Welcome</h2>
    <p>This course presents the Gospel — the good news of Jesus Christ — in four clear, connected points. Together they tell the story of who GOD is, what went wrong, what GOD did about it, and how we should respond.</p>
    <h3>Structure</h3>
    <ul>
      <li><strong>4 Sections</strong> — one for each point of the Gospel</li>
      <li><strong>Teaching</strong> — clear explanation of each truth</li>
      <li><strong>Key Scriptures</strong> — the Bible's own words on each point</li>
      <li><strong>Main Point</strong> — a one-sentence summary to anchor each section</li>
    </ul>
    <h3>Tips for Study</h3>
    <ol>
      <li>Have a Bible open alongside this course (BibleGateway.com works well)</li>
      <li>Read slowly — let each truth settle before moving on</li>
      <li>Engage with the reflection questions honestly</li>
      <li>Click <strong>Mark as done</strong> on each page as you complete it</li>
    </ol>"
    . quote("For I am not ashamed of the gospel, for it is the power of God for salvation to everyone who believes.", "Romans 1:16", "#10b981"));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 1 — GOD: GOD Created Us and Loves Us
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 1, 'GOD &#8212; GOD Created Us and Loves Us',
    '<p>The Gospel begins with GOD. Before we can understand salvation, we must first understand who GOD is and why He created us.</p>');

add_page($generator, $course, 1, 'GOD — GOD Created Us and Loves Us',
    "<h2>The Gospel Begins with GOD</h2>
    <p>Before we can understand salvation, we must first understand who GOD is and why He created us.</p>
    <p>GOD is holy, loving, righteous, and the Creator of all things. He made humanity in His image to know Him, worship Him, enjoy fellowship with Him, and glorify Him forever. GOD did not create us by accident or for meaningless existence. He created us with purpose — to live in relationship with Him.</p>
    <p>GOD's design was perfect. In the beginning, mankind lived in harmony with GOD, enjoying His presence without shame, fear, or separation.</p>

    <h3>Key Scriptures</h3>"
    . quote("So God created man in His own image; in the image of God He created him; male and female He created them.", "Genesis 1:27")
    . quote("You are worthy, O Lord, to receive glory and honor and power; for You created all things, and by Your will they exist and were created.", "Revelation 4:11")
    . quote("The LORD has appeared of old to me, saying: 'Yes, I have loved you with an everlasting love; therefore with lovingkindness I have drawn you.'", "Jeremiah 31:3", "#10b981")
    . main_point("GOD created us to know Him, love Him, and live in relationship with Him.")
    . reflect([
        "What does it mean to you that you were made in the image of GOD?",
        "How does knowing that GOD created you on purpose — not by accident — change the way you see your own life?",
        "What does an 'everlasting love' (Jeremiah 31:3) tell you about GOD's commitment to you personally?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 2 — MAN: Sin Separated Us from GOD
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 2, 'MAN &#8212; Sin Separated Us from GOD',
    '<p>Although GOD created humanity for fellowship with Him, mankind rebelled through sin — with serious consequences.</p>');

add_page($generator, $course, 2, 'MAN — Sin Separated Us from GOD',
    "<h2>What Went Wrong</h2>
    <p>Although GOD created humanity for fellowship with Him, mankind rebelled against GOD through sin. Sin is disobedience to GOD — choosing our own way instead of GOD's way.</p>
    <p>From the fall of Adam and Eve onward, sin entered the world and affected every human being. We are not sinners merely because we sin; rather, we sin because we are sinners by nature.</p>
    <p>The Bible teaches that every person has sinned. No one is morally perfect or righteous before GOD. Our good deeds, religious activity, morality, or efforts cannot erase sin or make us acceptable to a holy GOD.</p>
    <p>Sin has serious consequences. It separates us from GOD spiritually and ultimately leads to eternal judgment. Left to ourselves, we are unable to save ourselves.</p>

    <h3>Key Scriptures</h3>"
    . quote("For all have sinned and fall short of the glory of God.", "Romans 3:23")
    . quote("For the wages of sin is death, but the gift of God is eternal life in Christ Jesus our Lord.", "Romans 6:23")
    . quote("But your iniquities have separated you from your God; and your sins have hidden His face from you, so that He will not hear.", "Isaiah 59:2", "#10b981")
    . main_point("Our sin has separated us from GOD, and we cannot save ourselves.")
    . reflect([
        "Romans 3:23 says <em>all</em> have sinned — including you and me. How does that truth sit with you honestly?",
        "What are the \"wages\" of sin, and what does it mean that sin leads to death — spiritually, not just physically?",
        "If we cannot save ourselves, what hope do we have? What would need to be true for there to be a way out?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 3 — CHRIST: JESUS Died and Rose Again from the Dead
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 3, 'CHRIST &#8212; JESUS Died and Rose Again from the Dead',
    '<p>Because GOD loves us, He did not leave us without hope. GOD provided the solution through His Son, JESUS CHRIST.</p>');

add_page($generator, $course, 3, 'CHRIST — JESUS Died and Rose Again from the Dead',
    "<h2>GOD's Solution</h2>
    <p>Because GOD loves us, He did not leave us without hope. GOD provided the solution to our greatest problem through His Son, JESUS CHRIST.</p>
    <p>JESUS CHRIST is fully GOD and fully man. He lived a perfectly sinless life — the life we could never live. He willingly died on the cross as a substitute for sinners, taking upon Himself the punishment that we deserved.</p>
    <p>Three days later, JESUS rose from the dead, proving His victory over sin, death, and Satan. Through His death and resurrection, forgiveness and reconciliation with GOD are now available to all who believe.</p>
    <p>Salvation is not earned through good works, religious performance, or human effort. It is made possible only through JESUS CHRIST.</p>

    <h3>Key Scriptures</h3>"
    . quote("For God so loved the world that He gave His only begotten Son, that whoever believes in Him should not perish but have everlasting life.", "John 3:16")
    . quote("But God demonstrates His own love toward us, in that while we were still sinners, Christ died for us.", "Romans 5:8")
    . quote("For I delivered to you first of all that which I also received: that Christ died for our sins according to the Scriptures, and that He was buried, and that He rose again the third day according to the Scriptures.", "1 Corinthians 15:3&ndash;4", "#10b981")
    . main_point("JESUS did for us what we could never do for ourselves — He paid for our sins and conquered death.")
    . reflect([
        "Romans 5:8 says Christ died for us 'while we were still sinners' — before we changed or even asked. What does that tell you about GOD's love?",
        "What does it mean that Jesus lived the sinless life we couldn't live, and died the death we deserved?",
        "The resurrection is the proof that everything Jesus claimed is true. How does the historical reality of the resurrection affect your view of Jesus?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 4 — RESPONSE: We Must Repent and Believe
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 4, 'RESPONSE &#8212; We Must Repent and Believe',
    '<p>The Gospel demands a personal response. It is not enough merely to know facts about JESUS. Each person must respond to Him personally.</p>');

add_page($generator, $course, 4, 'RESPONSE — We Must Repent and Believe',
    "<h2>Your Personal Response</h2>
    <p>The Gospel demands a personal response. It is not enough merely to know facts about JESUS. Each person must respond to Him personally.</p>
    <p>The Bible calls us to <strong>repent and believe</strong>. Repentance means turning away from sin and turning toward GOD. Faith means trusting completely in JESUS CHRIST alone for forgiveness and salvation — not in our own goodness, religion, or works.</p>
    <p>When a person repents and places their faith in CHRIST, GOD forgives their sins, gives them eternal life, adopts them into His family, and begins transforming them from the inside out.</p>
    <p>Following JESUS is not merely a one-time decision; it is the beginning of a lifelong relationship with Him.</p>

    <h3>Key Scriptures</h3>"
    . quote("The time is fulfilled, and the kingdom of God is at hand. Repent, and believe in the gospel.", "Mark 1:15")
    . quote("For by grace you have been saved through faith, and that not of yourselves; it is the gift of God, not of works, lest anyone should boast.", "Ephesians 2:8&ndash;9")
    . quote("That if you confess with your mouth the Lord Jesus and believe in your heart that God has raised Him from the dead, you will be saved.", "Romans 10:9", "#10b981")
    . main_point("We must personally respond to JESUS through repentance and faith.")
    . reflect([
        "Repentance means a genuine turning — not just feeling sorry, but changing direction. Is there anything you need to turn away from in order to turn toward GOD?",
        "Ephesians 2:8–9 says salvation is a gift, not earned. Does that feel too good to be true? Why or why not?",
        "What would it look like for you personally to place your trust in JESUS CHRIST right now?"
    ])
    . "<div style='background:#f0fdf4;border:1px solid #6ee7b7;border-radius:12px;padding:24px;margin-top:32px;text-align:center;'>
        <h3 style='color:#065f46;margin-top:0;'>A Prayer of Response</h3>
        <p style='color:#1e293b;font-style:italic;margin-bottom:0;'>\"GOD, I know that I am a sinner and that my sin separates me from You. I believe that JESUS CHRIST died for my sins and rose from the dead. I turn from my sin and place my trust in JESUS alone as my Savior and Lord. Thank You for forgiving me and giving me eternal life. Help me to follow You every day. Amen.\"</p>
    </div>");

// ═════════════════════════════════════════════════════════════════════════
// Course Conclusion Page (in Section 4)
// ═════════════════════════════════════════════════════════════════════════
add_page($generator, $course, 4, 'Course Conclusion: The Gospel Summary',
    "<h2>The Four Points — Reviewed</h2>
    <p>You have now walked through the four essential truths of the Gospel:</p>"
    . card('1. GOD', 'GOD created us and loves us. He made us in His image to know Him, love Him, and live in relationship with Him.', '#4f46e5')
    . card('2. MAN', 'Sin separated us from GOD. Every person has sinned and fallen short of GOD\'s glory, and we cannot save ourselves.', '#dc2626')
    . card('3. CHRIST', 'JESUS died and rose again from the dead. He paid for our sins and conquered death so that we could be forgiven and reconciled to GOD.', '#d97706')
    . card('4. RESPONSE', 'We must repent and believe. Each person must personally turn from sin and place their trust in JESUS CHRIST alone.', '#10b981')
    . "<h3 style='margin-top:28px;'>The Most Important Question</h3>"
    . quote("What will you do with JESUS?", "Matthew 16:15 (paraphrase)")
    . "<p>The four points are only good news if you respond to them. GOD has done everything necessary to reconcile you to Himself through JESUS CHRIST. The invitation is open to you right now.</p>
    <h3>Suggested Next Steps</h3>
    <ul>
      <li>&#x1F4D6; <strong>Read the Gospel of John</strong> — the clearest presentation of who Jesus is and why He came</li>
      <li>&#x1F64F; <strong>Respond to JESUS directly</strong> — He is alive and hears your prayer</li>
      <li>&#x1F91D; <strong>Find a local church</strong> — connect with believers who can walk with you in your new faith</li>
      <li>&#x1F4DA; <strong>Continue studying</strong> — explore Romans for a deep dive into the Gospel's theology</li>
    </ul>
    <div style='background:#e0e7ff;border-radius:12px;padding:24px;margin-top:24px;text-align:center;'>
      <h3 style='color:#3730a3;margin-top:0;'>Thank you for taking this course.</h3>
      <p style='margin:0;color:#1e293b;'>May these four truths take root in your heart and bear lasting fruit for eternity.</p>
    </div>");

// ═════════════════════════════════════════════════════════════════════════
// Tracking JavaScript
// ═════════════════════════════════════════════════════════════════════════
$tracking_js = <<<'JS'
<!-- Certeza Progress Tracker -->
<script>
(function () {
    var ENDPOINT  = 'https://your-api.example.com/track'; // ← REPLACE with your endpoint
    var COURSE    = 'GOSPEL101';
    var UID_KEY   = 'uid';

    function getUID() {
        var p = new URLSearchParams(window.location.search);
        var uid = p.get(UID_KEY);
        if (uid) { sessionStorage.setItem('_trk_uid', uid); return uid; }
        return sessionStorage.getItem('_trk_uid');
    }

    function send(event, extra) {
        var uid = getUID();
        if (!uid || ENDPOINT.indexOf('your-api') !== -1) return;
        var payload = Object.assign({
            uid: uid, course: COURSE, event: event,
            url: window.location.pathname,
            cmid: new URLSearchParams(window.location.search).get('id'),
            ts: Date.now()
        }, extra || {});
        try {
            if (navigator.sendBeacon) {
                navigator.sendBeacon(ENDPOINT, new Blob([JSON.stringify(payload)], {type:'application/json'}));
            } else {
                fetch(ENDPOINT, {method:'POST', body:JSON.stringify(payload),
                    headers:{'Content-Type':'application/json'}}).catch(function(){});
            }
        } catch(e){}
    }

    function propagate() {
        var uid = getUID(); if (!uid) return;
        document.querySelectorAll('a[href]').forEach(function(a) {
            try {
                if (a.href.indexOf(window.location.hostname) === -1) return;
                var u = new URL(a.href);
                if (!u.searchParams.has(UID_KEY)) { u.searchParams.set(UID_KEY, uid); a.href = u.toString(); }
            } catch(e){}
        });
    }

    send('page_view', {title: document.title});

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-action="toggle-manual-completion"], .btn-completionmanual');
        if (btn) send('activity_complete', {
            cmid:  btn.getAttribute('data-cmid'),
            label: btn.getAttribute('aria-label') || btn.innerText.trim()
        });
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', propagate);
    } else { propagate(); }
    new MutationObserver(propagate).observe(document.documentElement, {childList:true, subtree:true});
})();
</script>
JS;

set_config('additionalhtmlfooter', $tracking_js);
echo "Tracking JS injected.\n";

rebuild_course_cache($course->id, true);

echo "\n✓ Done!\n";
echo "Course URL : http://localhost:8080/course/view.php?id={$course->id}\n";
echo "With tracking: http://localhost:8080/course/view.php?id={$course->id}&uid=PARTICIPANT_ID\n";
echo "\nNext: replace 'https://your-api.example.com/track' in\n";
echo "Site Admin → Appearance → Additional HTML → Before closing </body>\n";
