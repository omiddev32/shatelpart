<?php

namespace App\Packages\Datetime;

use Carbon\Carbon;
use NumberFormatter;
use IntlDateFormatter;
use Illuminate\Support\Arr;
 
class DateFormatter
{
    /**
    * The instance carbon date
    *
    * @var \Carbon\Carbon $dateTime
    */
    protected $dateTime;

    /**
     * Date constructor.
     *
     * @param string $time
     * @param string $timezone
     */
    public function __construct($time = null, $timezone= 'Asia/Tehran', $lang = 'fa')
    {
        $this->dateTime = self::createDateTime($time, $timezone, $lang);
    }

    /**
     * @param null $time
     * @param string $timezone
     *
     * @return static
     */
    public static function forge($time = null, $timezone = 'Asia/Tehran', $lang = 'fa')
    {
        return new static($time, $timezone, $lang);
    }

    /**
     * Formats the date for display.
     *
     * @param string $format
     * @param string $type
     * @param string $lang
     * @param string $timezone
     *
     * @return string
     */
    public function format($format = 'HH:mm:ss - yyyy/MM/dd', $calendar = 'fa', $lang = 'en', $timezone = 'Asia/Tehran')
    {
        $timestamp = $this->time();

        if ($calendar == 'fa') {
            $locale = "{$lang}_IR@calendar=persian";
        } elseif ($calendar == 'ar') {
            $locale = "{$lang}_IR@calendar=islamic-civil";
        } else {
            $locale = "{$lang}_US";
        }
        $date = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::FULL, $timezone, IntlDateFormatter::TRADITIONAL);
        $date->setPattern($format);

