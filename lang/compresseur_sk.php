<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
// extrait automatiquement de http://www.spip.net/trad-lang/
// ** ne pas modifier le fichier **

if (!defined('_ECRIRE_INC_VERSION')) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// I
	'info_compresseur_titre' => 'Optimisations and compression', # MODIF
	'info_question_activer_compactage_css' => 'Do you wish to activate compression for CSS stylesheets?', # MODIF
	'info_question_activer_compactage_js' => 'Do you wish to activate compression for Javascript files?', # MODIF
	'info_question_activer_compresseur' => 'Do you wish to activate compression for the HTTP data?', # MODIF
	'item_compresseur' => 'Activate compression', # MODIF
	'item_compresseur_closure' => 'Utiliser Google Closure Compiler [expérimental]', # NEW

	// T
	'texte_compacter_avertissement' => 'Be careful not to activate these options during the development of your site: compressed elements become difficult to read and debug.',
	'texte_compacter_script_css' => 'SPIP can compact Javascript files and CSS stylesheets and save them as static files. This accelerates the display of the site.',
	'texte_compresseur_page' => 'SPIP can automatically compress each page that it sends. This option reduces the bandwidth used, making the site faster for lower speed connections), but it does require more resources from the server.',
	'titre_compacter_script_css' => 'Compression of scripts and CSS', # MODIF
	'titre_compresser_flux_http' => 'Compression of HTTP data' # MODIF
);

?>
