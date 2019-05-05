<?php

class Result
{
    private $name;
    private $yearOfBirth;
    private $times;
    private $originalLine;
    private $isDq;

    /**
     * Result constructor.
     * @param string $name
     * @param int $yearOfBirth last two digits of the year
     * @param array $times
     * @param $isDq
     * @param $line
     */
    public function __construct($name, $yearOfBirth, $times, $isDq, $line)
    {
        $this->name = $name;
        $this->yearOfBirth = $yearOfBirth;
        $this->times = $times;
        $this->isDq = $isDq;
        $this->originalLine = $line;
    }

    public static function create($name, $yearOfBirth, $times, $isDq, $line)
    {
        $name = preg_replace('/\s{2,}/', '', $name);
        if($name && $times && (!PARSE_YOB || $yearOfBirth)) {
            return new Result($name, $yearOfBirth, $times, $isDq, $line);
        }
        var_dump($name, $yearOfBirth, $times, $line);
        sleep(5);
        return null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        $name = $this->name;
        if(ENCODING !== 'UTF-8') $name = mb_convert_encoding($this->name, 'UTF-8', ENCODING);
        return str_replace("'", "''", $name);
    }

    /**
     * @return int
     */
    public function getYearOfBirth()
    {
        return $this->yearOfBirth;
    }

    /**
     * @return int
     */
    public function getYearOfBirthOrNull()
    {
        if($this->yearOfBirth == 'unknown') return "NULL";
        return "'$this->yearOfBirth'";
    }

    /**
     * @return array
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * @return mixed
     */
    public function getFirstTime()
    {
        return $this->times[0];
    }

    /**
     * @return string
     */
    public function getOriginalLine()
    {
        return $this->originalLine;
    }

    /**
     * @return bool
     */
    public function isDq()
    {
        return $this->isDq ? 'true' : 'false';
    }
}