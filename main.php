#!/usr/bin/php
<?php
require_once 'classes/Invoicing.php';
require_once 'classes/Logger.php';
require_once 'classes/Toggl.php';
require_once 'classes/Traffika.php';


date_default_timezone_set('Europe/Prague');

$config = require_once 'config.php';
$logger = new Logger();


$toggl = new Toggl($config, $logger);

if (in_array('-invoicing', $argv)) {
	$invoicing = new Invoicing($logger);

	if (count($argv) > 2) {
		list($from, $to) = $invoicing->getPeriod($argv[2]);
	} else {
		list($from, $to) = $invoicing->getCurrentMonthPeriod();
	}

	$reports = $toggl->getReports($from, $to);
	$invoicing->summarize($reports);

} else {
	$reports = $toggl->getTodayReports();

	$traffika = new Traffika($config, $logger);
	$traffika->uploadReports($reports);
}
