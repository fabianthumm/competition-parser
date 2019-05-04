<?php
define("RESULTS_DIR", 'competitions/');

include 'vendor/autoload.php';
include '_functions.php';

require_all('classes');

$config = $argv[1];
$config = yaml_parse_file($config);

$competition = new Competition($config['name'], $config['date'], $config['location'], $config['clock_type']);

$competitionParser = new CompetitionParser($config);
$fileName = RESULTS_DIR . $config['file'];

switch (pathinfo($fileName, PATHINFO_EXTENSION)) {
    case 'csv':
        $lines = file($fileName, FILE_IGNORE_NEW_LINES);
        define('ENCODING', "UTF-8");
        break;
    case 'pdf':
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fileName);
        $text = $pdf->getText();
        $text = str_replace('&#39;', "'", $text);
        $text = preg_replace('/\h+/', ' ', $text);
        $lines = explode("\n", $text);
        define('ENCODING', "UTF-8");
        break;
    case 'txt':
        $lines = file($fileName, FILE_IGNORE_NEW_LINES);
        define('ENCODING', "UTF-8");
        break;
    default:
        print_r('Unknown filetype ' . pathinfo($fileName, PATHINFO_EXTENSION));
        exit;
        break;
}

$lines = $competitionParser->createUsableLines($lines);

writeToFile($lines);

$i = 1;
foreach ($lines as $line) {
    $lineType = $competitionParser->getLineType($line);
    switch ($lineType) {
        case 'event':
            $eventId = $competitionParser->getEventIdFromLine($line);
            $gender = $competitionParser->getGenderFromLine($line);
            $includeEvent = $competitionParser->shouldIncludeEvent($line);
            $event = Event::create($eventId, $gender, $includeEvent, $line);
            $competition->addEvent($event);
            break;
        case 'gender':
            $gender = $competitionParser->getGenderFromLine($line);
            $events = $competition->getEvents();

            /** @var Event $currentEvent */
            $currentEvent = end($events);
            if (is_null($currentEvent) || !$currentEvent) continue;

            $event = Event::create($currentEvent->getId(), $gender, true, $currentEvent->getOriginalLine());
            $competition->addEvent($event);
            break;
        case 'result':
            if (!$competition->hasCurrentEvent()) continue;
            $name = $competitionParser->getNameFromLine($line);
            $yearOfBirth = $competitionParser->getYearOfBirthFromLine($line);
            $times = $competitionParser->getTimesFromLine($line);
            $result = Result::create($name, $yearOfBirth, $times, $line);

            $competition->addResultToCurrentEvent($result);
            break;
        case 'round':
            $roundNumber = $competitionParser->getRoundFromLine($line);
            $currentEvent = $competition->getCurrentEvent();
            $event = Event::create($currentEvent->getId(), $currentEvent->getGender(), true, $currentEvent->getOriginalLine(), $roundNumber);
            $competition->addEvent($event);
            break;
    }
}

$competition->removeNullEvents();

try {
    printCompetition($competition, 'template');
//    $dbHelper = new DbHelper();
//    $dbHelper->saveCompetitionToDatabase($competition);
} catch (Exception $e) {
    print_r('Something terrible happened' . PHP_EOL);
    print_r($e->getMessage());
}