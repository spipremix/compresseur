<?php

function compacte_ecrire_balise_script_dist($src){
	return "<script type='text/javascript' src='$src'></script>";
}
function compacte_ecrire_balise_link_dist($src,$media=""){
	return "<link rel='stylesheet'".($media?" media='$media'":"")." href='$src' type='text/css' />";
}

/**
 * Minifier un contenu CSS
 * Si $options est vide
 *	on utilise la methode regexp simple
 * Si $options est une chaine non vide
 *  elle definit un media a appliquer a la css
 *	si la css ne contient aucun @media ni @import, on encapsule tout dans "@media $option {...}" et on utilise regexp
 *  sinon on utilise csstidy pour ne pas faire d'erreur, mais c'est 12 fois plus lent
 * Si $options sous forme de array()
 *	on pass par csstidy pour parser le code
 *  et produire un contenu plus compact et prefixe eventuellement par un @media
 * options disponibles :
 *  string media : media qui seront utilises pour encapsuler par @media
 *	  les selecteurs sans media
 *  string template : format de sortie parmi 'low','default','high','highest'
 * @param string $contenu  contenu css
 * @param mixed $options options de minification
 * @return string
 */
function compacte_css ($contenu, $options='') {
	if (is_string($options) AND $options){
		if ($options=="all") // facile : media all => ne rien preciser
			$options = "";
		elseif (
					strpos($contenu,"@media")==false
			AND strpos($contenu,"@import")==false
			){
			$contenu = "@media $options {\n$contenu\n}\n";
			$options="";
		}
		else
			$options = array('media'=>$options);
	}
	if (!is_array($options)){

		// nettoyer la css de tout ce qui sert pas
		// pas de commentaires
		$contenu = preg_replace(",/\*.*\*/,Ums","",$contenu);
		$contenu = preg_replace(",\s//[^\n]*\n,Ums","",$contenu);
		// espaces autour des retour lignes
		$contenu = str_replace("\r\n","\n",$contenu);
		$contenu = preg_replace(",\s+\n,ms","\n",$contenu);
		$contenu = preg_replace(",\n\s+,ms","\n",$contenu);
		// pas d'espaces consecutifs
		$contenu = preg_replace(",\s(?=\s),Ums","",$contenu);
		// pas d'espaces avant et apres { ; ,
		$contenu = preg_replace("/\s?({|;|,)\s?/ms","$1",$contenu);
		// supprimer les espaces devant : sauf si suivi d'une lettre (:after, :first...)
		$contenu = preg_replace("/\s:([^a-z])/ims",":$1",$contenu);
		// supprimer les espaces apres :
		$contenu = preg_replace("/:\s/ms",":",$contenu);
		// pas d'espaces devant }
		$contenu = preg_replace("/\s}/ms","}",$contenu);

		// ni de point virgule sur la derniere declaration
		$contenu = preg_replace("/;}/ms","}",$contenu);
		// pas d'espace avant !important
		$contenu = preg_replace("/\s!\s?important/ms","!important",$contenu);
		// passser les codes couleurs en 3 car si possible
		// uniquement si non precedees d'un [="'] ce qui indique qu'on est dans un filter(xx=#?...)
		$contenu = preg_replace(",([^=\"'])#([0-9a-f])(\\2)([0-9a-f])(\\4)([0-9a-f])(\\6),i","$1#$2$4$6",$contenu);
		// remplacer font-weight:bold par font-weight:700
		$contenu = preg_replace("/font-weight:bold/ims","font-weight:700",$contenu);
		// remplacer font-weight:normal par font-weight:400
		$contenu = preg_replace("/font-weight:normal/ims","font-weight:400",$contenu);

		// enlever le 0 des unites decimales
		$contenu = preg_replace("/0[.]([0-9]+em)/ims",".$1",$contenu);
		// supprimer les declarations vides
		$contenu = preg_replace(",\s([^{}]*){},Ums"," ",$contenu);
		// zero est zero, quelle que soit l'unite
		$contenu = preg_replace("/([^0-9.]0)(em|px|pt|%)/ms","$1",$contenu);

		// renommer les couleurs par leurs versions courtes quand c'est possible
		$colors = array(
			'source'=>array('black','fuchsia','white','yellow','#800000','#ffa500','#808000','#800080','#008000','#000080','#008080','#c0c0c0','#808080','#f00'),
			'replace'=>array('#000' ,'#F0F'   ,'#FFF' ,'#FF0'  ,'maroon' ,'orange' ,'olive'  ,'purple' ,'green'  ,'navy'   ,'teal'   ,'silver' ,'gray'   ,'red')
		);
		foreach($colors['source'] as $k=>$v){
			$colors['source'][$k]=",([^=\"';{])".$v.",ms";
			$colors['replace'][$k] = "$1".$colors['replace'][$k];
		}
		$contenu = preg_replace($colors['source'],$colors['replace'],$contenu);

		// raccourcir les padding qui le peuvent (sur 3 ou 2 valeurs)
		$contenu = preg_replace(",padding:([^\s;}]+)\s([^\s;}]+)\s([^\s;}]+)\s(\\2),ims","padding:$1 $2 $3",$contenu);
		$contenu = preg_replace(",padding:([^\s;}]+)\s([^\s;}]+)\s(\\1)([;}!]),ims","padding:$1 $2$4",$contenu);

		// raccourcir les margin qui le peuvent (sur 3 ou 2 valeurs)
		$contenu = preg_replace(",margin:([^\s;}]+)\s([^\s;}]+)\s([^\s;}]+)\s(\\2),ims","margin:$1 $2 $3",$contenu);
		$contenu = preg_replace(",margin:([^\s;}]+)\s([^\s;}]+)\s(\\1)([;}!]),ims","margin:$1 $2$4",$contenu);

		$contenu = trim($contenu);

		return $contenu;
	}
	else {
		// compression avancee en utilisant csstidy
		// beaucoup plus lent, mais necessaire pour placer des @media la ou il faut
		// si il y a deja des @media ou des @import

		// modele de sortie plus ou moins compact
		$template = 'high';
		if (isset($options['template']) AND in_array($options['template'],array('low','default','high','highest')))
			$template = $options['template'];
		// @media eventuel pour prefixe toutes les css
		// et regrouper plusieurs css entre elles
		$media = "";
		if (isset($options['media']))
			$media = "@media ".$options['media']." ";

		include_spip("csstidy/class.csstidy");
		$css = new csstidy();

		// essayer d'optimiser les font, margin, padding avec des ecritures raccourcies
		$css->set_cfg('optimise_shorthands',2);
		$css->set_cfg('template',$template);
		$css->parse($contenu);
		return $css->print->plain($media);
	}
}

