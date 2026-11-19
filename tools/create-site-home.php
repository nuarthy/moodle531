<?php
// Creates a demo "Home" page for the MB2 Page Builder and assigns it to the
// Moodle site home (front page). Run from the Moodle root directory:
//
//   php tools/create-site-home.php
//
// Requires: theme_mb2nl and local_mb2builder installed and upgraded.

define('CLI_SCRIPT', true);
require(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/local/mb2builder/classes/pages_api.php');

$layout = $CFG->dirroot . '/theme/mb2nl/builder2/import/layouts/data/new-school.json';
if (!file_exists($layout)) {
    echo "Layout file not found: $layout\n";
    exit(1);
}

$admin = get_admin();
$content = file_get_contents($layout);

// Resolve demo media placeholders to real URLs (what the builder does on import).
$sampleurl = $OUTPUT->image_url('sample-data', 'local_mb2builder')->out_as_local_url(false) . '/';
$content = str_replace('mb2sampledata:', $sampleurl, $content);
$content = str_replace('mb2dummyimg:', 'https://dummyimage.com/', $content);

$existing = $DB->get_record('local_mb2builder_pages', ['mpage' => -1]);
if ($existing) {
    $existing->content = $content;
    $existing->timemodified = time();
    $existing->modifiedby = $admin->id;
    Mb2builderPagesApi::update_record_data($existing, true, true);
    echo "Updated existing site home builder page (id={$existing->id}).\n";
    purge_all_caches();
    exit(0);
}

$now = time();
$data = (object)[
    'pageid' => 'home' . $now,
    'title' => 'Home',
    'content' => $content,
    'democontent' => '',
    'timecreated' => $now,
    'timemodified' => 0,
    'createdby' => $admin->id,
    'modifiedby' => 0,
    'heading' => 0,
    'footer' => 0,
    'headerstyle' => null,
    'pagecss' => null,
    'menu' => 0,
    'tgsdb' => 0,
    'mpage' => -1,
];

Mb2builderPagesApi::add_record($data, true, true);
purge_all_caches();
echo "Created site home builder page using the 'new-school' demo layout.\n";
echo "Open Site administration > Mb2 Builder > Pages to edit it.\n";
