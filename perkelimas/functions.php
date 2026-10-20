<?php
/**
 * Salient ATP — dukterinė tema (ATP Sprendimai).
 * Dizainas: assets/atp.css ir assets/atp.js. Šablonai: template-pradzia.php, template-vidinis.php.
 */
if (!defined('ABSPATH')) exit;

define('ATP_VER', '1.0.0');
define('ATP_URI', get_stylesheet_directory_uri());
define('ATP_DIR', get_stylesheet_directory());

/* ─────────────────────────────────────────────────────────────
   ĮMONĖS DUOMENYS — vienintelė vieta, kurią reikia redaguoti.
   ───────────────────────────────────────────────────────────── */
define('ATP_IMONE',        'ATP Sprendimai');
define('ATP_ADRESAS',      'Titnago g. 19, Vilnius');
define('ATP_TEL',          '+370 608 08909');
define('ATP_TEL_LINK',     '+37060808909');
define('ATP_EL',           'info@atpsprendimai.lt');
define('ATP_LAIKAS',       'I–V 8.00–17.00');

/* Rekvizitai. Kol reikšmė tuščia, lentelėje rodoma „—*“. */
define('ATP_IMONES_KODAS', '');
define('ATP_PVM_KODAS',    '');
define('ATP_BANKAS',       '');
define('ATP_SASKAITA',     '');

/* Kur siųsti užklausas iš formos. */
define('ATP_UZKLAUSU_EL',  ATP_EL);

/** Failo versija iš pakeitimo laiko — kad naršyklė neįstrigtų sename CSS. */
function atp_versija($kelias) {
    $failas = ATP_DIR . $kelias;
    return file_exists($failas) ? (string) filemtime($failas) : ATP_VER;
}

/** Stiliai ir skriptai. */
function atp_stiliai() {
    wp_enqueue_style('salient-parent', get_template_directory_uri() . '/style.css', array(), ATP_VER);
    wp_enqueue_style('atp-sriftai', ATP_URI . '/assets/fonts.css', array(), ATP_VER);
    wp_enqueue_style('atp-stilius', ATP_URI . '/assets/atp.css', array('salient-parent', 'atp-sriftai'), atp_versija('/assets/atp.css'));
    wp_enqueue_script('atp-skriptas', ATP_URI . '/assets/atp.js', array(), atp_versija('/assets/atp.js'), true);
}
add_action('wp_enqueue_scripts', 'atp_stiliai', 9999); // vėlyvas prioritetas — po Salient stilių

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    register_nav_menus(array('pagrindinis' => 'Pagrindinis meniu (ATP)'));
});

/** Brėžinių formatai medijos bibliotekai. */
add_filter('upload_mimes', function ($mimes) {
    $mimes['dwg'] = 'image/vnd.dwg';
    $mimes['dxf'] = 'image/vnd.dxf';
    return $mimes;
});

/** Kūno klasė ATP šablonams. */
add_filter('body_class', function ($klases) {
    if (is_page_template(array('template-pradzia.php', 'template-vidinis.php'))) {
        $klases[] = 'atp-sablonas';
    }
    return $klases;
});

require_once ATP_DIR . '/inc/forma.php';

/** Užsakymo forma konstruktoriaus puslapiuose: trumpinys [atp_forma]. */
add_shortcode('atp_forma', function () {
    ob_start();
    atp_forma();
    return ob_get_clean();
});

/** Puslapiai redaguojami WPBakery konstruktoriumi, todėl blokų redaktorių puslapiams išjungiame. */
add_filter('use_block_editor_for_post_type', function ($naudoti, $tipas) {
    return $tipas === 'page' ? false : $naudoti;
}, 10, 2);


/* ─────────────────────────────────────────────────────────────
   ADMINISTRAVIMO IŠVAIZDA — Simplex v8
   Paslepia tėvinės temos meniu bei pranešimus ir prideda
   priežiūros kontaktus į skydelį.
   ───────────────────────────────────────────────────────────── */
define('ATP_PRIEZIURA_IMONE', 'Simplex');
define('ATP_PRIEZIURA_EL',    'arvydas@simplex.lt');
define('ATP_PRIEZIURA_TEL',   '+370 601 314 53');
define('ATP_PRIEZIURA_WWW',   'https://simplex.lt');

/** Paslepia tėvinės temos meniu punktą. */
add_action('admin_menu', function () {
    // Tėvinė tema meniu raktą susideda iš temos pavadinimo be tarpų.
    $raktas = str_replace(' ', '', (string) wp_get_theme()->get('Name'));
    remove_menu_page($raktas);
    remove_menu_page('SalientATP');
    remove_menu_page('Simplexv8');
    remove_submenu_page('index.php', 'salient-welcome-screen');
}, 999);

/** Atsarginis variantas, jei meniu įdedamas vėliau. */
add_action('admin_head', function () {
    $raktas = sanitize_html_class(str_replace(' ', '', (string) wp_get_theme()->get('Name')));
    echo '<style>#toplevel_page_' . $raktas . ',#toplevel_page_SalientATP{display:none!important}</style>';
});

/** Paslepia įskiepių reikalavimo pranešimą ir temos sveikinimo ekraną. */
add_action('admin_head', function () {
    echo '<style>#setting-error-tgmpa,.tgmpa.notice,#nectar-welcome-notice{display:none!important}</style>';
});

/** Skydelio langelis su priežiūros kontaktais. */
add_action('wp_dashboard_setup', function () {
    wp_add_dashboard_widget('atp_prieziura', 'Svetainės priežiūra', function () {
        echo '<p style="margin-top:0">Svetainę sukūrė ir prižiūri <strong>' . esc_html(ATP_PRIEZIURA_IMONE) . '</strong>. Kilus klausimų dėl turinio, dizaino ar veikimo, susisiekite:</p>';
        echo '<p style="font-size:15px;line-height:1.9;margin:0">';
        echo 'El. paštas: <a href="mailto:' . esc_attr(ATP_PRIEZIURA_EL) . '">' . esc_html(ATP_PRIEZIURA_EL) . '</a><br>';
        echo 'Telefonas: <a href="tel:' . esc_attr(str_replace(' ', '', ATP_PRIEZIURA_TEL)) . '">' . esc_html(ATP_PRIEZIURA_TEL) . '</a><br>';
        echo 'Internete: <a href="' . esc_url(ATP_PRIEZIURA_WWW) . '" target="_blank" rel="noopener">simplex.lt</a>';
        echo '</p>';
        echo '<hr style="margin:14px 0;border:0;border-top:1px solid #e0e0e0">';
        echo '<p style="margin:0"><strong>Turinio redagavimas.</strong> Visi puslapiai sudėlioti WPBakery konstruktoriumi: '
           . '<a href="' . esc_url(admin_url('edit.php?post_type=page')) . '">Puslapiai</a> &rarr; pasirinkite puslapį &rarr; '
           . 'užveskite pelę ant teksto ar nuotraukos ir spauskite pieštuko piktogramą. Baigę spauskite <em>Atnaujinti</em>. '
           . 'Klasių, prasidedančių <code>atp-</code>, trinti nereikia — jos suteikia dizainą.</p>';
    });
});

/** Administravimo poraštė. */
add_filter('admin_footer_text', function () {
    return 'Svetainė veikia su <strong>Simplex v8</strong> tema. Priežiūra: <a href="mailto:' . esc_attr(ATP_PRIEZIURA_EL) . '">' . esc_html(ATP_PRIEZIURA_EL) . '</a>, ' . esc_html(ATP_PRIEZIURA_TEL) . '.';
});