        return $date->format($timestamp);
    }

    /**
     * Convert date from persian to english
     *
     * @param $time
     * @param string $fromFormat
     * @param string $toFormat
     * @param string $timezone
     * @return string
     */
    public static function convert($time, $fromFormat = 'yyyy-MM-dd',
                     $toFormat = 'yyyy-MM-dd', $timezone = 'Asia/Tehran')
    {
        $formatter = IntlDateFormatter::create('US_IR@calendar=persian', IntlDateFormatter::FULL, IntlDateFormatter::FULL, $timezone, IntlDateFormatter::TRADITIONAL, $fromFormat);
        
        $output = IntlDateFormatter::create('en_US', IntlDateFormatter::FULL, IntlDateFormatter::FULL, $timezone, IntlDateFormatter::GREGORIAN, $toFormat);
        $output = $output->format($formatter->parse($time));

        if ($output) return $output;
        return $out->getErrorMessage();
    }
    
    /**
     * Creates a date object .
     *
     * @param null $timestamp
     * @param null $timezone
     * @param string $lang
     *
     * @return Carbon|\DateTimeInterface|null
     */
    protected static function createDateTime($timestamp = null, $timezone = null, $lang)
    {
        Carbon::setLocale($lang);

        $timezone = static::createTimeZone($timezone);
        if ($timestamp === null) {
            return Carbon::now($timezone);
        }
        if ($timestamp instanceof \DateTimeInterface) {
            return $timestamp;
        }
        if (is_string($timestamp)) {
            return Carbon::parse($timestamp, $timezone);
        }
        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestamp($timestamp, $timezone);
        }
        throw new \InvalidArgumentException('timestamp is not valid');
    }

    /**
     * @param null $timezone
     *
     * @return \DateTimeZone|null
     */
    protected static function createTimeZone($timezone = null)
    {
        if ($timezone instanceof \DateTimeZone) {
            return $timezone;
        }
        if ($timezone === null) {
            return new \DateTimeZone(date_default_timezone_get());
        }
        if (is_string($timezone)) {
            return new \DateTimeZone($timezone);
        }
        throw new \InvalidArgumentException('timezone is not valid');
    }

    /**
     * Create a new safe Carbon instance from a specific date and time.
     *
     * If any of $year, $month or $day are set to null their now() values will
     * be used.
     *
     * If $hour is null it will be set to its now() value and the default
     * values for $minute and $second will be their now() values.
     *
     * If $hour is not null then the default values for $minute and $second
     * will be 0.
     *
     * If one of the set values is not valid, an \InvalidArgumentException
     * will be thrown.
     *
     * @param int|null                  $year
     * @param int|null                  $month
     * @param int|null                  $day
     * @param int|null                  $hour
     * @param int|null                  $minute
     * @param int|null                  $second
     * @param \DateTimeZone|string|null $tz
     *
     * @throws \Carbon\Exceptions\InvalidDateException|\InvalidArgumentException
     *
     * @return static
     */
    public static function createSafe($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null, $tz = 'Asia/Tehran')
    {
        $dateTime = Carbon::createSafe($year, $month, $day, $hour, $minute, $second, $tz);

        return new static($dateTime);
    }

    /**
     * Create a Carbon instance from just a date. The time portion is set to now.
     *
     * @param int|null                  $year
     * @param int|null                  $month
     * @param int|null                  $day
     * @param \DateTimeZone|string|null $tz
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public static function createFromDate($year = null, $month = null, $day = null, $tz = null)
    {
        $dateTime = Carbon::createFromDate($year, $month, $day, $tz);

        return new static($dateTime);
    }

    /**
     * Create a Carbon instance from just a date. The time portion is set to midnight.
     *
     * @param int|null                  $year
     * @param int|null                  $month
     * @param int|null                  $day
     * @param \DateTimeZone|string|null $tz
     *
     * @return static
     */
    public static function createMidnightDate($year = null, $month = null, $day = null, $tz = null)
    {
        $dateTime = Carbon::createMidnightDate($year, $month, $day, $tz);

        return new static($dateTime);
    }

    /**
     * Create a Carbon instance from just a time. The date portion is set to today.
     *
     * @param int|null                  $hour
     * @param int|null                  $minute
     * @param int|null                  $second
     * @param \DateTimeZone|string|null $tz
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public static function createFromTime($hour = null, $minute = null, $second = null, $tz = null)
    {
        $dateTime = Carbon::createFromTime($hour, $minute, $second, $tz);

        return new static($dateTime);
    }

    /**
     * Gets the timespan between this date and another date.
     *
     * @param  string|DateTimeZone $timezone
     * @param  array $only
     * @return int
     */
    public function timespan($timezone = null, $only = [])
    {
        // Get translator
        $lang = $this->dateTime->getTranslator();

        $units = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        if (! empty($only)) {
            $units =  Arr::only($units, $only);
        }

        // Get DateInterval and cast to array
        $interval = (array) (new \Datetime())->diff($this->dateTime);
       
        // Get weeks
        $interval['w'] = (int) ($interval['d'] / 7);
        $interval['d'] = $interval['d'] % 7;
        // Get ready to build
        $str = [];
        // Loop all units and build string
        foreach ($units as $k => $unit) {
            if ($interval[$k]) {
                $str[] = $lang->transChoice($unit, $interval[$k], [':count' => $interval[$k]]);
            }
        }

        return implode(', ', $str);
    }

    /**
     * Alias for diffForHumans.
     *
     * @param  Date $since
     * @param  bool $absolute Removes time difference modifiers ago, after, etc
     * @return string
     */
    public function ago($other = null, $absolute = false, $short = false, $parts = 1)
    {
        return $this->dateTime->diffForHumans($other, $absolute, $short, $parts);
    }

    /**
     * Alias for diffForHumans.
     *
     * @param  Date $since
     * @return string
     */
    public function until($since = null)
    {
        return $this->ago($since);
    }
    /**
     *
     *
     * @return bool
     */
    public function isToday()
    {
        return $this->dateTime->isToday();
    }

    /**
     * Checks if this day is a Saturday.
     *
     * @return bool
     */
    public function isSaturday()
    {
        return $this->dateTime->isSaturday();
    }

    /**
     * Checks if this day is a Sunday.
     *
     * @return bool
     */
    public function isSunday()
    {
        return $this->dateTime->isSunday();
    }

    /**
     * Checks if this day is a Monday.
     *
     * @return bool
     */
    public function isMonday()
    {
        return $this->dateTime->isMonday();
    }

    /**
     * Checks if this day is a Tuesday.
     *
     * @return bool
     */
    public function isTuesday()
    {
        return $this->dateTime->isTuesday();
    }

    /**
     * Checks if this day is a Wednesday.
     *
     * @return bool
     */
    public function isWednesday()
    {
        return $this->dateTime->isWednesday();
    }

    /**
     * Checks if this day is a Thursday.
     *
     * @return bool
     */
    public function isThursday()
    {
        return $this->dateTime->isThursday();
    }

    /**
     * Checks if this day is a Friday.
     *
     * @return bool
     */
    public function isFriday()
    {
        return $this->dateTime->isFriday();
    }

    /**
     * Add centuries to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function addCenturies($value)
    {
        $this->dateTime->addCenturies($value);

        return $this;
    }

    /**
     * Add a century to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function addCentury($value = 1)
    {
        $this->dateTime->addCentury($value);

        return $this;
    }

    /**
     * Remove centuries from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subCenturies($value)
    {
        $this->dateTime->subCenturies($value);

        return $this;
    }

    /**
     * Remove a century from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subCentury($value = 1)
    {
        $this->dateTime->subCentury($value);

        return $this;
    }

    /**
     * Add years to the instance. Positive $value travel forward while
     * negative $value travel into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function addYears($value)
    {
        $this->dateTime->addYears($value);

        return $this;
    }

    /**
     * Add a year to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function addYear($value = 1)
    {
        $this->dateTime->addYear($value);

        return $this;
    }

    /**
     * Add years to the instance with no overflow of months
     * Positive $value travel forward while
     * negative $value travel into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function addYearsNoOverflow($value)
    {
        $this->dateTime->addYearsNoOverflow($value);

        return $this;
    }

    /**
     * Add year with overflow months set to false
     *
     * @param int $value
     *
     * @return static
     */
    public function addYearNoOverflow($value = 1)
    {
        $this->dateTime->addYearNoOverflow($value);

        return $this;
    }

    /**
     * Add years to the instance.
     * Positive $value travel forward while
     * negative $value travel into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function addYearsWithOverflow($value)
    {
        $this->dateTime->addYearsWithOverflow($value);

        return $this;
    }

    /**
     * Add year with overflow.
     *
     * @param int $value
     *
     * @return static
     */
    public function addYearWithOverflow($value = 1)
    {
        $this->dateTime->addYearWithOverflow($value);

        return $this;
    }

    /**
     * Remove years from the instance.
     *
     * @param int $value
     *
     * @return static
     */
    public function subYears($value)
    {
        $this->dateTime->subYears($value);

        return $this;
    }

    /**
     * Remove a year from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subYear($value = 1)
    {
        $this->dateTime->subYear($value);

        return $this;
    }

    /**
     * Remove years from the instance with no month overflow.
     *
     * @param int $value
     *
     * @return static
     */
    public function subYearsNoOverflow($value)
    {
        $this->dateTime->subYearsNoOverflow($value);

        return $this;
    }

    /**
     * Remove year from the instance with no month overflow
     *
     * @param int $value
     *
     * @return static
     */
    public function subYearNoOverflow($value = 1)
    {
        $this->dateTime->subYearNoOverflow($value);

        return $this;
    }

    /**
     * Remove years from the instance.
     *
     * @param int $value
     *
     * @return static
     */
    public function subYearsWithOverflow($value)
    {
        $this->dateTime->subYearsWithOverflow($value);

        return $this;
    }

    /**
     * Remove year from the instance.
     *
     * @param int $value
     *
     * @return static
     */
    public function subYearWithOverflow($value = 1)
    {
        $this->dateTime->subYearWithOverflow($value);

        return $this;
    }

    /**
     * Add quarters to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function addQuarters($value)
    {
        $this->dateTime->addQuarters($value);

        return $this;
    }

    /**
     * Add a quarter to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function addQuarter($value = 1)
    {
        $this->dateTime->addQuarter($value);

        return $this;
    }

    /**
     * Remove quarters from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subQuarters($value)
    {
        $this->dateTime->subQuarters($value);

        return $this;
    }

    /**
     * Remove a quarter from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subQuarter($value = 1)
    {
        $this->dateTime->subQuarter($value);

        return $this;
    }

    /**
     * Add months to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function addMonths($value)
    {
        $this->dateTime->addMonths($value);

        return $this;
    }

    /**
     * Add a month to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function addMonth($value = 1)
    {
        $this->dateTime->addMonth($value);

        return $this;
    }

    /**
     * Remove months from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subMonths($value)
    {
        $this->dateTime->subMonths($value);

        return $this;
    }

    /**
     * Remove a month from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subMonth($value = 1)
    {
        $this->dateTime->subMonth($value);

        return $this;
    }

    /**
     * Add months to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function addMonthsWithOverflow($value)
    {
        $this->dateTime->addMonthsWithOverflow($value);
        return $this;
    }

    /**
     * Add a month to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function addMonthWithOverflow($value = 1)
    {
        $this->dateTime->addMonthWithOverflow($value);

        return $this;
    }

    /**
     * Remove months from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subMonthsWithOverflow($value)
    {
        $this->dateTime->subMonthsWithOverflow($value);

        return $this;
    }

    /**
     * Remove a month from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subMonthWithOverflow($value = 1)
    {
        $this->dateTime->subMonthWithOverflow($value);

        return $this;
    }

    /**
     * Add months without overflowing to the instance. Positive $value
     * travels forward while negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function addMonthsNoOverflow($value)
    {
        $this->dateTime->addMonthsNoOverflow($value);

        return $this;
    }

    /**
     * Add a month with no overflow to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function addMonthNoOverflow($value = 1)
    {
        $this->dateTime->addMonthNoOverflow($value);

        return $this;
    }

    /**
     * Remove months with no overflow from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subMonthsNoOverflow($value)
    {
        $this->dateTime->subMonthsNoOverflow($value);

        return $this;
    }

    /**
     * Remove a month with no overflow from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subMonthNoOverflow($value = 1)
    {
        $this->dateTime->subMonthNoOverflow($value);

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function addDay($value = 1)
    {
        $this->dateTime->addDay($value);

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function subDay($value = 1)
    {
        $this->dateTime->subDay($value);

        return $this;
    }

    /**
     * Add a minute to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function addMinute($value = 1)
    {
        $this->dateTime->addMinute($value);

        return $this;
    }

    /**
     * Remove a minute from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function subMinute($value = 1)
    {
        $this->dateTime->subMinute($value);

        return $this;
    }

    /**
     * @return int
     */
    public function time()
    {
        return $this->dateTime->getTimestamp();
    }
    
    /**
     * Format the instance as a string using the set format
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }

    /**
     * Format the instance as date
     *
     * @return string
     */
    public function toDateString($calendar = 'fa', $lang = 'fa')
    {
        return $this->format('yyyy-MM-dd', $calendar, $lang);
    }

    /**
     * Format the instance as a readable date
     *
     * @return string
     */
    public function toFormattedDateString($calendar = 'fa', $lang = 'fa')
    {
        return $this->format('MMMM EEEE, Y', $calendar, $lang);
    }

    /**
     * Format the instance as time
     *
     * @return string
     */
    public function toTimeString($calendar = 'fa', $lang = 'fa')
    {
        return $this->format('H:mm:ss',$calendar, $lang);
    }

    /**
     * Format the instance as date and time
     *
     * @return string
     */
    public function toDateTimeString($calendar = 'fa', $lang = 'fa')
    {
        return $this->format('yyyy-MM-dd H:mm:ss', $calendar, $lang);
    }

    /**
     * Format the instance with day, date and time
     *
     * @return string
     */
    public function toDayDateTimeString($calendar = 'fa', $lang = 'fa')
    {
        return $this->format('EEEE ,d MMMM yy h:m a', $calendar,$lang);
    }

    /**
     * Get a part of the Carbon object
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return string|int|\DateTimeZone
     */
    public function __get($name)
    {
        return $this->dateTime->__get($name);
    }

    /**
     * Check if an attribute exists on the object
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        try {
            $this->__get($name);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Set a part of the Carbon object
     *
     * @param string                   $name
     * @param string|int|\DateTimeZone $value
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->dateTime->__set($name, $value);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException|\ReflectionException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->dateTime, $method], $parameters);
    }
}