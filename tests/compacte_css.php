<?php
/**
 * Test unitaire de compacte_css
 * du fichier inc/compresseur
 *
 */

	$test = 'compacte_css';
	$remonte = "../";
	while (!is_dir($remonte."ecrire"))
		$remonte = "../$remonte";
	require $remonte.'tests/test.inc';
	$ok = true;

	include_spip('inc/compresseur');

	lire_fichier(dirname(__FILE__)."/css/source.css", $css_code);

	// test du compacteur simple
	lire_fichier(dirname(__FILE__)."/css/expected.css", $expected);

	$compacte = compacte_css($css_code);
	if (rtrim($compacte)!=rtrim($expected)) {
		erreur("compacte_css('simple')",$compacte,$expected);
		$ok = false;
	}

	lire_fichier(dirname(__FILE__)."/css/expected_more.css", $expected);
	$compacte = compacte_css($css_code,array());
	if (rtrim($compacte)!=rtrim($expected)) {
		erreur("compacte_css(array())",$compacte,$expected);
		$ok = false;
	}

	lire_fichier(dirname(__FILE__)."/css/expected_more_screen.css", $expected);
	$compacte = compacte_css($css_code,array('media'=>'screen'));
	if (rtrim($compacte)!=rtrim($expected)) {
		erreur("compacte_css(array('media'=>'screen'))",$compacte,$expected);
		$ok = false;
	}

	lire_fichier(dirname(__FILE__)."/css/expected_highest_screen.css", $expected);
	$compacte = compacte_css($css_code,array('media'=>'screen','template'=>'highest_compression'));
	if (rtrim($compacte)!=rtrim($expected)) {
		erreur("compacte_css(array('media'=>'screen','template'=>'highest_compression'))",$compacte,$expected);
		$ok = false;
	}

	if ($ok)
		echo "OK";

	function erreur($titre,$result,$expected){
		echo "Erreur $titre<br />";
		echo "<tt>Resultat:</tt><pre>$result</pre>";
		echo "<tt>Attendu :</tt><pre>$expected</pre>";
	}
?>