/**
 * Compacte du javascript grace a Dean Edward's JavaScriptPacker
 *
 * Bench du 15/11/2010 sur jQuery.js :
 * JSMIN (https://github.com/rgrove/jsmin-php/)
 *	61% de la taille initiale / 2 895 ms
 * JavaScriptPacker
 *  62% de la taille initiale / 752 ms
 *
 * Closure Compiler
 *  44% de la taille initiale / 3 785 ms
 *
 * JavaScriptPacker + Closure Compiler
 *  43% de la taille initiale / 3 100 ms au total
 *
 * Il est donc plus rapide&efficace
 * - de packer d'abord en local avec JavaScriptPacker
 * - d'envoyer ensuite au closure compiler
 * Cela permet en outre d'avoir un niveau de compression decent si closure
 * compiler echoue
 *
 * Dans cette fonction on ne fait que le compactage local,
 * l'appel a closure compiler est fait une unique fois pour tous les js concatene
 * afin d'eviter les requetes externes
 * 
 * @param string $flux
 * @return string
 */
function compacte_js($flux) {
	if (!strlen($flux))
		return $flux;

	include_spip('lib/JavascriptPacker/class.JavaScriptPacker');
	$packer = new JavaScriptPacker($flux, 0, true, false);

	// en cas d'echec (?) renvoyer l'original
	if (!strlen($t = $packer->pack())) {
		spip_log('erreur de compacte_js');
		return $flux;
	}
	return $t;
}

/**
 * Compacter du javascript plus intensivement
 * grace au google closure compiler
 * 
 * @param string $content
 * @param bool $file
 * @return string 
 */
