<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Concatener en un seul une liste de fichier,
 * avec appels de callback sur chaque fichier,
 * puis sur le fichier final
 *
 * Gestion d'un cache : le fichier concatene n'est produit que si il n'existe pas
 * pour la liste de fichiers fournis en entree  
 *
 *
 * @param  $files
 * @param string $format
 * @return array
 */
function concatener_fichiers($files,$format='js', $callbacks = array()){
	$nom = "";
	if (!is_array($files) && $files) $files = array($files);
	if (count($files)){

		$callback_min = isset($callbacks['each_min'])?$callbacks['each_min']:'concatener_callback_identite';
		$callback_pre = isset($callbacks['each_pre'])?$callbacks['each_pre']:'';
	  $url_base = self('&');

		// on trie la liste de files pour calculer le nom
		// necessaire pour retomber sur le meme fichier
		// si on renome une url a la volee pour enlever le var_mode=recalcul
		// mais attention, il faut garder l'ordre initial pour la minification elle meme !
		$s2 = $files;
		ksort($s2);
		$dir = sous_repertoire(_DIR_VAR,'cache-'.$format);
		$nom = $dir . md5(serialize($s2)) . ".$format";
		if (
			$GLOBALS['var_mode']=='recalcul'
			OR !file_exists($nom)
		) {
			$fichier = "";
			$comms = array();
			$total = 0;
			$s2 = false;
			foreach($files as $key=>$file){
				if (!is_array($file)) {
					// c'est un fichier
					$comm = $file;
					// enlever le timestamp si besoin
					$file = preg_replace(",[?].+$,",'',$file);

					// preparer le fichier si necessaire
					if ($callback_pre)
						$file = $callback_pre($file);
					
					lire_fichier($file, $contenu);
				}
				else {
					// c'est un squelette
					$comm = _SPIP_PAGE . "=$file[0]"
						. (strlen($file[1])?"($file[1])":'');
					parse_str($file[1],$contexte);
					$contenu = recuperer_fond($file[0],$contexte);

					// preparer le contenu si necessaire
					if ($callback_pre)
						$file = $callback_pre($contenu, $url_base);

					// enlever le var_mode si present pour retrouver la css minifiee standard
					if (strpos($file[1],'var_mode')!==false) {
						if (!$s2) $s2 = $files;
						unset($s2[$key]);
						$key = preg_replace(',(&(amp;)?)?var_mode=[^&\'"]*,','',$key);
						$file[1] = preg_replace(',&?var_mode=[^&\'"]*,','',$file[1]);
						$s2[$key] = $file;
					}
				}
				// passer la balise html initiale en second argument
				$fichier .= "/* $comm */\n". $callback_min($contenu, $key) . "\n\n";
				$comms[] = $comm;
				$total += strlen($contenu);
			}

			// calcul du % de compactage
			$pc = intval(1000*strlen($fichier)/$total)/10;
			$comms = "compact [\n\t".join("\n\t", $comms)."\n] $pc%";
			$fichier = "/* $comms */\n\n".$fichier;

			if ($s2) {
				ksort($s2);
				$nom = $dir . md5(serialize($s2)) . ".$format";
			}

			// ecrire
			ecrire_fichier($nom,$fichier,true);
			// ecrire une version .gz pour content-negociation par apache, cf. [11539]
			ecrire_fichier("$nom.gz",$fichier,true);

		  if (isset($callbacks['all'])){
			  $callback = $callbacks['all'];
				// closure compiler ou autre super-compresseurs
				// a appliquer sur le fichier final
				$nom = $callback($nom, $format);
		  }
		}


	}

	// Le commentaire detaille n'apparait qu'au recalcul, pour debug
	return array($nom, (isset($comms) AND $comms) ? "<!-- $comms -->\n" : '');
}

function &concatener_callback_identite(&$contenu){
	return $contenu;
}