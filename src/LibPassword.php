<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les mots de passe   
 */
class LibPassword {
	/**
	 * Vérification du mot de passe d'un utilisateur
	 * @param string $pwdTrue
	 * @param string $passwordWrite
	 * @return boolean
	 */
	public static function checkPasswordForUser(string $pwdTrue, string $passwordWrite)
	{
		if (password_verify($passwordWrite, $pwdTrue))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Cryptage d'un mot de passe
	 * @param string $password
	 * @return string
	 */
	public static function cryptPassword(string $password): string
	{
		$options = array (
				'cost' => 11	// Cout algorithmique
		);
		 
		return password_hash($password, PASSWORD_BCRYPT, $options);
	}
	
	/**
	 * Génère un mot de passe aléatoire
	 * @param int $length
	 * @return string
	 */
	public static function randomPassword($length = 8) {
	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    
	    $pass = array();
	    $alphaLength = strlen($alphabet) - 1;
	    for ($i = 0; $i < $length; $i++) {
	        $n = mt_rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    
	    return implode($pass);
	}
}