function compacte_js_more($content,$file=false) {
	# Closure Compiler n'accepte pas des POST plus gros que 200 000 octets
	# au-dela il faut stocker dans un fichier, et envoyer l'url du fichier
	# dans code_url ; en localhost ca ne marche evidemment pas
	if ($file) {
		$nom = $content;
		lire_fichier($nom, $content);
		$dest = dirname($nom).'/'.md5($content).'.js';
		if (file_exists($dest))
			if (filesize($dest))
				return $dest;
			else
				return $nom;
	}

	if (!$file AND strlen($content)>200000)
		return $content;

	include_spip('inc/distant');

	$datas=array(
		'output_format' => 'text',
		'output_info' => 'compiled_code',
		'compilation_level' => 'SIMPLE_OPTIMIZATIONS', // 'SIMPLE_OPTIMIZATIONS', 'WHITESPACE_ONLY', 'ADVANCED_OPTIMIZATIONS'
	);
	if (!$file OR strlen($content) < 200000)
		$datas['js_code'] = $content;
	else
		$datas['url_code'] = url_absolue($nom);

	$cc = recuperer_page('http://closure-compiler.appspot.com/compile',
		$trans=false, $get_headers=false,
		$taille_max = null,
		$datas,
		$boundary = -1);

	if ($cc AND !preg_match(',^\s*Error,', $cc)) {
		spip_log('Closure Compiler: success');
		$cc = "/* $nom + Closure Compiler */\n".$cc;
		if ($file){
			ecrire_fichier ($dest, $cc, true);
			ecrire_fichier ("$dest.gz", $cc, true);
			$content = $dest;
		}
		else
			$content = &$cc;
	} else {
		if ($file)
			ecrire_fichier ($dest, '', true);
	}
	return $content;
}


// Appelee par compacte_head() si le webmestre le desire, cette fonction
// compacte les scripts js dans un fichier statique pose dans local/
// en entree : un <head> html.
// http://doc.spip.org/@compacte_head_js
function compacte_head_js($flux) {
	$url_base = url_de_base();
	$url_page = substr(generer_url_public('A'), 0, -1);
	$dir = preg_quote($url_page,',').'|'.preg_quote(preg_replace(",^$url_base,",_DIR_RACINE,$url_page),',');

	$scripts = array();
	$flux_nocomment = preg_replace(",<!--.*-->,Uims","",$flux);
	foreach (extraire_balises($flux_nocomment,'script') as $s) {
		if (extraire_attribut($s, 'type') === 'text/javascript'
		AND $src = extraire_attribut($s, 'src')
		AND !strlen(strip_tags($s))
		AND (
			preg_match(',^('.$dir.')(.*)$,', $src, $r)
			OR (
				// ou si c'est un fichier
				$src = preg_replace(',^'.preg_quote(url_de_base(),',').',', '', $src)
				// enlever un timestamp eventuel derriere un nom de fichier statique
				AND $src2 = preg_replace(",[.]js[?].+$,",'.js',$src)
				// verifier qu'il n'y a pas de ../ ni / au debut (securite)
				AND !preg_match(',(^/|\.\.),', substr($src,strlen(_DIR_RACINE)))
				// et si il est lisible
				AND @is_readable($src2)
			)
		)) {
			if ($r)
				$scripts[$s] = explode('&',
					str_replace('&amp;', '&', $r[2]), 2);
			else
				$scripts[$s] = $src;
		}
	}

	if (list($src,$comms) = filtre_cache_static($scripts,'js')){
		$compacte_ecrire_balise_script = charger_fonction('compacte_ecrire_balise_script','');
		$scripts = array_keys($scripts);
		$flux = str_replace(reset($scripts),
			$comms .$compacte_ecrire_balise_script($src)."\n",
			$flux);
		$flux = str_replace($scripts,"",$flux);
	}

	return $flux;
}

