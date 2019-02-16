<?php

class WorldGames2017 extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        define("PARSE_YOB", $GLOBALS['config']['parser'][strtolower(self::class)]['parse_yob']);

        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function createUsableLines($lines, $type) 
    {
        $newLines = [];
        $name = '';
        $yearOfBirth = '';
        $time = '';
    
        foreach($lines as $line) {
            print_r($line);
            if(preg_match("/[A-Z\x{00C0}-\x{00DF}]{2,}\s([A-Z][a-z]*[\s\-]?){1,2}/", $line, $matches)) {
                $name = $matches[0];
                print_r($name . PHP_EOL);
                sleep(1);
            } elseif (preg_match('/[0-9]?:?[0-9]{2}\.[0-9]{2}/', $line, $matches)) {
                print_r($line . PHP_EOL);
            }
        }
    }

    /**
     * @param $line
     * @return string
     */
    public function getGenderFromLine($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['splash']['genders']['female_signifiers'])) return 2;
        elseif ($this->lineContains($line, $GLOBALS['config']['parser']['splash']['genders']['male_signifiers'])) return 1;
        return 0;
    }

    public function getLineType($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['splash']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['splash']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}\.[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['splash']['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param string $line
     * @return string
     */
    public function getFirstNameFromLine($line)
    {
        $matches = array();
        preg_match('/(\s?[A-Z]?[a-z\x{0060}-\x{00ff}]+-?)+/', utf8_decode($line), $matches);
        return trim(utf8_encode($matches[0]));
    }

    /**
     * @param string $line
     * @return string
     */
    function getLastNameFromLine($line)
    {
        $matches = array();
        preg_match('/(\s\'?[a-z]+)*((\s?[A-Z\x{00C0}-\x{00DF}]{2,}\s?)+([\']\w+\s)?-?)+/', utf8_decode($line), $matches);
        return trim(utf8_encode($matches[0]));
    }

    /**
     * @param $line
     * @return string
     */
    function getNameFromLine($line)
    {
        return utf8_decode($this->getFirstNameFromLine($line) . " " . $this->getLastNameFromLine($line));
    }


    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\s[0-9]{2}\s/', $line, $matches);
        return trim($matches[0]);
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $times = array();
        preg_match('/[0-9]{0,2}[:]?[0-9]{1,2}[.][0-9]{2}/', $line, $times);
        return [$times[0]];
    }

    function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $GLOBALS['config']['parser']['splash']['event_rejectors']);
    }
}