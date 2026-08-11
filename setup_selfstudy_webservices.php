<?php
/**
 * Enables web services + auth_userkey SSO so the Certeza Django app can create a
 * shadow Moodle account per anonymous prospect (keyed by idnumber = prospect_form_id),
 * enrol it, and auto-login the prospect via a one-time key — no password prompt.
 *
 * Idempotent: safe to re-run.
 *
 * The auth_userkey plugin's files are dropped into auth/userkey by the Dockerfile, but
 * Moodle only registers a new plugin's DB tables/capabilities when its upgrade process
 * runs. Run this FIRST, every time the image is rebuilt with a new/updated plugin:
 *   docker exec -it moodle-app php admin/cli/upgrade.php --non-interactive
 *
 * Then run this script:
 *   docker exec -i moodle-app php < setup_selfstudy_webservices.php
 */
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once($CFG->dirroot . '/lib/upgradelib.php');
require_once($CFG->dirroot . '/user/lib.php');

if (moodle_needs_upgrading()) {
    fwrite(STDERR,
        "Moodle has pending plugin installs/upgrades (e.g. auth_userkey) that haven't been " .
        "applied yet. Run this first:\n" .
        "  docker exec -it moodle-app php admin/cli/upgrade.php --non-interactive\n" .
        "...then re-run this script.\n"
    );
    exit(1);
}

$syscontext = context_system::instance();

// ── Web services + REST protocol ────────────────────────────────────────────
set_config('enablewebservices', 1);

$protocols = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);
if (!in_array('rest', $protocols)) {
    $protocols[] = 'rest';
    set_config('webserviceprotocols', implode(',', $protocols));
}
echo "Web services + REST protocol enabled.\n";

// ── auth_userkey plugin ─────────────────────────────────────────────────────
$auths = empty($CFG->auth) ? [] : explode(',', $CFG->auth);
if (!in_array('userkey', $auths)) {
    $auths[] = 'userkey';
    set_config('auth', implode(',', $auths));
}
set_config('mappingfield', 'idnumber', 'auth_userkey');
set_config('iprestriction', 0, 'auth_userkey');
set_config('keylifetime', 120, 'auth_userkey');
echo "auth_userkey enabled (mapping field: idnumber).\n";

// ── Least-privilege role for the Django service account ────────────────────
$rolename = 'Certeza Web Service';
$roleshortname = 'certeza_ws';
$role = $DB->get_record('role', ['shortname' => $roleshortname]);
if (!$role) {
    $roleid = create_role($rolename, $roleshortname,
        'Used by the Certeza Django app to provision self-study shadow accounts.');
    $role = $DB->get_record('role', ['id' => $roleid]);
    set_role_contextlevels($roleid, [CONTEXT_SYSTEM]);
    echo "Role '$roleshortname' created (id {$roleid}).\n";
} else {
    echo "Role '$roleshortname' already exists (id {$role->id}).\n";
}

$capabilities = [
    'webservice/rest:use',
    'moodle/user:create',
    'moodle/user:update',
    'moodle/user:viewalldetails',
    'moodle/user:viewdetails',
    'enrol/manual:enrol',
    'moodle/role:assign',
    'moodle/course:view',
    'auth/userkey:generatekey',
    'report/progress:view', // needed by core_completion_get_activities_completion_status for another user
];
foreach ($capabilities as $capability) {
    assign_capability($capability, CAP_ALLOW, $role->id, $syscontext->id, true);
}
// Let this role assign the student role via enrol_manual_enrol_users.
// core_role_set_assign_allowed() always inserts with no dedup check, so guard it ourselves.
$studentrole = $DB->get_record('role', ['shortname' => 'student']);
if ($studentrole) {
    $alreadyallowed = $DB->record_exists('role_allow_assign', [
        'roleid' => $role->id,
        'allowassign' => $studentrole->id,
    ]);
    if (!$alreadyallowed) {
        core_role_set_assign_allowed($role->id, $studentrole->id);
    }
}
echo "Capabilities assigned to '$roleshortname'.\n";

