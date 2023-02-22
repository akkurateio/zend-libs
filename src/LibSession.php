<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les lectures des fichiers de sessions   
 */
class LibSession 
{
    /**
     * Compte le nombre de sessions actives (A personnaliser la partie en commentaire)
     * @return int
     */
    public static function session_active()
    {
	if ($d = opendir(session_save_path())) 
	{
	    $count = 0;
	    $session_timeout = 30 * 60;
	    while (false !== ($file = readdir($d))) 
	    {
		if (($file != '.') && ($file != '..')) 
		{
		    if (time()- fileatime(session_save_path() . '/' . $file) < $session_timeout) 
		    {
			$fp = @fopen(session_save_path() . '/' . $file, "r");					
			$sess_data = @fread($fp, filesize(session_save_path() . '/' . $file));
			/*$session = Subvitamine\Libs\LibSession::unserializesession($sess_data);

			if (isset($session['Zend_Auth']['storage']->user))
			{
			    if ($session['Zend_Auth']['storage']->user->getLog_siebel() != "")	*/
				$count++;
			//}
		    }
		}
	    }
	}
	return $count;
    }
    
    /**
     * Renvoi les variables de sessions sous forme de tableau
     * @param mixed $data
     * @return mixed
     */
    public static function unserializesession($data)
    {
	if(strlen($data) == 0)
	{
	    return array();
	}

	// match all the session keys and offsets
	preg_match_all('/(^|;|\})([a-zA-Z0-9_]+)\|/i', $data, $matchesarray, PREG_OFFSET_CAPTURE);

	$returnArray = array();

	$lastOffset = null;
	$currentKey = '';
	foreach ($matchesarray[2] as $value)
	{
	    $offset = $value[1];
	    if(!is_null($lastOffset))
	    {
		$valueText = substr($data, $lastOffset, $offset - $lastOffset );
		$returnArray[$currentKey] = @unserialize($valueText);
	    }
	    $currentKey = $value[0];

	    $lastOffset = $offset + strlen( $currentKey )+1;
	}

	$valueText = substr($data, $lastOffset);
	$returnArray[$currentKey] = unserialize($valueText);

	return $returnArray;
    }
}