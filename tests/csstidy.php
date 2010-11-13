<?php
/**
 * Test unitaire de
 * du fichier
 *
 */

	$test = 'atmedia';
	$remonte = "../";
	while (!is_dir($remonte."ecrire"))
		$remonte = "../$remonte";
	require $remonte.'tests/test.inc';

	include_spip('csstidy/testing/css_results');
	include_spip('csstidy/class.csstidy');

	ini_set('display_errors','On');

$css = new csstidy();

$css->set_cfg('preserve_css',false);
$css_code = file_get_contents(find_in_path('csstidy/testing/fisubsilver.css'));

$css->parse($css_code);

if($css->css !== $xhtml_result) {
    echo '<div style="color:red">XHTML failed!</div>';
		echo "<pre>".serialize($css->css)."</pre><br>";
		echo "<pre>".serialize($xhtml_result)."</pre><br>";
		var_dump($css->css);
}
flush();

$css_code = file_get_contents(find_in_path('csstidy/testing/base.css'));

$css->parse($css_code);

if($css->css !== $ala_result) {
    echo '<div style="color:red">ALA failed!</div>';
		echo "<pre>".serialize($css->css)."</pre><br>";
		echo "<pre>".serialize($ala_result)."</pre><br>";
		var_dump($css->css);
}
flush();

$css->set_cfg('remove_last_;',true);

if($css->print->formatted() !== $ala_html) {
    echo '<div style="color:red">ALA HTML failed!</div>';
}
flush();

$css->set_cfg('optimise_shorthands',false);
$css->set_cfg('merge_selectors',1);

$css->parse($css_code);

if($css->css !== $ala_options_result) {
    echo '<div style="color:red">ALA +options failed!</div>';
		echo "<pre>".serialize($css->css)."</pre><br>";
		echo "<pre>".serialize($ala_options_result)."</pre><br>";
		var_dump($css->css);
}
flush();

echo "OK";

?>