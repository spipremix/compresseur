<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
// extrait automatiquement de http://trad.spip.org
// ** ne pas modifier le fichier **

if (!defined('_ECRIRE_INC_VERSION')) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// I
	'info_compresseur_titre' => 'Optimizaciones y compresión',
	'info_question_activer_compactage_css' => '¿Desea activar la compresión de las hojas de estilo (CSS)?', # MODIF
	'info_question_activer_compactage_js' => '¿Desea activar la compresión de los scripts (javascript) ?', # MODIF
	'info_question_activer_compresseur' => '¿Desea activar la compresión del flujo HTTP ?', # MODIF
	'item_compresseur_closure' => 'Utiliser Google Closure Compiler [expérimental]', # NEW
	'item_compresseur_css' => 'Activer la compression des feuilles de styles (CSS)', # NEW
	'item_compresseur_html' => 'Activer la compression du HTML', # NEW
	'item_compresseur_js' => 'Activer la compression des scripts (javascript)', # NEW

	// T
	'texte_compacter_avertissement' => 'Atención a no activar estas opciones durante el desarrollo de tu sitio: los elementos compactados pierden toda legibilidad.',
	'texte_compacter_script_css' => 'SPIP puede compactar los scripts javascript y las hojas de estilo CSS, para almacenarlos en ficheros estáticos; esto acelera la presentación del sitio.',
	'texte_compresseur_page' => 'SPIP puede comprimir automáticamente cada página que envía a los
visitantes del sitio. Este ajuste permite optimizar la banda pasante (el
sitio es más rápido en caso de una conexión de baja banda pasante), pero
requiere más potencia del servidor.',
	'titre_compacter_script_css' => 'Compresión de los scripts y CSS',
	'titre_compresser_flux_http' => 'Compresión del flujo HTTP' # MODIF
);

?>
