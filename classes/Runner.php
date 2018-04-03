<?php

class Runner
{
	const FLAG_INVOICING = '--reporting';
	const FLAG_INVOICING_SHORT = '-r';
	const FLAG_TIMESHEETS = '--timesheets';
	const FLAG_TIMESHEETS_SHORT = '-t';

	/** @var Logger */
	private $logger;

	/** @var array */
	private $config;

	public function run($argv)
	{
		$this->logger = new Logger();
		$this->config = require_once __DIR__ . '/../config.php';

		$this->validateOptions($argv);

		if (in_array(self::FLAG_INVOICING, $argv) || in_array(self::FLAG_INVOICING_SHORT, $argv)) {
			$this->runReporting($argv);
		} else if (in_array(self::FLAG_TIMESHEETS, $argv) || in_array(self::FLAG_TIMESHEETS_SHORT, $argv)) {
			$this->runTimesheets($argv);
		} else {
			$this->runToggl2Traffika();
		}
	}

	private function validateOptions($argv)
	{
		$optionCount = 0;

		foreach($argv as $arg) {
			if ($arg[0] == '-') {
				$optionCount++;
			}
		}

		if ($optionCount > 1) {
			$this->logger->log('Too many options');
			exit(1);
		}
	}

	private function runToggl2Traffika()
	{
		$toggl = new Toggl($this->config, $this->logger);
		$traffika = new Traffika($this->config, $this->logger);

		$reports = $toggl->getThisMonthReports();
		$traffika->uploadReports($reports);
	}

	private function runReporting($argv)
	{
		$toggl = new Toggl($this->config, $this->logger);
		$invoicing = new Reporting($this->logger);

		list($from, $to) = $this->getPeriod($argv);

		$reports = $toggl->getReports($from, $to);
		$invoicing->summarize($reports);
	}

	private function runTimesheets($argv)
	{
		$this->logger->setLevel(Logger::LEVEL_ERROR);
		$traffika = new Traffika($this->config, $this->logger);
		$timesheets = new Timesheets();

		list($from, $to) = $this->getPeriod($argv);

		$entries = $traffika->getTimesheets($from, $to);
		$timesheets->report($entries);
	}

	private function getPeriod($argv)
	{
		if (count($argv) > 2) {
			return $this->getMonthPeriod($argv[2]);
		} else {
			return $this->getCurrentMonthPeriod();
		}
	}

	private function getMonthPeriod($dateString)
	{
		list($month, $year) = explode('/', $dateString);
		$date = $year . '-' . $month . '-01';
		$start = new DateTime(date('Y-m-01', strtotime($date)));
		$end = new DateTime(date('Y-m-t', strtotime($date)));
		return [$start, $end];
	}

	private function getCurrentMonthPeriod()
	{
		$start = new DateTime(date('Y-m-01'));
		$end = new DateTime(date('Y-m-t'));
		return [$start, $end];
	}
}
