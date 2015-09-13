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
}
