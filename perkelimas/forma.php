<?php
/**
 * Užklausos forma: atvaizdavimas ir laiško siuntimas.
 * Be papildomų įskiepių — laiškas siunčiamas per wp_mail().
 * Apsauga: nonce, spąstų laukas robotams, dažnio riba pagal IP.
 */
if (!defined('ABSPATH')) exit;

/** Leidžiami prisegamų failų tipai. */
function atp_leidziami_failai() {
    return array(
        'pdf'      => 'application/pdf',
        'dwg'      => 'image/vnd.dwg',
        'dxf'      => 'image/vnd.dxf',
        'xlsx'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg|jpeg' => 'image/jpeg',
        'png'      => 'image/png',
    );
}

/** Formos atvaizdavimas. Kviečiama per trumpinį [atp_forma] (functions.php) ir iš template-pradzia.php. */
function atp_forma() {
    $busena = isset($_GET['atp']) ? sanitize_key(wp_unslash($_GET['atp'])) : '';
    ?>
      <form class="forma" id="uzklausa" method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
        <?php if ($busena === 'ok') : ?>
          <p class="forma__pranesimas" role="status">Ačiū, užklausa gauta. Susisieksime nurodytu el. paštu arba telefonu.</p>
        <?php elseif ($busena === 'failas') : ?>
          <p class="forma__pranesimas forma__pranesimas--klaida" role="alert">Failo įkelti nepavyko. Leidžiami PDF, DWG, DXF, XLSX, JPG ir PNG failai iki 20 MB.</p>
        <?php elseif ($busena === 'klaida') : ?>
          <p class="forma__pranesimas forma__pranesimas--klaida" role="alert">Užklausos išsiųsti nepavyko. Patikrinkite privalomus laukus arba parašykite el. paštu <?php echo esc_html(ATP_EL); ?>.</p>
        <?php endif; ?>
        <input type="hidden" name="action" value="atp_uzklausa">
        <input type="hidden" name="atp_grizti" value="<?php echo esc_url(get_permalink()); ?>">
        <?php wp_nonce_field('atp_uzklausa', 'atp_nonce'); ?>
        <p class="sr" aria-hidden="true"><label>Nepildykite šio lauko <input type="text" name="atp_papildomai" tabindex="-1" autocomplete="off"></label></p>

        <div class="forma__vidus">
          <div class="eile">
            <div class="laukas">
              <label for="vardas">Vardas <span class="zvaigzde">*</span></label>
              <input id="vardas" name="vardas" type="text" autocomplete="given-name" required>
              <span class="pranesimas">Prašome įvesti vardą.</span>
            </div>
            <div class="laukas">
              <label for="pavarde">Pavardė</label>
              <input id="pavarde" name="pavarde" type="text" autocomplete="family-name">
            </div>
          </div>
          <div class="eile">
            <div class="laukas">
              <label for="elpastas">El. paštas <span class="zvaigzde">*</span></label>
              <input id="elpastas" name="elpastas" type="email" placeholder="vardas@imone.lt" autocomplete="email" required>
              <span class="pranesimas">Prašome įvesti teisingą el. pašto adresą.</span>
            </div>
            <div class="laukas">
              <label for="telefonas">Telefonas</label>
              <input id="telefonas" name="telefonas" type="tel" placeholder="+370" autocomplete="tel">
            </div>
          </div>
          <div class="eile">
            <div class="laukas">
              <label for="imone">Įmonė</label>
              <input id="imone" name="imone" type="text" autocomplete="organization">
              <span class="pagalba">Neprivaloma</span>
            </div>
            <div class="laukas">
              <label for="tema">Dėl ko kreipiatės</label>
              <select id="tema" name="tema">
                <option>Gaminiai pramonei</option>
                <option>Mažoji architektūra</option>
                <option>Gaminiai statyboms</option>
                <option>Kita</option>
              </select>
            </div>
          </div>
          <div class="laukas">
            <label for="pranesimas">Pranešimas</label>
            <textarea id="pranesimas" name="pranesimas" placeholder="Trumpai aprašykite gaminį, matmenis, kiekį ir pageidaujamą terminą."></textarea>
          </div>
          <div class="laukas">
            <label for="brezinys">Brėžinys arba nuotrauka</label>
            <div class="failas" id="failo-zona">
              Nutempkite failą čia arba pasirinkite
              <input id="brezinys" name="brezinys" type="file" accept=".pdf,.dwg,.dxf,.xlsx,.jpg,.jpeg,.png">
              <span class="pagalba" id="failo-info">PDF, DWG, DXF, XLSX, JPG, PNG · iki 20 MB</span>
            </div>
          </div>
          <label class="sutikimas" for="sutikimas">
            <input id="sutikimas" name="sutikimas" type="checkbox" value="1" required>
            <span>Sutinku, kad mano pateikti duomenys būtų naudojami atsakymui į užklausą<?php
              $atp_pp = get_privacy_policy_url();
              if ($atp_pp) : ?> (<a href="<?php echo esc_url($atp_pp); ?>" target="_blank" rel="noopener">privatumo politika</a>)<?php endif; ?>. <span class="zvaigzde">*</span></span>
          </label>
          <div class="forma__apacia">
            <button class="mygtukas mygtukas--karstas" type="submit">Siųsti užklausą</button>
            <span class="mazas">Laukai su <span class="zvaigzde">*</span> yra privalomi.</span>
          </div>
</div>

      </form>
    <?php
}

