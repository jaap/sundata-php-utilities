<?php

namespace Sundata\Utilities\Time;

use Carbon\CarbonImmutable;
use DateTimeZone;
use InvalidArgumentException;

/** Represents a date WITHOUT time */
class Date
{
    /** @var string */
    private $dateString;
    /** @var string|null */
    private $timezone;

    /** @var CarbonImmutable|null */
    private $carbon = null;

    private function __construct(string $dateString, ?string $timezone = null)
    {
        $this->dateString = $dateString;
        $this->timezone = $timezone;
    }

    static function of(string $dateString, ?string $timezone = null): Date
    {
        if (!preg_match('/^(19|20)\d{2}-[01]\d-[0123]\d$/', $dateString)) {
            throw new InvalidArgumentException('Invalid date format. Use 20YY-MM-DD');
        }
        if ($timezone && !in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw new InvalidArgumentException('Unknown timezone identifier');
        }
        return new Date($dateString, $timezone);
    }

    /**
     * @deprecated use DateTz instead
     * Only possible when timezone has been set
     * Will convert to the START of the day that is
     * associated with the date
     */
    function asCarbonImmutable(): CarbonImmutable
    {
        if (is_null($this->timezone)) {
            throw new InvalidArgumentException("Timezone missing, can't convert to CarbonImmutable");
        }
        if (is_null($this->carbon)) {
            $this->carbon = CarbonImmutable::parse($this->dateString, $this->timezone);
        }
        return $this->carbon;
    }

    function toDateString(): string
    {
        return $this->dateString;
    }

    function toDateTz(?string $timezone = null): DateTz
    {
        if (!$this->hasTimezone() && is_null($timezone)) {
            throw new InvalidArgumentException("Timezone missing. Can't convert to DateTz");
        }
        return DateTz::of($this->dateString, $timezone ?? $this->timezone);
    }

    function toString(): string
    {
        return sprintf("Date: %s@%s",
            $this->dateString,
            $this->timezone ? $this->timezone : "<none>"
        );
    }

    function addDays(int $days): Date
    {
        return new Date(
            CarbonImmutable::parse($this->dateString, $this->timezone)
                ->addDays($days)
                ->toDateString(),
            $this->timezone
        );
    }

    /** @deprecated use DateTz instead */
    public function timezone(): ?string
    {
        return $this->timezone;
    }

    /** @deprecated use DateTz instead */
    public function hasTimezone(): bool
    {
        return $this->timezone !== null;
    }

    /** @deprecated use DateTz instead */
    public function setTimezone(string $tz): Date
    {
        return Date::of($this->toDateString(), $tz);
    }

    public function isBefore(Date $date): bool
    {
        return $this->toDateString() < $date->toDateString();
    }

    public function isAfter(Date $date): bool
    {
        return $this->toDateString() > $date->toDateString();
    }

    public function max(Date $date): Date
    {
        return $this->isAfter($date) ? $this : $date;
    }

    public function min(Date $date): Date
    {
        return $this->isBefore($date) ? $this : $date;
    }
}
