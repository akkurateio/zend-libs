<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur la manipulation des dates   
 */
class LibDate {

    /**
     * Age par rapport à une date (2012-07-23)
     * @param date $naiss 
     * @return int age
     */
    public static function getAgeFromDate($naiss) {
        list($annee, $mois, $jour) = split('[-.]', $naiss);
        $today['mois'] = date('n');
        $today['jour'] = date('j');
        $today['annee'] = date('Y');

        //Soustraction entre l'année en cours et l'année de naissance
        $annees = $today['annee'] - $annee;

        //Si le mois en cours est plus petit que le mois de naissance
        if ($today['mois'] < $mois) {
            $annees--;
        } else {
            //Si le mois de naissance égal le mois actuel
            if ($today['mois'] == $mois) {
                //Si le jours en cours est plus petit que le jour de naissance
                if ($today['jour'] < $jour) {
                    $annees--;
                }
            }
        }
        return $annees;
    }

    /**
     * Ecriture de la date en français au format texte avec l'heure
     * @param date $datetime 
     * @return string (23/07/2010 à 15:05:52)
     */
    public static function convertDate($datetime) {
        list($date, $time) = explode(' ', $datetime);

        $date = array_reverse(explode('-', $date));
        $date = join('/', $date);

        return $date . ' &agrave; ' . $time;
    }

    /**
     * Convertit une date française compléte (25/02/2012 12:25:12) en anglais (2012-02-25 12:25:12)
     * @param string $frenchDate
     * @return datetime
     */
    public static function convertFrenchDateTimeToEnglish($frenchDate) {
        list($date, $time) = explode(' ', $frenchDate);

        $date = array_reverse(explode('/', $date));
        $date = join('-', $date);
        return $date . " " . $time;
    }

    /**
     * Convertit une date française (25/02/2012) en anglais (2012-02-25)
     * @param string $frenchDate
     * @return date
     */
    public static function convertFrenchDateToEnglish($date) {
        if ($date != "") {
            $split = explode("/", $date);
            $annee = $split[2];
            $mois = $split[1];
            $jour = $split[0];
            return "$annee" . "-" . "$mois" . "-" . "$jour";
        }
    }

    /**
     * Renvois de la date au format français
     * Convertit une date anglaise complète (2012-02-25 12:25:12)en français (25/02/2012 12:25:12)
     * @param date $datetime (2009-10-22 15:10:25)
     * @param boolean $time (heure spécifiée ou non)
     * @return string (22/10/2009 15:10:25)
     */
    public static function convertEnglishDateTimeToFrench($date, $time = true) {
        $format = "d/m/Y H:i:s";
        if (!$time) {
            $format = "d/m/Y";
        }
        $date_fr = date($format, strtotime($date));
        return $date_fr;
    }

    /**
     * Renvoi de la date actuelle au format texte
     * @return string (Mercredi 29 Décembre 2010)
     */
    public static function getDateComplete() {
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        return strftime("%A %d %B %Y");
    }

    /**
     * Différence entre 2 heures
     * @param time $heuredeb
     * @param time $heurefin
     * @return time
     */
    public static function getDifHeure($heuredeb, $heurefin) {
        $hd = explode(":", $heuredeb);
        $hf = explode(":", $heurefin);

        $hd[0] = (int) ($hd[0]);
        $hd[1] = (int) ($hd[1]);
        $hd[2] = (int) ($hd[2]);
        $hf[0] = (int) ($hf[0]);
        $hf[1] = (int) ($hf[1]);
        $hf[2] = (int) ($hf[2]);

        if ($hf[2] < $hd[2]) {
            $hf[1] = $hf[1] - 1;
            $hf[2] = $hf[2] + 60;
        }
        if ($hf[1] < $hd[1]) {
            $hf[0] = $hf[0] - 1;
            $hf[1] = $hf[1] + 60;
        }
        if ($hf[0] < $hd[0]) {
            $hf[0] = $hf[0] + 24;
        }
        return (($hf[0] - $hd[0]) . ":" . ($hf[1] - $hd[1]) . ":" . ($hf[2] - $hd[2]));
    }

    /**
     * Ecart en jours entre 2 dates
     * @param date $date1
     * @param date $date2
     * @return int $nbjours
     */
    public static function getNbDayDiffFromDate($date1, $date2) {
        $date1 = new \DateTime($date1);
        $date2 = new \DateTime($date2);

        $nbjours = $date2->diff($date1)->format("%a");
        return $nbjours;
    }

