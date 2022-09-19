<?php
$services=array(
	'mypluginservice'=>array(
		"functions"=>array('local_onboarding_course_enroll'
		),//// web service functions of this service
		'requiredcapability'=>'',// if set, the web service user need this capability to access 
		'restrictedusers'=>'',// if enabled, the Moodle administrator must link some user to this service
		'enabled'=>1, // if enabled, the service can be reachable on a default installation
		'shortname'=>"", // optional – but needed if restrictedusers is set so as to allow logins.
		'downloadfiles'=>0,// allow file downloads.
		'uploadfiles'=>0 // allow file uploads.
	)
);
$functions=array(
	'local_onboarding_course_enroll' => array(         //web service function name
    'classname'   => 'local_onboarding_external',  //class containing the external function OR namespaced class in classes/external/XXXX.php
    'methodname'  => 'course_enroll',           //external function name
    'classpath'   => 'local/onboarding/externallib.php',  //file containing the class/external function - not required if using namespaced auto-loading classes.
                                                   // defaults to the service's externalib.php
    'description' => 'Course Enroll.',    //human readable description of the web service function
    'type'        => 'write',                  //database rights of the web service function (read, write)
    'ajax' => true,        // is the service available to 'internal' ajax calls. 
    'capabilities' => array(), 
	)
);