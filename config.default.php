<?php
return [
	// this token can be found in toggl profile
	'toggl_token' => 'your toggle token',
	// string name or array of workspace names or null for the first workspace
	'toggl_workspace' => null,

	// your traffika credentials
	'traffika_username' => 'you@usertechnologies.com',
	'traffika_password' => 'your password',

	// deadline hour until you may upload data from previous day
	'traffika_timesheet_deadline' => '12',

	// default activity if none is selected in toggl
	'traffika_default_activity' => 'development + unit test',

	// no need to modify the rest now
	'traffika_company_domain' => 'usertechnologies.com',
	'traffika_api_url' => 'https://appu.gettraffika.com/api'
];
