<?php
/**
 * Creates "Foundations of Christianity: Book of John"
 * Run: docker exec -i moodle-app php < setup_john_course.php
 */
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/lib/testing/generator/lib.php');

$generator = new testing_data_generator();

// ── Course ────────────────────────────────────────────────────────────────
echo "Creating course...\n";
$course = $generator->create_course([
    'fullname'         => 'Foundations of Christianity: The Book of John',
    'shortname'        => 'JOHN101',
    'category'         => 1,
    'summary'          => '<p>A foundational journey through the Gospel of John — exploring who Jesus is, what He taught, and why it matters. No account required.</p>',
    'summaryformat'    => FORMAT_HTML,
    'format'           => 'topics',
    'numsections'      => 8,
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
        <h4 style='margin:0 0 8px;color:#92400e;'>✏️ Reflection Questions</h4><ol style='margin:0;padding-left:20px;'>{$items}</ol></div>";
}

course_create_sections_if_missing($course, range(0, 8));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 0 — Course Introduction
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 0, 'Course Introduction');

add_label($generator, $course, 0,
    "<div style='background:#e0e7ff;border-left:4px solid #4f46e5;border-radius:8px;padding:20px 24px;'>
     <h2 style='color:#3730a3;margin:0 0 8px;'>Foundations of Christianity: The Book of John</h2>
     <p style='margin:0;color:#1e293b;'>A verse-by-verse journey through the Gospel of John — one of the most profound books ever written.
     Open to all, no account required. Work at your own pace.</p></div>");

add_page($generator, $course, 0, 'How to Use This Course',
    "<h2>Welcome</h2>
    <p>This course guides you through the Gospel of John in 8 sections. Each section covers key passages with teaching, scripture, and reflection questions.</p>
    <h3>Structure</h3>
    <ul>
      <li><strong>8 Sections</strong> — each covering major chapters from John's Gospel</li>
      <li><strong>Content Pages</strong> — teaching with scripture and commentary</li>
      <li><strong>Reflection Questions</strong> — space to engage personally with the material</li>
    </ul>
    <h3>Tips for Study</h3>
    <ol>
      <li>Open a Bible alongside this course (BibleGateway.com works great)</li>
      <li>Read the referenced passages before each page</li>
      <li>Sit with the reflection questions — don't rush through them</li>
      <li>Click <strong>Mark as done</strong> on each page as you complete it</li>
    </ol>"
    . quote("These are written so that you may believe that Jesus is the Messiah, the Son of God, and that by believing you may have life in his name.", "John 20:31", "#10b981"));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 1 — Introduction to John's Gospel
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 1, "Introduction to John's Gospel",
    '<p>Who wrote this gospel, why, and what makes it unique among the four?</p>');

