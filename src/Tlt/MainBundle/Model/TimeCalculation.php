<?php
namespace Tlt\MainBundle\Model;

use Tlt\AdmnBundle\Entity\System;

class TimeCalculation {

    /**
     * Return working minutes elapsed from OCCURED moment to RESOLVED moment.
     * @param System $system
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return integer
     */
    public static function getSystemTotalWorkingTime(/*$system,*/ $workingTime, $startDate, $endDate) {
//        $guaranteedValue = $system->getGuaranteedValues()->first();
//        $startWorkingTime = $guaranteedValue->getMinHour()->format('H:i:s');
//        $endWorkingTime = $guaranteedValue->getMaxHour()->format('H:i:s');


        $startWorkingTime = $workingTime->getMinHour()->format('H:i:s');
        $endWorkingTime = $workingTime->getMaxHour()->format('H:i:s');


        if (!defined('ONEMINUTE'))
            define ('ONEMINUTE', 60);

        // ESTABLISH THE MINUTES PER DAY FROM START AND END TIMES
        $startWorkingTime = strtotime($startWorkingTime);
        $endWorkingTime = strtotime($endWorkingTime);
        $minutes_per_day = (int)(($endWorkingTime - $startWorkingTime) / 60) + 1;


        // ESTABLISH THE HOLIDAYS
        $holidays = array(
            '2015-01-01',
            '2015-01-02',
            '2015-01-02',
            '2015-04-12',
            '2015-04-13',
            '2015-05-01',
            '2015-05-31',
            '2015-06-01',
            '2015-08-15',
            '2015-11-30',
            '2015-12-01',
            '2015-12-25',
            '2015-12-26',
        );

        // Convert to TIMESTAMP
        $start = $startDate->getTimestamp();
        $end = $endDate->getTimestamp();

        // RESET WORK MINUTES
        $workminutes = 0;

        // ITERATE OVER THE DAYS
        $start = $start - ONEMINUTE;

        while ($start < $end) {
            $start = $start + ONEMINUTE;

            /**
             * Daca nu este program NON STOP, atunci scadem WEEKEND-urile si SARBATORILE LEGALE
             */
            if ( date('H:i:s', $startWorkingTime)!=date('H:i:s', strtotime('00:00:00')) || date('H:i:s', $endWorkingTime)!=date('H:i:s', strtotime('23:59:59')) ) {
                // ELIMINATE WEEKENDS - SAT AND SUN
                $weekday = date('D', $start);
                if (substr($weekday, 0, 1) == 'S') continue;

                // ELIMINATE HOLIDAYS
                $iso_date = date('Y-m-d', $start);
                if (in_array($iso_date, $holidays)) continue;
            }

            // ELIMINATE HOURS BEFORE BUSINESS HOURS
            $daytime = date('H:i:s', $start);
            if (($daytime < date('H:i:s', $startWorkingTime))) continue;

            // ELIMINATE HOURS PAST BUSINESS HOURS
            $daytime = date('H:i:s', $start);
            if (($daytime > date('H:i:s', $endWorkingTime))) continue;

            $workminutes++;
        } // end while

        $workminutes = $workminutes - (ceil($workminutes / $minutes_per_day) > 1 ? ceil($workminutes / $minutes_per_day) - 1 : 1);

        return ($workminutes > 0 ? $workminutes : 0);
    }
}