<?php
class Logger
{
	private $counter = [
		'label' => '',
		'current' => 0,
		'total' => 0
	];

	public function log($message)
	{
		print "$message\n";
	}

	public function announceTask($task)
	{
		print "$task...";
	}

	public function taskDone()
	{
		print "\033[32m Done\033[0m\n";
	}

	public function taskFail($message)
	{
		print "\033[31m FAIL\033[0m\n";
		$this->log($message);
	}

	public function startCounter($label, $totalCount)
	{
		$this->counter['label'] = $label;
		$this->counter['current'] = 0;
		$this->counter['total'] = $totalCount;
		$this->printCounter();
	}

	public function addCounter()
	{
		$this->counter['current']++;
		$this->printCounter();

		if ($this->counter['current'] == $this->counter['total']) {
			print "{$this->counter['label']}... {$this->counter['current']}/{$this->counter['total']}";
			$this->taskDone();
		}
	}

	private function printCounter()
	{
		print "{$this->counter['label']}... {$this->counter['current']}/{$this->counter['total']}\r";
	}

	public function makeDeleteConfirmation($dateString) {
		print "You are going to delete all Timesheet entries for date: \e[101m".$dateString."\033[0m\n";
		print "\e[36m[(y)es, (n)o, (c)ancel, (h)elp]:\033[0m ";

		$handle = fopen ("php://stdin","r");
		$line = trim(fgets($handle));

		while ($line !== 'yes' && $line !== 'no' && $line != 'cancel' &&
		       $line !== 'y' && $line !== 'n' && $line !== 'c') {
			if ($line === 'help' || $line === 'h') {
				print "\n";
				print "\t - \e[93myes\033[0m\t\twill delete all timesheet entries made on ".$dateString." and import today timesheets from toggl into the Traffika app\n";
				print "\t - \e[93mno\033[0m\t\twill not delete any timesheet entries but it will continue with the import and upload today timesheets from toggl into the Traffika app\n";
				print "\t - \e[93mcancel\033[0m\twill stop the import. This means no data will be imported.\n\n";
			}
			print "\e[36m[(y)es, (n)o, (c)ancel, (h)elp]:\033[0m ";
			$line = trim(fgets($handle));
		}

		$mapping = [
			'yes' => true,
			'y' => true,
			'no' => false,
			'n' => false,
			'cancel' => 'cancel',
			'c' => 'cancel',
		];

		$return = $mapping[$line];

		if ($return === 'cancel') {
			$this->log('Aborting...');
			$this->taskDone();
			die();
		}

		return $return;
	}
}
