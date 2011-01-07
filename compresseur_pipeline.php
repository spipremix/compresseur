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
 * Pipeline header_prive
 * 
 * @param string $flux
 * @return string
 */
function compresseur_header_prive($flux){
	include_spip('filtres/compresseur');
	return compacte_head($flux);
}

function compresseur_affiche_milieu($flux){
	
	if ($flux['args']['exec']=='configurer_avancees'){
			// Compression http et compactages CSS ou JS
			$flux['data'] .= recuperer_fond('prive/squelettes/inclure/configurer',array('configurer'=>'configurer_compresseur'));
	}

	return $flux;
}

function compresseur_configurer_liste_metas($metas){
	$metas['auto_compress_js']='non';
	$metas['auto_compress_closure']='non';
	$metas['auto_compress_css']='non';
	return $metas;
}

?>