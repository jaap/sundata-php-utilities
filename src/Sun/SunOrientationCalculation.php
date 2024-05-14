<?php

namespace Sundata\Utilities\Sun;

// shortcuts for easier to read formulas
use Carbon\CarbonImmutable;
use DateTime;
use Sundata\Utilities\Services\CarbonService;

define('PI', M_PI);
define('rad', PI / 180);

// date/time constants and conversions
define('daySec', 60 * 60 * 24);
define('J1970', 2440588);
define('J2000', 2451545);
// general calculations for position
define('e', rad * 23.4397); // obliquity of the Earth
define('J0', 0.0009);

class SunOrientationCalculation {
    var $date;
    var $timezone;
    var $lat;
    var $lng;

    // adds a custom time to the times config
    private function addTime($angle, $riseName, $setName) {
        $this->times[] = [$angle, $riseName, $setName];
    }

    function __construct($date, $timezone, $lat, $lng) {
        $this->date = $date;
        $this->timezone = $timezone;
        $this->lat  = $lat;
        $this->lng  = $lng;
    }

    // calculates sun position for a given date and latitude/longitude
    function getSunPosition() {

        $lw  = rad * -$this->lng;
        $phi = rad * $this->lat;
        $d   = $this->toDays($this->date, $this->timezone);

        $c   = $this->sunCoords($d);
        $H   = $this->siderealTime($d, $lw) - $c->ra;

        $azAlt = new AzAlt(
            $this->azimuth($H, $phi, $c->dec),
            $this->altitude($H, $phi, $c->dec)
        );

        return new SunOrientationAndAngle($this->azimuthToOrientation($azAlt->azimuth), $this->altitudeToAnlge($azAlt->altitude));
    }


    /*
     SunCalc is a PHP library for calculating sun/moon position and light phases.
     https://github.com/gregseth/suncalc-php
    
     Based on Vladimir Agafonkin's JavaScript library.
     https://github.com/mourner/suncalc
    
     Sun calculations are based on http://aa.quae.nl/en/reken/zonpositie.html
     formulas.
    
     Moon calculations are based on http://aa.quae.nl/en/reken/hemelpositie.html
     formulas.
    
     Calculations for illumination parameters of the moon are based on
     http://idlastro.gsfc.nasa.gov/ftp/pro/astro/mphase.pro formulas and Chapter 48
     of "Astronomical Algorithms" 2nd edition by Jean Meeus (Willmann-Bell,
     Richmond) 1998.
    
     Calculations for moon rise/set times are based on
     http://www.stargazing.net/kepler/moonrise.html article.
    */


    public function toJulian($date, $timezone) {
        if($date instanceof CarbonImmutable){
            $date = $date->format('Y-m-d H:i:s');
        }
        $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date, $timezone);
        $date->setTimezone('UTC');
        $julianDate = $date->getTimestamp() / daySec - 0.5 + J1970;
        return $julianDate;
    }
    public function fromJulian($j)  {
        if (!is_nan($j)) {
            $dt = new DateTime("@".round(($j + 0.5 - J1970) * daySec));
            $dt->setTimezone((new DateTime())->getTimezone());
            return $dt;
        }
        return null;
    }
    public function toDays($date, $timezone)   { return $this->toJulian($date, $timezone) - J2000; }

    public function rightAscension($l, $b) { return atan2(sin($l) * cos(e) - tan($b) * sin(e), cos($l)); }
    public function declination($l, $b)    { return asin(sin($b) * cos(e) + cos($b) * sin(e) * sin($l)); }

    public function azimuth($H, $phi, $dec)  { return atan2(sin($H), cos($H) * sin($phi) - tan($dec) * cos($phi)); }
    public function altitude($H, $phi, $dec) { return asin(sin($phi) * sin($dec) + cos($phi) * cos($dec) * cos($H)); }

    public function siderealTime($d, $lw) { return rad * (280.16 + 360.9856235 * $d) - $lw; }

    // calculations for sun times
    public function julianCycle($d, $lw) { return round($d - J0 - $lw / (2 * PI)); }

    public function approxTransit($Ht, $lw, $n) { return J0 + ($Ht + $lw) / (2 * PI) + $n; }
    public function solarTransitJ($ds, $M, $L)  { return J2000 + $ds + 0.0053 * sin($M) - 0.0069 * sin(2 * $L); }

    public function hourAngle($h, $phi, $d) { return acos((sin($h) - sin($phi) * sin($d)) / (cos($phi) * cos($d))); }


    // general sun calculations
    public function solarMeanAnomaly($d) { return rad * (357.5291 + 0.98560028 * $d); }
    public function eclipticLongitude($M) {

        $C = rad * (1.9148 * sin($M) + 0.02 * sin(2 * $M) + 0.0003 * sin(3 * $M)); // equation of center
        $P = rad * 102.9372; // perihelion of the Earth

        return $M + $C + $P + PI;
    }

    public function azimuthToOrientation($azimuth) {
        return round(($azimuth / PI * 180) + 180);
    }

    public function altitudeToAnlge($altitude) {
        return round($altitude / PI * 180);
    }

    public function orientationPerformanceGrade($value) {
        // The closer to the worst value should be 0
        // Closer to 0 should be return 1
        // Get percentage of worst value where worst value is 100%
        $performance = 1 - ($value / 180);
        return $performance;
    }

    public function anglePerformanceGrade($angle, $systemAngle) {
        $difference = abs($angle - $systemAngle);
        if($difference > 45){
            return 0;
        }
        $performance = 1 - ($difference / 45);
        return $performance;
    }

    public function combinedPerformance($orientationPerformance, $anglePerformance)
    {
        if($anglePerformance <= 0){
            return 0;
        }
        return ($orientationPerformance + $anglePerformance) / 2;
    }

    function sunCoords($d) {

        $M = $this->solarMeanAnomaly($d);
        $L = $this->eclipticLongitude($M);

        return new DecRa(
            $this->declination($L, 0),
            $this->rightAscension($L, 0)
        );
    }
}