/** Užklausos priėmimas ir siuntimas. */
function atp_uzklausa_priimti() {
    $grizti = isset($_POST['atp_grizti']) ? esc_url_raw(wp_unslash($_POST['atp_grizti'])) : home_url('/');

    if (!isset($_POST['atp_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['atp_nonce'])), 'atp_uzklausa')) {
        atp_grazinti($grizti, 'klaida');
    }
    if (!empty($_POST['atp_papildomai'])) {           // spąstai robotams
        atp_grazinti($grizti, 'ok');
    }

    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $raktas = 'atp_uzkl_' . md5($ip);
    $kiek = (int) get_transient($raktas);
    if ($kiek >= 5) {                                  // daugiausia 5 užklausos per valandą iš to paties IP
        atp_grazinti($grizti, 'klaida');
    }
    set_transient($raktas, $kiek + 1, HOUR_IN_SECONDS);

    $vardas   = sanitize_text_field(wp_unslash(isset($_POST['vardas']) ? $_POST['vardas'] : ''));
    $pavarde  = sanitize_text_field(wp_unslash(isset($_POST['pavarde']) ? $_POST['pavarde'] : ''));
    $elpastas = sanitize_email(wp_unslash(isset($_POST['elpastas']) ? $_POST['elpastas'] : ''));
    $telefonas= sanitize_text_field(wp_unslash(isset($_POST['telefonas']) ? $_POST['telefonas'] : ''));
    $imone    = sanitize_text_field(wp_unslash(isset($_POST['imone']) ? $_POST['imone'] : ''));
    $tema     = sanitize_text_field(wp_unslash(isset($_POST['tema']) ? $_POST['tema'] : ''));
    $zinute   = sanitize_textarea_field(wp_unslash(isset($_POST['pranesimas']) ? $_POST['pranesimas'] : ''));

    if ($vardas === '' || !is_email($elpastas) || empty($_POST['sutikimas'])) {
        atp_grazinti($grizti, 'klaida');
    }

    $priedai = array();
    if (!empty($_FILES['brezinys']['name'])) {
        if ((int) $_FILES['brezinys']['size'] > 20 * MB_IN_BYTES) {
            atp_grazinti($grizti, 'failas');
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $ikelta = wp_handle_upload($_FILES['brezinys'], array('test_form' => false, 'mimes' => atp_leidziami_failai()));
        if (!is_array($ikelta) || isset($ikelta['error'])) {
            atp_grazinti($grizti, 'failas');
        }
        $priedai[] = $ikelta['file'];
    }

    $eilutes = array(
        'Vardas: ' . $vardas . ' ' . $pavarde,
        'El. paštas: ' . $elpastas,
        'Telefonas: ' . ($telefonas !== '' ? $telefonas : '—'),
        'Įmonė: ' . ($imone !== '' ? $imone : '—'),
        'Dėl ko kreipiasi: ' . ($tema !== '' ? $tema : '—'),
        '',
        'Pranešimas:',
        ($zinute !== '' ? $zinute : '—'),
        '',
        'Prisegtas failas: ' . (empty($priedai) ? 'nėra' : basename($priedai[0])),
        'Puslapis: ' . $grizti,
    );

    $domenas = wp_parse_url(home_url(), PHP_URL_HOST);
    $antrastes = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . ATP_IMONE . ' <no-reply@' . $domenas . '>',
        'Reply-To: ' . $vardas . ' <' . $elpastas . '>',
    );

    wp_mail(ATP_UZKLAUSU_EL, 'Nauja užklausa iš svetainės: ' . ($tema !== '' ? $tema : 'bendra'), implode("\n", $eilutes), $antrastes, $priedai);

    atp_grazinti($grizti, 'ok');
}
add_action('admin_post_nopriv_atp_uzklausa', 'atp_uzklausa_priimti');
add_action('admin_post_atp_uzklausa', 'atp_uzklausa_priimti');

/** Grąžina lankytoją į formą su būsena. */
function atp_grazinti($adresas, $busena) {
    wp_safe_redirect(add_query_arg('atp', $busena, $adresas) . '#uzsakymas');
    exit;
}