// Appelee par compacte_head() si le webmestre le desire, cette fonction
// compacte les feuilles de style css dans un fichier statique pose dans local/
// en entree : un <head> html.
// http://doc.spip.org/@compacte_head_css
function compacte_head_css($flux) {
	$url_base = url_de_base();
	$url_page = substr(generer_url_public('A'), 0, -1);
	$dir = preg_quote($url_page,',').'|'.preg_quote(preg_replace(",^$url_base,",_DIR_RACINE,$url_page),',');

	$css = array();
	$flux_nocomment = preg_replace(",<!--.*-->,Uims","",$flux);
	foreach (extraire_balises($flux_nocomment, 'link') as $s) {
		if (extraire_attribut($s, 'rel') === 'stylesheet'
		AND (!($type = extraire_attribut($s, 'type'))
			OR $type == 'text/css')
		AND is_null(extraire_attribut($s, 'name')) # css nommee : pas touche
		AND is_null(extraire_attribut($s, 'id'))   # idem
		AND !strlen(strip_tags($s))
		AND $src = preg_replace(",^$url_base,",_DIR_RACINE,extraire_attribut($s, 'href'))
		AND (
			// regarder si c'est du format spip.php?page=xxx
			preg_match(',^('.$dir.')(.*)$,', $src, $r)
			OR (
				// ou si c'est un fichier
				// enlever un timestamp eventuel derriere un nom de fichier statique
				$src2 = preg_replace(",[.]css[?].+$,",'.css',$src)
				// verifier qu'il n'y a pas de ../ ni / au debut (securite)
				AND !preg_match(',(^/|\.\.),', substr($src2,strlen(_DIR_RACINE)))
				// et si il est lisible
				AND @is_readable($src2)
			)
		)) {
			$media = strval(extraire_attribut($s, 'media'));
			if ($r)
				$css[$media][$s] = explode('&',
					str_replace('&amp;', '&', $r[2]), 2);
			else
				$css[$media][$s] = $src;
		}
	}

	// et mettre le tout dans un cache statique
	foreach($css as $m=>$s){
		// si plus d'une css pour ce media ou si c'est une css dynamique
		if (count($s)>1 OR is_array(reset($s))){
			if (list($src,$comms) = filtre_cache_static($s,'css')){
				$compacte_ecrire_balise_link = charger_fonction('compacte_ecrire_balise_link','');
				$s = array_keys($s);
				$flux = str_replace(reset($s),
								$comms . $compacte_ecrire_balise_link($src,$m)."\n",
								$flux);
				$flux = str_replace($s,"",$flux);
			}
		}
	}

	return $flux;
}


// http://doc.spip.org/@filtre_cache_static
function filtre_cache_static($scripts,$type='js'){
	$nom = "";
	if (!is_array($scripts) && $scripts) $scripts = array($scripts);
	if (count($scripts)){
		// on trie la liste de scripts pour calculer le nom
		// necessaire pour retomber sur le meme fichier
		// si on renome une url a la volee pour enlever le var_mode=recalcul
		// mais attention, il faut garder l'ordre initial pour la minification elle meme !
		$s2 = $scripts;
		ksort($s2);
		$dir = sous_repertoire(_DIR_VAR,'cache-'.$type);
		$nom = $dir . md5(serialize($s2)) . ".$type";
		if (
			$GLOBALS['var_mode']=='recalcul'
			OR !file_exists($nom)
		) {
			$fichier = "";
			$comms = array();
			$total = 0;
			$s2 = false;
			foreach($scripts as $key=>$script){
				if (!is_array($script)) {
					// c'est un fichier
					$comm = $script;
					// enlever le timestamp si besoin
					$script = preg_replace(",[?].+$,",'',$script);
					if ($type=='css'){
						$fonctions = array('urls_absolues_css');
						if (isset($GLOBALS['compresseur_filtres_css']) AND is_array($GLOBALS['compresseur_filtres_css']))
							$fonctions = $GLOBALS['compresseur_filtres_css'] + $fonctions;
						$script = appliquer_fonctions_css_fichier($fonctions, $script);
					}
					lire_fichier($script, $contenu);
				}
				else {
					// c'est un squelette
					$comm = _SPIP_PAGE . "=$script[0]"
						. (strlen($script[1])?"($script[1])":'');
					parse_str($script[1],$contexte);
					$contenu = recuperer_fond($script[0],$contexte);
					if ($type=='css'){
						$fonctions = array('urls_absolues_css');
						if (isset($GLOBALS['compresseur_filtres_css']) AND is_array($GLOBALS['compresseur_filtres_css']))
							$fonctions = $GLOBALS['compresseur_filtres_css'] + $fonctions;
						$contenu = appliquer_fonctions_css_contenu($fonctions, $contenu, self('&'));
					}
					// enlever le var_mode si present pour retrouver la css minifiee standard
					if (strpos($script[1],'var_mode')!==false) {
						if (!$s2) $s2 = $scripts;
						unset($s2[$key]);
						$key = preg_replace(',(&(amp;)?)?var_mode=[^&\'"]*,','',$key);
						$script[1] = preg_replace(',&?var_mode=[^&\'"]*,','',$script[1]);
						$s2[$key] = $script;
					}
				}
				$f = 'compacte_'.$type;
					$fichier .= "/* $comm */\n". $f($contenu) . "\n\n";
				$comms[] = $comm;
				$total += strlen($contenu);
			}

			// calcul du % de compactage
			$pc = intval(1000*strlen($fichier)/$total)/10;
			$comms = "compact [\n\t".join("\n\t", $comms)."\n] $pc%";
			$fichier = "/* $comms */\n\n".$fichier;

			if ($s2) {
				ksort($s2);
				$nom = $dir . md5(serialize($s2)) . ".$type";
			}

			// ecrire
			ecrire_fichier($nom,$fichier,true);
			// ecrire une version .gz pour content-negociation par apache, cf. [11539]
			ecrire_fichier("$nom.gz",$fichier,true);
			// closure compiler ou autre super-compresseurs
			$nom = compresse_encore($nom, $type);
		}


	}

	// Le commentaire detaille n'apparait qu'au recalcul, pour debug
	return array($nom, (isset($comms) AND $comms) ? "<!-- $comms -->\n" : '');
}