add_page($generator, $course, 1, 'About the Author & Purpose',
    "<h2>Who Wrote John's Gospel?</h2>
    <p><strong>John the Apostle</strong> — one of the Twelve, part of Jesus's inner circle alongside Peter and James. He refers to himself as <em>\"the disciple whom Jesus loved\"</em> — an expression of intimacy, not pride.</p>"
    . card('Written', 'Approximately AD 85–95, the latest of the four Gospels. Written from Ephesus (modern Turkey) for Gentile believers across the Roman world.')
    . card("John's Unique Approach", "Unlike Matthew, Mark, and Luke (the Synoptic Gospels), John includes no parables. Instead he offers extended discourses, seven miraculous signs, and deep theology focused on <em>who Jesus is</em> — the eternal Son of God.")
    . "<h3>The Purpose — In John's Own Words</h3>"
    . quote("But these are written so that you may believe that Jesus is the Messiah, the Son of God, and that by believing you may have life in his name.", "John 20:30–31")
    . "<p>Every passage, miracle, and conversation in John's Gospel serves this one goal: <strong>that you would believe and live.</strong></p>"
    . "<h3>Key Themes</h3>
    <ul>
      <li><strong>Believe</strong> — the word appears 98 times (more than any other NT book)</li>
      <li><strong>Light vs. Darkness</strong> — Jesus as the light that darkness cannot overcome</li>
      <li><strong>Eternal Life</strong> — offered to all who believe (John 3:16, 10:10, 17:3)</li>
      <li><strong>The I AM Statements</strong> — seven declarations linking Jesus to God's own name</li>
    </ul>"
    . reflect([
        "What draws you to study the Gospel of John at this point in your life?",
        "Which of the key themes above is most interesting to you right now, and why?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 2 — The Word Made Flesh (John 1)
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 2, 'The Word Made Flesh — John 1',
    '<p>John opens not with a birth narrative but with eternity — the most theologically dense prologue in all of Scripture.</p>');

add_page($generator, $course, 2, 'The Prologue: In the Beginning Was the Word',
    "<h2>John 1:1–18 — The Prologue</h2>"
    . quote("In the beginning was the Word, and the Word was with God, and the Word was God. He was with God in the beginning. Through him all things were made; without him nothing was made that has been made.", "John 1:1–3")
    . "<h3>What Is \"The Word\"?</h3>
    <p>John uses the Greek word <strong>Logos</strong> (λόγος). For Greek readers, Logos was the rational principle ordering the universe. For Jewish readers, the \"Word of God\" was God's creative power (Psalm 33:6). John declares this eternal, divine Word <em>became a human being</em>.</p>
    <h3>Three Staggering Claims</h3>
    <ol>
      <li><strong>The Word was eternal</strong> — \"In the beginning was the Word\" (pre-existence, before creation)</li>
      <li><strong>The Word was with God</strong> — distinct from the Father, yet in eternal relationship</li>
      <li><strong>The Word was God</strong> — fully divine, not a lesser or created being</li>
    </ol>
    <h3>The Incarnation</h3>"
    . quote("The Word became flesh and made his dwelling among us. We have seen his glory, the glory of the one and only Son, who came from the Father, full of grace and truth.", "John 1:14", "#10b981")
    . "<p>\"Made his dwelling\" — literally <em>tabernacled</em> among us. Just as God dwelt in Israel's Tabernacle, He now dwells in human flesh. The infinite entered the finite. The eternal entered time.</p>"
    . reflect([
        "What does it mean to you personally that God became a human being?",
        "John says Jesus is \"full of grace and truth.\" What happens when you have truth without grace, or grace without truth?"
    ]));

add_page($generator, $course, 2, 'John the Baptist & The First Disciples',
    "<h2>John 1:19–51</h2>
    <h3>John the Baptist's Testimony (v. 19–34)</h3>
    <p>When religious leaders ask John who he is, he clearly states what he is <em>not</em>: not the Messiah, not Elijah, not the Prophet. He is \"a voice crying in the wilderness\" — preparing the way.</p>
    <p>When Jesus walks by, John declares: <em>\"Look, the Lamb of God, who takes away the sin of the world!\"</em> (v. 29) — the most important introduction in history.</p>
    <h3>The First Disciples: Come and See (v. 35–51)</h3>
    <p>The pattern of discipleship in John 1 is beautifully simple: <strong>encounter → follow → invite others.</strong></p>
    <ul>
      <li><strong>Andrew</strong> finds his brother Simon: <em>\"We have found the Messiah.\"</em></li>
      <li><strong>Philip</strong> finds Nathanael: <em>\"Come and see.\"</em></li>
    </ul>"
    . quote("Before Philip called you, when you were under the fig tree, I saw you.", "John 1:48")
    . "<p>Jesus's knowledge of Nathanael before they had ever met produces immediate, wholehearted faith: <em>\"Rabbi, you are the Son of God! You are the King of Israel!\"</em></p>"
    . reflect([
        "Andrew's first act after meeting Jesus was to bring his brother. Who would you want to bring to \"come and see\" Jesus?",
        "Jesus saw Nathanael fully before they met. How does the idea of being completely known by God make you feel?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 3 — New Birth & Living Water (John 2–4)
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 3, 'New Birth & Living Water — John 2–4',
    '<p>The first miracle, a religious leader by night, and a conversation at a well that changed a village.</p>');

add_page($generator, $course, 3, 'Water into Wine & Cleansing the Temple',
    "<h2>John 2 — Signs and Zeal</h2>
    <h3>Water into Wine (v. 1–12) — The First Sign</h3>
    <p>At a wedding in Cana, the wine runs out — a serious social crisis. Jesus turns six stone water jars (used for Jewish purification) into the finest wine. Six jars × 20–30 gallons each = up to 180 gallons of superior wine.</p>
    <p>John calls this a <strong>sign</strong> — not just a miracle, but a window into a deeper reality. The abundance of fine wine echoes the messianic banquet promised in Isaiah 25:6. Jesus replaces religious ritual (purification jars) with joy.</p>
    <h3>Cleansing the Temple (v. 13–25)</h3>
    <p>Jesus drives out merchants and money-changers from the Temple court with a whip of cords. When challenged for his authority, He says:</p>"
    . quote("Destroy this temple, and in three days I will raise it up.", "John 2:19")
    . "<p>The crowd thinks He means the physical Temple (46 years under construction). John tells us He was speaking of His body — a claim the disciples only understood after the resurrection.</p>"
    . reflect([
        "What does the abundance of wine at Cana tell you about the kind of life Jesus offers?",
        "Jesus showed fierce, righteous zeal for God's house. What does this reveal about His character?"
    ]));

add_page($generator, $course, 3, 'Born Again: Nicodemus (John 3)',
    "<h2>John 3:1–21 — You Must Be Born Again</h2>
    <p>Nicodemus was a Pharisee and member of the Sanhedrin — Israel's ruling council. He came to Jesus at night, perhaps to avoid scrutiny from his colleagues.</p>
    <p>Before Nicodemus can even ask a question, Jesus cuts to the core:</p>"
    . quote("Very truly I tell you, no one can see the kingdom of God unless they are born again.", "John 3:3")
    . "<p>Nicodemus takes this literally — can a man re-enter his mother's womb? Jesus explains: this is a spiritual birth, from above, initiated by God's Spirit — not achieved by human effort or religious performance.</p>
    <h3>The Most Famous Verse</h3>"
    . quote("For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.", "John 3:16", "#10b981")
    . "<p>Four movements: <strong>God's love</strong> (motivation) → <strong>His gift</strong> (the Son) → <strong>the condition</strong> (believe) → <strong>the result</strong> (eternal life). The whole gospel in one sentence.</p>"
    . reflect([
        "What is the difference between being \"born again\" spiritually and simply trying harder to be a better person?",
        "John 3:16 says God loved <em>the world</em> — including people you find difficult. How does that challenge you?"
    ]));

add_page($generator, $course, 3, 'Living Water: The Woman at the Well (John 4)',
    "<h2>John 4:1–42 — Come, See a Man</h2>
    <p>Jesus travels through Samaria — a region Jews typically avoided due to centuries of ethnic and religious hostility. He stops at Jacob's Well around noon and meets a Samaritan woman drawing water alone.</p>
    <p>Three boundaries Jesus crosses to speak with her: <strong>ethnic</strong> (Jew/Samaritan), <strong>gender</strong> (a Jewish man addressing a woman alone), and <strong>moral</strong> (she has had five husbands).</p>
    <h3>Living Water</h3>"
    . quote("Everyone who drinks this water will be thirsty again, but whoever drinks the water I give them will never thirst. Indeed, the water I give them will become in them a spring of water welling up to eternal life.", "John 4:13–14")
    . "<h3>True Worship</h3>
    <p>When the woman deflects to theology (which mountain is correct for worship?), Jesus redirects:</p>"
    . quote("God is spirit, and his worshipers must worship in the Spirit and in truth.", "John 4:24")
    . "<h3>The First Evangelist</h3>
    <p>The woman returns to her village and tells everyone: <em>\"Come, see a man who told me everything I ever did. Could this be the Messiah?\"</em> Many believe. She becomes one of the earliest and most effective witnesses in John's Gospel.</p>"
    . reflect([
        "What \"wells\" do people return to again and again — hoping for lasting satisfaction — but leave thirsty?",
        "What does it mean to worship God \"in Spirit and in truth\" in your own life?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 4 — The Great "I AM" Statements
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 4, 'The Great "I AM" Statements',
    '<p>Seven declarations of Jesus connecting His identity directly to the God of the Old Testament.</p>');

add_page($generator, $course, 4, 'Introduction to the I AM Statements',
    "<h2>\"I AM\" — The Divine Name</h2>
    <p>When God appeared to Moses in the burning bush and Moses asked His name, God replied:</p>"
    . quote("I AM WHO I AM. Say this to the people of Israel: I AM has sent me to you.", "Exodus 3:14", "#10b981")
    . "<p>The Hebrew name <em>YHWH</em> — translated \"LORD\" — carries the meaning of absolute self-existence: the one who simply <em>is</em>. In John's Gospel, Jesus makes seven declarations beginning with <strong>\"I AM\"</strong> (Greek: <em>egō eimi</em>), directly connecting Himself to this divine name.</p>
    <div style='background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:16px 0;'>
      <h3 style='margin-top:0;color:#4f46e5;'>The Seven I AM Statements</h3>
      <ol>
        <li><strong>The Bread of Life</strong> — John 6:35</li>
        <li><strong>The Light of the World</strong> — John 8:12</li>
        <li><strong>The Gate</strong> — John 10:7</li>
        <li><strong>The Good Shepherd</strong> — John 10:11</li>
        <li><strong>The Resurrection and the Life</strong> — John 11:25</li>
        <li><strong>The Way, the Truth, and the Life</strong> — John 14:6</li>
        <li><strong>The True Vine</strong> — John 15:1</li>
      </ol>
    </div>
    <p>Each statement addresses a fundamental human need. Together they form a complete portrait of who Jesus is and what He provides.</p>"
    . reflect([
        "Before reading further, which of the seven statements is most familiar to you? Which is least?",
        "Why do you think Jesus described Himself using images from everyday life — bread, light, a shepherd, a vine?"
    ]));

add_page($generator, $course, 4, 'Bread of Life & Light of the World (John 6, 8)',
    "<h2>I AM the Bread of Life — John 6:35</h2>"
    . quote("I am the bread of life. Whoever comes to me will never go hungry, and whoever believes in me will never be thirsty.", "John 6:35")
    . "<p>After feeding 5,000 people, the crowd follows Jesus hoping for more food. He redirects them: the miracle was a <em>sign</em> pointing to a deeper reality. Just as God gave Israel manna in the wilderness, He now provides Jesus — the true bread from heaven that sustains eternal life.</p>
    <hr style='margin:24px 0;border-color:#e2e8f0;'>
    <h2>I AM the Light of the World — John 8:12</h2>"
    . quote("I am the light of the world. Whoever follows me will never walk in darkness, but will have the light of life.", "John 8:12")
    . "<p>In John's framework, light represents truth, revelation, and life. Darkness represents sin, deception, and death. Jesus claims to be the ultimate source of illumination for human existence — not just one light among many, but <em>the</em> light.</p>
    <p>This claim is immediately demonstrated in John 9, where Jesus heals a man born blind — giving physical sight as a sign of the spiritual sight He offers.</p>"
    . reflect([
        "Where in your life do you feel the most spiritual hunger — longing for something that doesn't seem to satisfy?",
        "What would it look like practically to \"walk in the light\" rather than darkness in your daily life?"
    ]));

add_page($generator, $course, 4, 'Good Shepherd, Resurrection, Way & True Vine (John 10, 11, 14, 15)',
    "<h2>I AM the Good Shepherd — John 10:11</h2>"
    . quote("I am the good shepherd. The good shepherd lays down his life for the sheep.", "John 10:11")
    . "<p>Unlike a hired hand who flees when wolves come, the Good Shepherd risks — and lays down — His own life for the sheep. This is a direct preview of the cross.</p>
    <h2>I AM the Resurrection and the Life — John 11:25</h2>"
    . quote("I am the resurrection and the life. The one who believes in me will live, even though they die; and whoever lives by believing in me will never die.", "John 11:25")
    . "<p>Spoken at the tomb of Lazarus. Jesus doesn't just <em>promise</em> resurrection — He <em>is</em> resurrection. He immediately proves it by raising Lazarus from four days of death.</p>
    <h2>I AM the Way, the Truth, and the Life — John 14:6</h2>"
    . quote("I am the way and the truth and the life. No one comes to the Father except through me.", "John 14:6")
    . "<p>On the night before His crucifixion, Thomas asks how they can know the way. Jesus's answer is not a map or a method — it is a Person. He Himself is the path to the Father.</p>
    <h2>I AM the True Vine — John 15:1</h2>"
    . quote("I am the true vine, and my Father is the gardener... Remain in me, as I also remain in you. No branch can bear fruit by itself; it must remain in the vine.", "John 15:1, 4")
    . reflect([
        "What does it mean to you that Jesus is the \"Good Shepherd\" who lays down His life specifically for you?",
        "Jesus says He is \"the Way\" — not one option among many. How do you respond to this exclusive claim?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 5 — Lazarus & the Hour Has Come (John 11–12)
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 5, 'Lazarus & The Hour Has Come — John 11–12',
    '<p>The greatest miracle triggers the final crisis, as Jesus\'s "hour" approaches.</p>');

add_page($generator, $course, 5, 'The Raising of Lazarus',
    "<h2>John 11 — The Seventh Sign</h2>
    <p>Lazarus, a close friend of Jesus, falls critically ill. His sisters Mary and Martha send word to Jesus. Jesus deliberately delays two more days. By the time He arrives, Lazarus has been dead for four days.</p>
    <h3>Jesus Weeps</h3>"
    . quote("When Jesus therefore saw her weeping, and the Jews also weeping... he groaned in the spirit, and was troubled... Jesus wept.", "John 11:33, 35")
    . "<p>The shortest verse in the Bible carries enormous theological weight. Jesus — who is <em>about to raise Lazarus</em> — still weeps. He is not unmoved by human grief. He enters fully into our pain even when He holds the solution in His hands.</p>
    <h3>\"Come Out!\"</h3>
    <p>Jesus prays aloud — not for His own benefit, but so the crowd will understand what is happening — then commands: <strong>\"Lazarus, come out!\"</strong></p>
    <p>Lazarus walks out, still wrapped in grave clothes. Jesus says: <em>\"Unwrap him and let him go.\"</em></p>
    <p>The Sanhedrin immediately resolves to kill Jesus (11:53). His greatest act of life-giving becomes the trigger for His death. The cross is now inevitable.</p>"
    . reflect([
        "\"Jesus wept\" — what does His emotional response tell you about how God relates to human suffering?",
        "In what area of your life do you feel like you're still bound in grave clothes — held back by something from your past?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 6 — The Upper Room Discourse (John 13–17)
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 6, 'The Upper Room — John 13–17',
    '<p>The night before the cross: Jesus washes feet, gives a new command, promises the Spirit, and prays for His followers.</p>');

add_page($generator, $course, 6, 'The Servant King: Washing Feet (John 13)',
    "<h2>John 13:1–17 — The Last Supper</h2>"
    . quote("Jesus knew that the Father had put all things under his power, and that he had come from God and was returning to God; so he got up from the meal, took off his outer clothing, and wrapped a towel around his waist.", "John 13:3–4")
    . "<p>The one with all authority takes the role of the lowest household slave. He washes His disciples' feet — a task so lowly that Jewish law exempted Jewish servants from doing it for their masters.</p>
    <h3>Peter's Protest</h3>
    <p>Peter refuses: <em>\"You shall never wash my feet.\"</em> Jesus answers: <em>\"Unless I wash you, you have no part with me.\"</em> To receive Jesus is to receive His service. Refusing His service is refusing Him.</p>
    <h3>The New Commandment</h3>"
    . quote("A new command I give you: Love one another. As I have loved you, so you must love one another. By this everyone will know that you are my disciples, if you love one another.", "John 13:34–35", "#10b981")
    . "<p>The standard for Christian love is not religious duty or natural affection — it is the cross. Love \"as I have loved you\" means sacrificial, servant love that goes all the way.</p>"
    . reflect([
        "Is it easier for you to serve others or to receive service? What does Peter's struggle reveal?",
        "Jesus says the world will identify His followers by their love for one another. Is that how people currently identify Christians?"
    ]));

add_page($generator, $course, 6, 'The Holy Spirit Promised (John 14–16)',
    "<h2>The Promise of the Comforter</h2>
    <p>Jesus prepares His disciples for His departure. Their hearts are troubled. He gives an extraordinary promise:</p>"
    . quote("And I will ask the Father, and he will give you another advocate to help you and be with you forever — the Spirit of truth.", "John 14:16–17")
    . "<h3>Who Is the Holy Spirit?</h3>"
    . card('The Helper / Advocate (Paraclete)', 'One called alongside to help — a legal defender, comforter, and supporter.')
    . card('The Spirit of Truth', 'He guides believers into all truth (John 16:13) — what Jesus taught, and more.')
    . card('The Reminder', 'He brings Jesus\'s words to mind in the moment they are needed (John 14:26).')
    . card('The Witness', 'He testifies about Jesus and convicts the world of sin, righteousness, and judgment (John 15:26; 16:8).')
    . "<h3>Better That Jesus Goes?</h3>"
    . quote("It is for your good that I am going away. Unless I go away, the Advocate will not come to you; but if I go, I will send him to you.", "John 16:7")
    . "<p>The disciples could imagine nothing better than Jesus physically present. Yet Jesus says the Spirit — who would live <em>inside</em> every believer, everywhere, always — is an even greater gift.</p>"
    . reflect([
        "How aware are you of the Holy Spirit's presence in your daily life?",
        "Which description of the Spirit (Helper, Truth, Reminder, Witness) is most meaningful to you right now?"
    ]));

add_page($generator, $course, 6, "Jesus Prays for You: The High Priestly Prayer (John 17)",
    "<h2>John 17 — The Longest Prayer of Jesus</h2>
    <p>On the night before His crucifixion, Jesus prays — not for Himself to be spared, but for His disciples and for all who would believe through them. This prayer has three movements:</p>"
    . card('For Himself (v. 1–5)', 'Jesus asks to be glorified so that through the cross and resurrection, He will glorify the Father.')
    . card('For the Disciples (v. 6–19)', 'He prays for their protection, joy, and sanctification — that they would be set apart by truth.')
    . card('For All Future Believers (v. 20–26)', '"I do not ask for these only, but also for those who will believe in me through their word." — That includes you, reading this now.')
    . "<h3>The Deepest Desire of Jesus</h3>"
    . quote("Father, I want those you have given me to be with me where I am, and to see my glory...", "John 17:24", "#10b981")
    . "<p>Before facing the cross, Jesus's heart is set on one thing: to have His people with Him forever, sharing in the eternal love between the Father and the Son.</p>"
    . reflect([
        "Jesus prayed for you specifically before He went to the cross. How does that change how you see your relationship with Him?",
        "Jesus prays for unity among believers (v. 21). What is one concrete thing you can do to pursue unity in your community?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 7 — The Cross & Resurrection (John 18–20)
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 7, 'The Cross & Resurrection — John 18–20',
    '<p>Arrest, trial, crucifixion, and the empty tomb — the events on which everything rests.</p>');

add_page($generator, $course, 7, 'The Trial: What Is Truth? (John 18–19)',
    "<h2>John 18–19 — Before Pilate</h2>
    <p>After praying in Gethsemane, Jesus is arrested by soldiers and temple guards — and falls to the ground at the sound of Jesus's voice alone when He says <em>\"I AM he\"</em> (John 18:6).</p>
    <p>He is brought before Annas, then Caiaphas (the High Priest), and finally to the Roman governor Pontius Pilate.</p>"
    . quote("My kingdom is not of this world. If it were, my servants would fight to prevent my arrest... But now my kingdom is from another place.", "John 18:36")
    . "<p>Pilate asks: <em>\"What is truth?\"</em> — and walks away without waiting for an answer, even though the answer is standing in front of him. He declares Jesus innocent <strong>three times</strong>, yet hands Him over to crucifixion to appease the crowd.</p>
    <p>Political safety wins over justice. The innocent is condemned. But this is precisely the plan.</p>"
    . reflect([
        "Pilate asked \"What is truth?\" — as if the answer were unknowable. How would you answer that question today?",
        "Where in your life do you face pressure to compromise what you know is right in order to appease others?"
    ]));

add_page($generator, $course, 7, 'It Is Finished: The Crucifixion (John 19)',
    "<h2>John 19 — The Cross</h2>
    <p>John stood at the foot of the cross (v. 26) — this is eyewitness testimony. Jesus is crucified at Golgotha. A sign is posted above Him in three languages (Hebrew, Latin, Greek): <strong>\"Jesus of Nazareth, King of the Jews.\"</strong></p>
    <p>Even in His final hours, Jesus cares for others: He entrusts His mother Mary to John's care (v. 26–27).</p>
    <h3>It Is Finished</h3>"
    . quote("When he had received the drink, Jesus said, 'It is finished.' With that, he bowed his head and gave up his spirit.", "John 19:30")
    . "<p>The Greek word is <strong>tetelestai</strong> (τετέλεσται) — a single word meaning <em>\"paid in full.\"</em> It was stamped on commercial receipts when a debt was completely settled. Jesus declares that the debt of humanity's sin is completely, finally, and permanently paid.</p>
    <p>He is not a victim. He gives up His spirit — actively, willingly, in the fullness of time.</p>"
    . reflect([
        "\"Tetelestai\" — paid in full. What debt of yours has Jesus paid? What does it mean to live as someone whose debt is cancelled?",
        "John was present at the cross. If you had been there, how do you think you would have responded?"
    ]));

add_page($generator, $course, 7, 'He Is Risen: The Resurrection (John 20)',
    "<h2>John 20 — The Empty Tomb</h2>
    <p>Early Sunday morning, Mary Magdalene finds the stone rolled away. She runs to Peter and John. They race to the tomb — John outrunning Peter but waiting at the entrance. Peter enters first; John follows and sees the burial cloths lying there, the face cloth neatly folded separately.</p>"
    . quote("He saw and believed.", "John 20:8")
    . "<h3>Mary and the Gardener</h3>
    <p>Mary, weeping outside the tomb, sees a man she assumes is the gardener. He asks: <em>\"Why are you crying? Who are you looking for?\"</em> Then He speaks one word: <strong>\"Mary.\"</strong> She recognizes Him immediately — by His voice.</p>
    <h3>Thomas — Unless I See</h3>
    <p>Thomas refuses to believe the disciples' testimony without physical proof. A week later, Jesus appears specifically for Thomas and invites him to touch His wounds:</p>"
    . quote("My Lord and my God!", "John 20:28")
    . "<p>The highest confession of Jesus's identity in the entire gospel. Jesus responds:</p>"
    . quote("Because you have seen me, you have believed; blessed are those who have not seen and yet have believed.", "John 20:29", "#10b981")
    . "<p>That blessing is for you.</p>"
    . reflect([
        "Thomas needed physical proof before he believed. How do you relate to his honest doubt?",
        "Jesus pronounces a blessing on those who believe without seeing. What helps you trust in what you cannot physically verify?"
    ]));

// ═════════════════════════════════════════════════════════════════════════
// SECTION 8 — Restoration & Mission (John 21)
// ═════════════════════════════════════════════════════════════════════════
set_section($course, 8, 'Restoration & Mission — John 21',
    '<p>The risen Jesus restores a broken disciple and commissions His followers to continue His mission.</p>');

add_page($generator, $course, 8, 'Do You Love Me? — Peter Restored',
    "<h2>John 21 — Three Questions</h2>
    <p>The disciples return to fishing — perhaps unsure what to do next. Jesus appears on the shore at dawn, unrecognised, and calls out asking if they've caught anything. At His direction they cast the net and catch 153 fish.</p>
    <p>Over a charcoal fire on the beach (the same kind of fire Peter warmed himself at when he denied Jesus — John 18:18), Jesus has a private conversation with Peter.</p>
    <p>Peter had denied Jesus <strong>three times</strong>. Jesus now asks him three times:</p>"
    . quote("Simon, son of John, do you love me?", "John 21:15, 16, 17")
    . "<p>Three denials. Three questions. Three commissions: <em>\"Feed my lambs... Take care of my sheep... Feed my sheep.\"</em></p>
    <p>Jesus does not define Peter by his failure. He does not shame him or sideline him. He restores him fully to his calling — and does so publicly, gently, completely.</p>
    <p>The conversation ends where it began — with two words:</p>"
    . quote("Follow me.", "John 21:19", "#10b981")
    . reflect([
        "Have you ever felt that a failure disqualified you from being used by God? What does Jesus's restoration of Peter say to you?",
        "Jesus asks \"Do you love me?\" three times. How would you honestly answer that question right now?"
    ]));

add_page($generator, $course, 8, 'Course Conclusion: What Will You Do?',
    "<h2>You've Completed the Course</h2>
    <p>You've walked through the Gospel of John and encountered Jesus as:</p>
    <ul>
      <li>The <strong>eternal Word made flesh</strong> (John 1)</li>
      <li>The one who offers <strong>new birth</strong> (John 3) and <strong>living water</strong> (John 4)</li>
      <li>The <strong>Bread of Life</strong>, <strong>Light of the World</strong>, <strong>Gate</strong>, and <strong>Good Shepherd</strong></li>
      <li>The <strong>Resurrection and the Life</strong> who wept at a friend's grave</li>
      <li>The servant who <strong>washed His disciples' feet</strong> the night before He died</li>
      <li>The one who prayed for <em>you</em> in John 17</li>
      <li>The one who said <strong>\"It is finished\"</strong> and proved it by rising</li>
      <li>The risen Lord who <strong>restores the broken</strong> and sends them out</li>
    </ul>
    <h3>John's Purpose — Has It Been Fulfilled?</h3>"
    . quote("But these are written so that you may believe that Jesus is the Messiah, the Son of God, and that by believing you may have life in his name.", "John 20:31", "#10b981")
    . "<p>John wrote this gospel for one reason: that you would <em>believe</em> and find <em>life</em>. Has something shifted for you as you've studied?</p>
    <h3>Suggested Next Steps</h3>
    <ul>
      <li>📖 <strong>Read John's Gospel in full</strong> — now that you have context, it will read differently</li>
      <li>🙏 <strong>Respond to Jesus directly</strong> — He is alive and present; talk to Him</li>
      <li>🤝 <strong>Find a community</strong> — connect with believers who can walk with you</li>
      <li>📚 <strong>Keep going</strong> — explore Romans, the Psalms, or the other Gospels</li>
    </ul>
    <div style='background:#e0e7ff;border-radius:12px;padding:24px;margin-top:24px;text-align:center;'>
      <h3 style='color:#3730a3;margin-top:0;'>Thank you for taking this course.</h3>
      <p style='margin:0;color:#1e293b;'>May the words of John's Gospel take root in your heart and bear lasting fruit.</p>
    </div>");

// ═════════════════════════════════════════════════════════════════════════
// Tracking JavaScript (injected into every page footer)
// ═════════════════════════════════════════════════════════════════════════
$tracking_js = <<<'JS'
<!-- Certeza Progress Tracker -->
<script>
(function () {
    var ENDPOINT  = 'https://your-api.example.com/track'; // ← REPLACE with your endpoint
    var COURSE    = 'JOHN101';
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

    // Propagate uid across all internal links so it survives navigation
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

    // Track page view on load
    send('page_view', {title: document.title});

    // Track manual completion button clicks
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-action="toggle-manual-completion"], .btn-completionmanual');
        if (btn) send('activity_complete', {
            cmid:  btn.getAttribute('data-cmid'),
            label: btn.getAttribute('aria-label') || btn.innerText.trim()
        });
    }, true);

    // Propagate uid to links
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
