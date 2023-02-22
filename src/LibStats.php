<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur la mise en forme de données pour les statistiques  
 */
class LibStats {
    const SLOTS_HALF_HOUR = 1;
    const SLOTS_HOUR = 2;
    const SLOTS_DAY = 3;
    const SLOTS_WEEK = 4;
    const SLOTS_MONTH = 5;
    const SLOTS_YEAR = 6;
    
    protected $_data_slots = array(
        self::SLOTS_HALF_HOUR => array(
            '00:00', '00:30',
            '01:00', '01:30',
            '02:00', '02:30',
            '03:00', '03:30',
            '04:00', '04:30',
            '05:00', '05:30',
            '06:00', '06:30',
            '07:00', '07:30',
            '08:00', '08:30',
            '09:00', '09:30',
            '10:00', '10:30',
            '11:00', '11:30',
            '12:00', '12:30',
            '13:00', '13:30',
            '14:00', '14:30',
            '15:00', '15:30',
            '16:00', '16:30',
            '17:00', '17:30',
            '18:00', '18:30',
            '19:00', '19:30',
            '20:00', '20:30',
            '21:00', '21:30',
            '22:00', '22:30',
            '23:00', '23:30',
        ),
        self::SLOTS_HOUR => array(
            '00:00',
            '01:00',
            '02:00',
            '03:00',
            '04:00',
            '05:00',
            '06:00',
            '07:00',
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00',
            '19:00',
            '20:00',
            '21:00',
            '22:00',
            '23:00',
        ),
        self::SLOTS_DAY => array(
            ''
        ),
        self::SLOTS_WEEK => array(
            ''
        ),
        self::SLOTS_MONTH => array(
            ''
        ),
        self::SLOTS_YEAR => array(
            ''
        ),
    );

    public function getFormatedDataUser($stats, $date_begin, $date_end, $slot, $default = 0)
    {
    	list($d, , $slot_label) = $this->_formatTime($date_begin, $date_end, $slot, $default);
        
        $data = array();
        foreach($stats as $stat){
            $data[$stat['login_user']] = intval($stat['nb']);
        }
        
        return array($data, $this->getIntervalTitle($d, $slot_label));
    }
    
    protected function getIntervalTitle($data, $slot_label){
        $first = key($data);
        end($data);
        $end = key($data);
        
        return "$first au  $end ($slot_label)";
    }
    
    public function getFormatedDataDate($stats, $date_begin, $date_end, $slot, $default = 0){
        list($data, $format, $slot_label) = $this->_formatTime($date_begin, $date_end, $slot, $default);
        
        foreach($stats as $stat){
            $d = date($format, strtotime($stat['date_begin']));
            $data[$d] = round($stat['nb'], 2);
        }
            
        return array($data, $this->getIntervalTitle($data, $slot_label));
    }
        
    protected function _formatTime($date_begin, $date_end, $slot, $default = 0){

        $date_index = new \Zend_Date($date_begin, null);
            
        $format = 'd/m/Y H:i';
        switch($slot){
            case self::SLOTS_HALF_HOUR:
            case self::SLOTS_HOUR:
                $format = 'd/m/Y H:i';
                $f = 'dd/MM/yyyy';
                $sep = ' ';
                $delta = Subvitamine\Libs\LibDate::getNbDayDiffFromTimestamp($date_begin, $date_end);
                $zend_inc = Zend_Date::DAY;
                $slot_label = 'Jours';
                break;
            case self::SLOTS_DAY:
                $format = 'd/m/Y';
                $f = 'dd/MM/yyyy';
                $sep = '';
                $delta = Subvitamine\Libs\LibDate::getNbDayDiffFromTimestamp($date_begin, $date_end);
                $zend_inc = Zend_Date::DAY;
                $slot_label = 'Jours';
                break;
            
            case self::SLOTS_WEEK:
                $format = 'W/Y';
                $f = 'ww/yyyy';
                $sep = '';
                $delta = Subvitamine\Libs\LibDate::getNbWeeks($date_begin, $date_end);
                $zend_inc = Zend_Date::WEEK;
                $slot_label = 'Semaines';
                break;
            
            case self::SLOTS_MONTH:
                $format = 'M/Y';
                $f = 'MMM/yyyy';
                $sep = '';
                $delta = Subvitamine\Libs\LibDate::getNbMonths($date_begin, $date_end);
                $zend_inc = Zend_Date::MONTH;
                $slot_label = 'Mois';
                break;
            
            case self::SLOTS_YEAR:
                $format = 'Y';
                $f = 'yyyy';
                $sep = '';
                $delta = Subvitamine\Libs\LibDate::getNbYears($date_begin, $date_end);
                $zend_inc = Zend_Date::YEAR;
                $slot_label = 'Mois';
                break;
            default:
                return;
        }
        
        // On crée notre tableau horaire vide
        // Pour cela, on part de la date de début, on crée l'index du tableau
        //avec la valeur formattée qui va bien, et on incrémente d'une unité
        //temporelle (jour, semaine ou mois selon le cas traitÃ©)
        // Les heures sont récupérées via _data_slots et permettent de construire
        //un index pertinent
        $data = array();
        for ($i = 0; $i <= $delta; ++$i) {
            foreach ($this->_data_slots[$slot] as $s) {
                $data[$date_index->toString($f, 'en_US') . "$sep$s"] = $default;
            }
            $date_index->add(1, $zend_inc);
        }
            
        return array($data, $format, $slot_label);
    }
    
    /**
     * Génération du fichier csv
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'date 1' => nbQueries,
     *       'date 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @return string
     */
    public function getFormatedDataForCsv($data, $cat='type', $souscat='date'){
		$entete = $cat.";".$souscat.";nb;\n";
		$csv = null;
		foreach($data as $type => $fields)
		{
		    foreach($fields['data'] as $date => $volume)
		    {
			$csv .= $type.";".$date.";".$volume.";\n";
		    }
		}
		$output = $entete.$csv;
        return $output;
    }
    
    /**
     * Génération du fichier csv
     * Syntaxe du tableau attendu : 
     * array (
     *   'abscisse 1' => array(
     *     'data' => array(
     *       'serie 1' => nbQueries,
     *       'serie 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'abscisse 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @return string
     */
    public function getFormatedDataBasicForCsv($data, $cat='type'){
		$entete = $cat.";nb;\n";
		$csv = null;
		foreach($data as $type => $fields)
		{
		    foreach($fields['data'] as $volume)
		    {
			$csv .= $type.";".$volume.";\n";
		    }
		}
		$output = $entete.$csv;
        return $output;
    }
    
    /**
     * Génération du fichier csv
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries)      
     *   ),
     *   'categorie 2' => array(
     *     'nb' => nbQueries)      
     *   ),
     *   ...
     * )
     * @param array $data
     * @return string
     */
    public function getFormatedDataPieForCsv($data, $cat='type'){
		$entete = $cat.";nb;\n";
		$csv = null;
		foreach($data as $type => $fields)
		{
		    foreach($fields as $volume)
		    {
			$csv .= $type.";".$volume.";\n";
		    }
		}
		$output = $entete.$csv;
        return $output;
    }
}