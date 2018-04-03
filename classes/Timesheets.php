<?php

class Timesheets
{
	public function report(array $entries)
	{
		foreach ($entries as $date => $timesheets) {
			foreach($timesheets as $timesheet) {
				printf("%s,\"%s\",\"%s\",\"%s\",%f\n", $date, $timesheet['project']['title'], $timesheet['activity']['title'], $timesheet['description'], $timesheet['time_spent']);
			}
		}
	}

}