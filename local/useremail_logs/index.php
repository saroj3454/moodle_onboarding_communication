<?php  require_once("../../config.php");
$userid=required_param('userid',PARAM_INT);
require_login();
// Set up the page.
$title = get_string('userlogs', 'local_useremail_logs');
$pagetitle = $title;
$url = new moodle_url("/local/useremail_logs/index.php",array('userid' => $userid));
$PAGE->set_url($url);
$PAGE->set_title($title);

$PAGE->set_context(context_user::instance($userid));
$PAGE->set_pagelayout('standard');
if(!has_capability('local/useremail_logs:displayuseremail_logssectiononprofile',context_system::instance())){
   notice(get_string('do_not_acceble','local_useremail_logs'));
}
$user = core_user::get_user($userid);

if (!$user || !core_user::is_real_user($userid)) {
    throw new \moodle_exception('invaliduser', 'error');
}
$PAGE->requires->js_call_amd('local_riskassessment/riskassessmentdisplay','init',array());

$PAGE->navigation->extend_for_user($user);

$PAGE->navbar->add(get_string('userlogs', 'local_useremail_logs'), $pageurl);


$output = $PAGE->get_renderer('local_useremail_logs');

echo $output->header();
echo $output->heading($pagetitle);
$renderable = new local_useremail_logs\output\index_page($userid);
echo $output->render($renderable);

echo $output->footer();