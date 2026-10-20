<?php
/**
 * Puslapio viršus: <head>, viršutinė juosta ir meniu.
 * Naudoja template-pradzia.php ir template-vidinis.php.
 */
if (!defined('ABSPATH')) exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#14171A">
<?php wp_head(); ?>
</head>
<body <?php body_class('atp'); ?>>
<a class="sr" href="#turinys">Praleisti į turinį</a>
<?php $atp_pr = is_front_page() ? '' : home_url('/'); /* vidiniuose puslapiuose inkarai veda į pradžios puslapį */ ?>
<header class="virsus" id="virsus">
  <div class="k">
    <a class="zenklas" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(ATP_IMONE); ?>, į pradžią">
      <svg viewBox="0 0 96 28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square" aria-hidden="true"><path d="M2 26 L14 2 L26 26"/><path d="M8 17 H20"/><path d="M32 3 H58"/><path d="M45 3 V26"/><path d="M39 26 H51"/><path d="M66 26 V2 H80 A7 7 0 0 1 80 16 H66"/></svg>
      <b>Sprendimai</b>
    </a>
    <nav class="meniu" aria-label="Pagrindinis meniu">
<?php if (has_nav_menu('pagrindinis')) {
  wp_nav_menu(array('theme_location' => 'pagrindinis', 'container' => false, 'depth' => 1, 'items_wrap' => '<ul>%3$s</ul>'));
} else { ?>
      <a href="<?php echo esc_url($atp_pr . '#paslaugos'); ?>">Paslaugos</a>
      <a href="<?php echo esc_url($atp_pr . '#procesas'); ?>">Kaip dirbame</a>
      <a href="<?php echo esc_url($atp_pr . '#medziagos'); ?>">Medžiagos</a>
      <a href="<?php echo esc_url($atp_pr . '#projektai'); ?>">Gaminiai</a>
      <a href="<?php echo esc_url($atp_pr . '#apie'); ?>">Apie mus</a>
      <a href="<?php echo esc_url($atp_pr . '#uzsakymas'); ?>">Užsakymas</a>
<?php } ?>
    </nav>
    <a class="tel" href="tel:<?php echo esc_attr(ATP_TEL_LINK); ?>"><?php echo esc_html(ATP_TEL); ?></a>
  </div>
</header>
