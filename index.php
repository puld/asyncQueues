<?php
require_once "vendor/autoload.php";
require_once "Queues.php";
require_once "Log.php";

$timeStart = time();

$log = new Log('log.txt');
$queues = new Queues();

$generator = new \LeadGenerator\Generator();
$generator->generateLeads(10000, function (\LeadGenerator\Lead $lead) use ($queues) {
	$queues->addItem($lead);
});

$queues->run(500, function (\LeadGenerator\Lead $lead) use ($log) {
	sleep(2);
	if (intval($lead->id) % 1000)
	{
		$line = "{$lead->id} | {$lead->categoryName} | " . time();
		$log->push($line);
		echo $line . "\n";
	}
	else
	{
		// имитация ошибочной ситуации
		throw new Exception();
	}
});

echo (time() - $timeStart) . " sec\n";