// ── Service account user ────────────────────────────────────────────────────
$username = 'certeza_ws';
$wsuser = $DB->get_record('user', ['username' => $username, 'deleted' => 0, 'mnethostid' => $CFG->mnet_localhost_id]);
if (!$wsuser) {
    $newuser = new stdClass();
    $newuser->username = $username;
    $newuser->password = base64_encode(random_bytes(24)); // never used to log in interactively
    $newuser->firstname = 'Certeza';
    $newuser->lastname = 'Web Service';
    $newuser->email = 'certeza-ws@prospects.certeza.app';
    $newuser->auth = 'manual';
    $newuser->confirmed = 1;
    $newuser->mnethostid = $CFG->mnet_localhost_id;
    $userid = user_create_user($newuser, true, false);
    $wsuser = $DB->get_record('user', ['id' => $userid]);
    echo "Service account '$username' created (id {$wsuser->id}).\n";
} else {
    echo "Service account '$username' already exists (id {$wsuser->id}).\n";
}
role_assign($role->id, $wsuser->id, $syscontext->id);

// ── External service ────────────────────────────────────────────────────────
$serviceshortname = 'certeza_selfstudy';
$service = $DB->get_record('external_services', ['shortname' => $serviceshortname]);
if (!$service) {
    $service = new stdClass();
    $service->name = 'Certeza Self-Study Integration';
    $service->shortname = $serviceshortname;
    $service->enabled = 1;
    $service->restrictedusers = 1;
    $service->downloadfiles = 0;
    $service->uploadfiles = 0;
    $service->timecreated = time();
    $service->timemodified = time();
    $service->id = $DB->insert_record('external_services', $service);
    echo "External service '$serviceshortname' created (id {$service->id}).\n";
} else {
    echo "External service '$serviceshortname' already exists (id {$service->id}).\n";
}

$functions = [
    'core_user_get_users',
    'core_user_create_users',
    'enrol_manual_enrol_users',
    'core_course_get_courses_by_field',
    'auth_userkey_request_login_url',
    'core_course_get_contents',
    'core_completion_get_activities_completion_status',
];
foreach ($functions as $functionname) {
    $exists = $DB->record_exists('external_services_functions', [
        'externalserviceid' => $service->id,
        'functionname' => $functionname,
    ]);
    if (!$exists) {
        $DB->insert_record('external_services_functions', [
            'externalserviceid' => $service->id,
            'functionname' => $functionname,
        ]);
        echo "  + $functionname\n";
    }
}

$authorised = $DB->record_exists('external_services_users', [
    'externalserviceid' => $service->id,
    'userid' => $wsuser->id,
]);
if (!$authorised) {
    $DB->insert_record('external_services_users', [
        'externalserviceid' => $service->id,
        'userid' => $wsuser->id,
        'timecreated' => time(),
    ]);
}

// ── Token ────────────────────────────────────────────────────────────────
$existingtoken = $DB->get_record('external_tokens', [
    'userid' => $wsuser->id,
    'externalserviceid' => $service->id,
    'tokentype' => EXTERNAL_TOKEN_PERMANENT,
]);
if ($existingtoken) {
    $token = $existingtoken->token;
    echo "Reusing existing token.\n";
} else {
    $token = external_generate_token(
        EXTERNAL_TOKEN_PERMANENT, $service->id, $wsuser->id, $syscontext
    );
    echo "New token generated.\n";
}

echo "\n============================================================\n";
echo "MOODLE_WS_BASE_URL = {$CFG->wwwroot}\n";
echo "MOODLE_WS_TOKEN    = {$token}\n";
echo "============================================================\n";
echo "Paste these into Django admin -> Configs as 'moodle_ws_base_url' and\n";
echo "'moodle_ws_token'. Also add 'moodle_selfstudy_course_shortname' (e.g. GOSPEL101).\n";
