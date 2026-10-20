<?php
/**
 * Puslapio apačia: footeris ir wp_footer().
 */
if (!defined('ABSPATH')) exit;
$atp_pr = is_front_page() ? '' : home_url('/'); /* vidiniuose puslapiuose inkarai veda į pradžios puslapį */
?>
<footer class="footeris">
  <div class="k">
    <div>
      <a class="zenklas" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(ATP_IMONE); ?>">
        <svg viewBox="0 0 96 28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square" aria-hidden="true"><path d="M2 26 L14 2 L26 26"/><path d="M8 17 H20"/><path d="M32 3 H58"/><path d="M45 3 V26"/><path d="M39 26 H51"/><path d="M66 26 V2 H80 A7 7 0 0 1 80 16 H66"/></svg>
        <b>Sprendimai</b>
      </a>
      <p class="mazas" style="color:var(--dumas);margin-top:14px;max-width:34ch">Nestandartiniai metalo gaminiai iš juodo ir nerūdijančio plieno.</p>
    </div>
    <div>
      <h4>Paslaugos</h4>
      <ul>
        <li><a href="<?php echo esc_url($atp_pr . '#paslaugos'); ?>">Gaminiai pramonei</a></li>
        <li><a href="<?php echo esc_url($atp_pr . '#paslaugos'); ?>">Mažoji architektūra</a></li>
        <li><a href="<?php echo esc_url($atp_pr . '#paslaugos'); ?>">Gaminiai statyboms</a></li>
        <li><a href="<?php echo esc_url($atp_pr . '#procesas'); ?>">Kaip dirbame</a></li>
        <li><a href="<?php echo esc_url($atp_pr . '#medziagos'); ?>">Medžiagos</a></li>
        <li><a href="<?php echo esc_url($atp_pr . '#duk'); ?>">Dažni klausimai</a></li>
      </ul>
    </div>
    <div>
      <h4>Kontaktai</h4>
      <ul>
        <li><?php echo esc_html(ATP_ADRESAS); ?></li>
        <li><a href="tel:<?php echo esc_attr(ATP_TEL_LINK); ?>"><?php echo esc_html(ATP_TEL); ?></a></li>
        <li><a href="mailto:<?php echo esc_attr(ATP_EL); ?>"><?php echo esc_html(ATP_EL); ?></a></li>
        <li><a href="<?php echo esc_url($atp_pr . '#uzsakymas'); ?>">Pateikti užklausą</a></li>
      </ul>
    </div>
    <p class="footeris__apacia mono">
      <span>&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php echo esc_html(ATP_IMONE); ?></span>
      <span>Vaizdinė medžiaga: Pexels</span>
      <?php if (get_privacy_policy_url()) : /* nuoroda tik tada, kai WP nustatytas privatumo politikos puslapis */ ?>
      <span><a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Privatumo politika</a></span>
      <?php endif; ?>
    </p>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