    /**
     * Retourne le nombre de jours entre 2 timestamp
     * @param int $date_begin
     * @param int $date_end
     * @return int 
     */
    static public function getNbDayDiffFromTimestamp($date_begin, $date_end) {
        return floor(($date_end - $date_begin) / (3600 * 24));
    }

    /**
     * Retourne le premier jour de la semaine
     * @param int $week (numéro de la semaine)
     * @param int $year
     * @return date
     */
    public static function getFirstDayOfWeek($week, $year) {
        return date('Y-m-d', strtotime($year . "W" . $week . "1"));
    }

    /**
     * Dernier jour du mois
     * @param int $month (numéro du mois)
     * @param int $year
     * @return date
     */
    public static function getLastDayOfMonth($month, $year) {
        return date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $year));
    }

    /**
     * Retourne le dernier jour de la semaine
     * @param int $week (numéro de la semaine)
     * @param int $year
     * @return date
     */
    public static function getLastDayOfWeek($week, $year) {
        return date('Y-m-d 00:00:00', strtotime($year . "W" . $week . "7"));
    }

    /**
     * Nombre de jours dans un mois donnée
     * @author Julien HERRERA
     * @param int $month (numéro du mois)
     * @param int $year
     * @return int
     */
    public static function getNbdaymonth($month, $year) {
        $lastday = date("d", mktime(0, 0, 0, $month + 1, 0, $year));
        return $lastday;
    }

    /**
     * Nombre de lundi dans un mois donnée retourné sous forme d'un tableau à 2 dimensions avec le numéro de la semaine et le jour concerné
     * @param int $month (numéro du mois)
     * @param int $year
     * @return array
     */
    public static function getNblundi($month, $year) {
        //Variable
        $tableau_lundi = array(array());
        $y = 0;

        //Parcours de chaque jours du mois
        for ($i = 1; $i <= nbdaymonth($month, $year); $i++) {
            //Si c'est un lundi
            if (date("l", mktime(0, 0, 0, $month, $i, $year)) == "Monday") {
                //Si c'est un lundi de la 2éme semaine
                if (($i >= 2) && ($i <= 7)) {
                    //Si c'est Janvier on prend Décembre de l'année d'avant
                    if ($month == 1) {
                        $temp_year = $year - 1;
                        $temp_month = 12;
                    }
                    //Sinon on prend le mois d'avant
                    else {
                        $temp_year = $year;
                        $temp_month = $month - 1;
                    }

                    //Récupération du dernier lundi du mois précédent
                    $j = nbdaymonth($month - 1, $temp_year) - (7 - $i);

                    //Récupération du numéro de semaine
                    $tableau_lundi[$y][0] = date("W", mktime(0, 0, 0, $temp_month, $j, $temp_year));
                    //Enregistrement du lundi sous forme de date anglaise
                    $tableau_lundi[$y][1] = $temp_year . "-" . $temp_month . "-" . $j;

                    echo "Semaine " . $tableau_lundi[$y][0];
                    echo " " . $tableau_lundi[$y][1] . "<br />";
                    $y++;
                }
                //Récupération du numéro de semaine
                $tableau_lundi[$y][0] = date("W", mktime(0, 0, 0, $month, $i, $year));
                //Enregistrement du lundi sous forme de date anglaise
                $tableau_lundi[$y][1] = $year . "-" . $month . "-" . $i;

                echo "Semaine " . $tableau_lundi[$y][0];
                echo " " . $tableau_lundi[$y][1] . "<br />";
            }
            $y++;
        }
        return $tableau_lundi;
    }

    /**
     * Retourne le nombre de mois entre 2 dates
     * @param date $date_begin
     * @param date $date_end
     * @return int $nbMois
     */
    public static function getNbMonths($date_begin, $date_end) {
        return ((date('Y', $date_end) - date('Y', $date_begin)) * 12) - date('m', $date_begin) + date('m', $date_end);
    }

    /**
     * Retourne le nombre d'années entre 2 dates
     * @param date $date_begin
     * @param date $date_end
     * @return int $nbYear
     */
    public static function getNbYears($date_begin, $date_end) 
    {
        return date('Y', $date_end) - date('Y', $date_begin);
    }

    /**
     * Retourne le nombre de semaines entre 2 dates
     * @param date $date_begin
     * @param date $date_end
     * @return int $nbWeeks
     */
    public static function getNbWeeks($date_begin, $date_end) {
        $deltaYear = date('Y', $date_end) - date('Y', $date_begin);
        $nbWeeks = 0;
        $yearBegin = intval(date('Y', $date_begin));
        for ($i = 0; $i < $deltaYear; ++$i) {
            $nbWeeks += date('W', mktime(0, 0, 0, 12, 28, $yearBegin));
            ++$yearBegin;
        }

        return $nbWeeks - date('W', $date_begin) + date('W', $date_end);
    }

    /**
     * Retourne l'heure au format texte (10h32)
     * @param time $time
     * @return string
     */
    public static function getFormatedTime($time) {
        $parts = explode(':', $time);
        $min = '';
        if ($parts[1] != '00') {
            $min = $parts[1];
        }
        return $parts[0] . 'h' . $min;
    }

    /**
     * Retourne une chaine de type (du lundi au vendredi)
     * @param array $days (tableau de jours : [0] => 'lundi'
     * 					      [1] => 'mardi'...)
     * @return string
     */
    public static function getFormatedDays($days) {
        $weekdays = array('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
        $previous = '';
        $memento = '';
        $first = '';

        if (count($days) <= 2) {
            return implode(', ', $days);
        }

        $result = '';
        $cpt = 1;
        foreach ($days as $day) {
            if ($memento == '') {
                $memento = $day;
                $first = $day;
            } else {
                // Les jours sont consécutifs
                if ((array_search(strtolower($day), $weekdays) - array_search(strtolower($previous), $weekdays)) <= 1) {
                    ++$cpt;
                    $memento .= ", $day";
                } else {
                    if (!empty($result) && $result[count($result) - 1] != ',') {
                        $result .= ', ';
                    }
                    if ($cpt >= 3) {
                        $result .= "$first au $previous";
                        $cpt = 1;
                        $memento = $day;
                        $first = $day;
                    } else {
                        $result .= $memento;
                        $memento = $day;
                        $first = $day;
                        $cpt = 1;
                    }
                }
            }

            $previous = $day;
        }

        if (!empty($result)) {
            $result .= ', ';
        }

        if ($cpt >= 3) {
            $result .= "$first au $previous";
        } else if ($memento == '') {
            $result .= $previous;
        } else {
            $result .= $memento;
        }

        return $result;
    }

    /**
     * Renvoie un tableau des jours fériés en France
     * @param int $year
     * @return array
     */
    public static function getFeries($year = null) {
        if ($year === null) {
            $year = intval(date('Y'));
        }

        $easterDate = easter_date($year);
        $easterDay = date('j', $easterDate);
        $easterMonth = date('n', $easterDate);
        $easterYear = date('Y', $easterDate);

        $holidays = array(
            // Dates fixes
            date("Y-m-d", mktime(0, 0, 0, 1, 1, $year)), // 1er janvier
            date("Y-m-d", mktime(0, 0, 0, 5, 1, $year)), // Féte du travail
            date("Y-m-d", mktime(0, 0, 0, 5, 8, $year)), // Victoire des alliés
            date("Y-m-d", mktime(0, 0, 0, 7, 14, $year)), // Féte nationale
            date("Y-m-d", mktime(0, 0, 0, 8, 15, $year)), // Assomption
            date("Y-m-d", mktime(0, 0, 0, 11, 1, $year)), // Toussaint
            date("Y-m-d", mktime(0, 0, 0, 11, 11, $year)), // Armistice
            date("Y-m-d", mktime(0, 0, 0, 12, 25, $year)), // Noél
            // Dates variables
            date("Y-m-d", mktime(0, 0, 0, $easterMonth, $easterDay + 1, $easterYear)), //Lundi de paques
            date("Y-m-d", mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear)), //Jeudi de l'Ascension
            date("Y-m-d", mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear)), //Lundi de pentecote
        );
        sort($holidays);

        return $holidays;
    }

    /**
     * Récupére les jours voulus entre 2 dates
     * @param timestamp $startDate
     * @param timestamp $endDate
     * @param int $weekdayNumber
     * @return array
     */
    public static function getDateForSpecificDayBetweenDates($startDate, $endDate, $weekdayNumber) {
        $dateArr = array();

        do {
            if (date("w", $startDate) != $weekdayNumber) {
                $startDate += (24 * 3600); // add 1 day
            }
        } while (date("w", $startDate) != $weekdayNumber);


        while ($startDate <= $endDate) {
            $dateArr[] = date('Y-m-d', $startDate);
            $startDate += (7 * 24 * 3600); // add 7 days
        }

        return($dateArr);
    }

    /**
     * Fonction permettant de retourner le nombre d'heures entre 2 dates
     * @param datetime $date1 - La première date
     * @param datetime $date2 - La seconde date
     * @return int 
     */
    public static function getNbHours($date1, $date2) {
        if (!$date1 || $date1 == '0000-00-00 00:00:00') {
            $date1 = date('Y-m-d h:i:s');
        }

        if (!$date2 || $date2 == '0000-00-00 00:00:00') {
            $date2 = date('Y-m-d h:i:s');
        }

        return round((strtotime($date1) - strtotime($date2)) / (3600));
    }

    /**
     * Permet de récupérer l'antériorité d'une date par rapport à une autre
     * @param string $date_beg
     * @param string $date_end 
     * @return int
     *      -1 : $date_beg est antérieure à $date_end
     *      0  : $date_beg est égale à $date_end
     *      1 : $date_beg est postérieure à $date_end
     *      false : impossible à déterminer
     */
    public static function checkAnteriority($date_beg, $date_end) {
        $begin = self::dateStringToInt($date_beg);
        $end = self::dateStringToInt($date_end);

        if ($begin === false || $end === false) {
            return false;
        }

        if ($begin < $end) {
            return -1;
        } else if ($begin > $end) {
            return 1;
        } else if ($begin == $end) {
            return 0;
        }
    }

    /**
     * Permet de transformer une date string en int de la forme suivante :
     *      28/01/2013 08:00 => 201301280800
     *      2013-06-02 15:42 => 201306021542
     * @param string $datestring
     * @return int 
     */
    public static function dateStringToInt($datestring) {
        $date = '';

        // On décompose la date de début
        $parts = explode(' ', $datestring);
        $countparts = count($parts);
        if ($countparts == 2) {
            $day = $parts[0];
            $hour = $parts[1];
        } else if ($countparts == 1) {
            $day = $parts[0];
            $hour = '00:00';
        } else {
            return false;
        }

        // On décompose la date
        $partsdate = explode('/', $day);
        $countpartsdate = count($partsdate);
        if ($countpartsdate == 3) {
            $date .= $partsdate[2];
            $date .= $partsdate[1];
            $date .= $partsdate[0];
        } else if ($countpartsdate == 1) {
            $partsdateen = explode('-', $day);
            $countpartsdateen = count($partsdateen);
            if ($countpartsdateen == 3) {
                $date .= $partsdateen[0];
                $date .= $partsdateen[1];
                $date .= $partsdateen[2];
            } else {
                return false;
            }
        } else {
            return false;
        }

        // On décompose l'heure
        $partshour = explode(':', $hour);
        $countpartshour = count($partshour);

        if ($countpartshour == 3) {
            $date .= $partshour[0];
            $date .= $partshour[1];
            $date .= $partshour[2];
        } else if ($countpartshour == 2) {
            $date .= $partshour[0];
            $date .= $partshour[1];
            $date .= '00';
        } else {
            return false;
        }

        return floatval($date);
    }

    /**
     * Permet d'arrondir la date à la demi-heure supérieure
     * Les secondes sont ignorées
     * 
     * @param string $datetime 
     */
    public static function toNearestHalfHour($datetime, $timezone = 'fr') {
        // On commence par séparer la date de l'heure
        $parts = explode(' ', $datetime);

        // On doit avoir 2 résultats : une date et une heure
        if (count($parts) != 2) {
            return $datetime;
        }

        // On isole date et heure
        $date = $parts[0];
        $hour = $parts[1];

        // On analyse l'heure, les secondes sont ignorées
        $hours = explode(':', $hour);
        $h = $hours[0];
        $m = $hours[1];

        // On récupére le format EN de la date
        $date = self::getDateEn($date);

        // Conversion de la date en timestamp
        $time = strtotime($date);

        // Si les minutes sont inférieures à la demi-heure, on arrondi à la demi-heure
        if ($m <= 30) {
            $m = 30;
        }
        // Sinon, donc a fortiori supérieure à la demi-heure, on arrondi à l'heure suivante
        else {
            $m = '00';

            // Si l'heure est postérieure à 23h, on se contente de l'incrémenter d'une unité
            if ($h < 23) {
                ++$h;
            }
            // Sinon, donc a fortiori si elle vaut 23h, on bascule sur minuit et rajoute un jour
            else {
                $h = '00';
                $time = strtotime('+1 day', $time);
            }
        }

        // On retourne le résultat dans le format souhaité
        if ($timezone == 'fr') {
            return date("d/m/Y $h:$m:00", $time);
        } else {
            return date("Y-m-d $h:$m:00", $time);
        }
    }

    /**
     * Permet de convertir une date/time au timezone fourni
     * puis la retourne au timezone du serveur
     * 
     * @param string $datetime 
     * @return string 'Y-m-d H:i:s'
     */
    public static function convertToTimezone($datetime, $timezone) {
        $date = new \DateTime($datetime, new \DateTimeZone($timezone));
        $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return $date->format('Y-m-d H:i:s');
    }

}