// experimenter le Closure Compiler de Google
function compresse_encore (&$nom, $type) {
	# Closure Compiler n'accepte pas des POST plus gros que 200 000 octets
	# au-dela il faut stocker dans un fichier, et envoyer l'url du fichier
	# dans code_url ; en localhost ca ne marche evidemment pas
	if (
	$GLOBALS['meta']['auto_compress_closure'] == 'oui'
	AND $type=='js'
	) {
		$nom = compacte_js_more($nom,true);
	}
	return $nom;
}

function appliquer_fonctions_css_fichier($fonctions,$css) {
	if (!preg_match(',\.css$,i', $css, $r)) return $css;

	$url_absolue_css = url_absolue($css);

	// verifier qu'on a un array
	if (is_string($fonctions))
		$fonctions = array($fonctions);

	$sign = implode(",",$fonctions);
	$sign = substr(md5("$css-$sign"), 0,8);

	$file = basename($css,'.css');
	$file = sous_repertoire (_DIR_VAR, 'cache-css')
		. preg_replace(",(.*?)(_rtl|_ltr)?$,","\\1-f-" . $sign . "\\2",$file)
		. '.css';

	if ((@filemtime($f) > @filemtime($css))
	AND ($GLOBALS['var_mode'] != 'recalcul'))
		return $f;

	if ($url_absolue_css==$css){
		if (strncmp($GLOBALS['meta']['adresse_site'],$css,$l=strlen($GLOBALS['meta']['adresse_site']))!=0
		 OR !lire_fichier(_DIR_RACINE . substr($css,$l), $contenu)){
		 		include_spip('inc/distant');
		 		if (!$contenu = recuperer_page($css))
					return $css;
		}
	}
	elseif (!lire_fichier($css, $contenu))
		return $css;

	$contenu = appliquer_fonctions_css_contenu($fonctions, $contenu, $css);

	// ecrire la css
	if (!ecrire_fichier($file, $contenu))
		return $css;

	return $file;
}

function appliquer_fonctions_css_contenu($fonctions, &$contenu, $base) {
	foreach($fonctions as $f)
		if (function_exists($f))
			$contenu = $f($contenu, $base);
	return $contenu;
}


function compresseur_embarquer_images_css($contenu, $source){
	#$path = suivre_lien(url_absolue($source),'./');
	$base = ((substr($source,-1)=='/')?$source:(dirname($source).'/'));

	return preg_replace_callback(
		",url\s*\(\s*['\"]?([^'\"/][^:]*[.](png|gif|jpg))['\"]?\s*\),Uims",
		create_function('$x',
			'return "url(\"".filtre_embarque_fichier($x[1],"'.$base.'")."\")";'
		), $contenu);
}