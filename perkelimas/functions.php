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
   ĮMONĖS DUOMENYS — antraštė, footeris, užsakymo forma ir senasis
   šablonas (template-pradzia.php). Pradžios puslapio tekstai,
   telefonai, adresas ir rekvizitai keičiami konstruktoriuje
   (turinį generuoja wp/konstruktorius/sukurti.py) — pasikeitus
   duomenims, juos reikia pakeisti abiejose vietose.
   ───────────────────────────────────────────────────────────── */
define('ATP_IMONE',        'ATP Sprendimai');
define('ATP_ADRESAS',      'Titnago g. 19, Vilnius');
define('ATP_TEL',          '+370 608 08909');
define('ATP_TEL_LINK',     '+37060808909');
define('ATP_EL',           'info@atpsprendimai.lt');
define('ATP_LAIKAS',       'I–V 8.00–17.00');

/* Rekvizitai. Kol reikšmė tuščia, lentelėje rodoma „—*“. */
define('ATP_IMONES_KODAS', '301673180');
define('ATP_PVM_KODAS',    'LT100003948819');
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

/** Vidiniuose puslapiuose WP meniu inkarai (#paslaugos) veda į pradžios puslapį. */
add_filter('nav_menu_link_attributes', function ($atributai) {
    if (!is_front_page() && isset($atributai['href']) && strpos($atributai['href'], '#') === 0) {
        $atributai['href'] = home_url('/') . $atributai['href'];
    }
    return $atributai;
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

/** SEO: meta aprašymas, „Open Graph“ ir organizacijos duomenys (kai nėra SEO įskiepio). */
add_action('wp_head', function () {
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION')) return;
    if (is_front_page()) {
        $aprasymas = 'Metalo gamyba Vilniuje: konstrukcijos pramonei pagal brėžinius, tvoros ir turėklai, armatūros karkasai ir gaminiai statyboms. Gamyba ir montavimas.';
    } elseif (is_singular() && has_excerpt()) {
        $aprasymas = wp_strip_all_tags(get_the_excerpt());
    } else {
        $aprasymas = ATP_IMONE . ' — nestandartiniai metalo gaminiai iš juodo ir nerūdijančio plieno. ' . ATP_ADRESAS . '.';
    }
    $adresas = is_front_page() ? home_url('/') : get_permalink();
    $vaizdas = ATP_URI . '/assets/img/poster-31806788.jpg';
    echo '<meta name="description" content="' . esc_attr($aprasymas) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:locale" content="lt_LT">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(ATP_IMONE) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(wp_get_document_title()) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($aprasymas) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($adresas) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($vaizdas) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    if (is_front_page()) {
        $org = array('@context' => 'https://schema.org', '@type' => 'LocalBusiness', 'name' => ATP_IMONE, 'url' => home_url('/'),
            'telephone' => ATP_TEL_LINK, 'email' => ATP_EL, 'image' => $vaizdas,
            'address' => array('@type' => 'PostalAddress', 'streetAddress' => 'Titnago g. 19', 'addressLocality' => 'Vilnius', 'addressCountry' => 'LT'));
        echo '<script type="application/ld+json">' . wp_json_encode($org, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
        // Pradžios ekrano poster — pirmas matomas vaizdas
        echo '<link rel="preload" as="image" href="' . esc_url($vaizdas) . '" fetchpriority="high">' . "\n";
    }
}, 2);

/** Salient išjungia WP „loading=lazy“ (wp_lazy_loading_enabled → false), todėl telefone iš karto būtų
    parsiunčiamos visos 18 pradžios puslapio nuotraukų (~4 MB). Konstruktoriaus nuotraukoms (Image with animation)
    grąžiname naršyklės „lazy“, kaip demo. Salient image_loading="lazy-load" nenaudojame — jis prideda išryškėjimą. */
add_filter('wp_content_img_tag', function ($img) {
    if (false === strpos($img, ' loading=') && false !== strpos($img, 'img-with-animation')) {
        $img = str_replace('<img ', '<img loading="lazy" ', $img);
    }
    return $img;
});

/** Pradžios puslapio turinio nuotraukos yra žemiau pirmo ekrano, todėl „fetchpriority=high“ joms nereikia. */
add_filter('wp_get_loading_optimization_attributes', function ($atributai) {
    if (is_front_page()) unset($atributai['fetchpriority']);
    return $atributai;
});

/** Be išorinių užklausų: Salient Open Sans iš Google ir WP jaustukų skriptas (BDAR, greitis). */
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('nectar_default_font_open_sans');
    wp_deregister_style('nectar_default_font_open_sans');
}, 10000);
add_filter('wp_resource_hints', function ($nuorodos, $tipas) {
    if ('dns-prefetch' === $tipas || 'preconnect' === $tipas) {
        $nuorodos = array_filter($nuorodos, function ($n) {
            $u = is_array($n) ? ($n['href'] ?? '') : $n;
            return strpos($u, 'fonts.googleapis') === false && strpos($u, 'fonts.gstatic') === false;
        });
    }
    return $nuorodos;
}, 10, 2);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');


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
