(function(){
  "use strict";
  var rm = matchMedia('(prefers-reduced-motion: reduce)');
  var taupyti = !!(navigator.connection && navigator.connection.saveData);

  /* ── Vienas rAF ciklas: paralakso reikšmė, antraštės būsena, atsiradimai ir video ── */
  var virsus = document.getElementById('virsus'), sy = 0, laukia = false;
  /* --sy rašomas tik pradžios ekrano video (ne :root): kitaip kiekvieną kadrą perskaičiuojami viso
     dokumento stiliai — su Salient CSS ir dvigubai didesniu DOM telefone slinkimas trūkčioja.
     CSS var(--sy) imamas iš paties video; žemiau ekrano (video nebematomas) nerašome visai. */
  var heroVideo = document.querySelector('#hero-video, .atp-hero video');
  var laukia_rodymo = [];   // elementai, laukiantys atsiradimo
  var laukia_video = [];    // video, paleidžiami tik priartėjus
  function kadras(){
    laukia = false;
    var y = window.scrollY || 0;
    if (Math.abs(y - sy) >= 1){ sy = y; if (!rm.matches && heroVideo && y < innerHeight * 1.5) heroVideo.style.setProperty('--sy', y); }
    if (virsus) virsus.classList.toggle('slinkta', y > 40);
    if (laukia_rodymo.length) rodymo_patikra();
    if (laukia_video.length) video_patikra();
  }
  addEventListener('scroll', function(){ if (!laukia){ laukia = true; requestAnimationFrame(kadras); } }, {passive:true});

  /* ── Sklandus slinkimas (html{scroll-behavior:smooth}) įjungiamas tik po įkėlimo (atp.css: html:not(.atp-sklandu)).
        Atėjus su inkaru (/#kontaktai, po formos ?atp=ok#uzsakymas) naršyklė kitaip po „load“ 1–2 s slinktų
        nuo viršaus per visą puslapį ir sustotų ne vietoje; su inkaru palaukiame, kol ji nušoks į vietą. ── */
  function sklandu(){ setTimeout(function(){ document.documentElement.classList.add('atp-sklandu'); }, location.hash ? 300 : 0); }
  if (document.readyState === 'complete') sklandu(); else addEventListener('load', sklandu);

  /* ── Atsiradimas slenkant: demo .rodyk ir konstruktoriaus stulpeliai / eilutės su klase „atp-rodyk“.
        IntersectionObserver kai kuriose aplinkose (įterptos naršyklės, emuliuojamas vaizdas) nepraneša,
        o turinys tada liktų paslėptas — todėl tikriname tame pačiame rAF cikle. ── */
  function rodymo_patikra(){
    for (var k = laukia_rodymo.length - 1; k >= 0; k--){
      var el = laukia_rodymo[k], vieta = el.getBoundingClientRect().top;
      if (vieta < innerHeight * 0.88){
        el.classList.remove('pries');
        el.classList.add('matomas');
        if (el.id === 'teiginys-linija') el.style.setProperty('--tp', 1);
        laukia_rodymo.splice(k, 1);
      } else if (vieta < innerHeight * 2.5 && !el.atpKrauta){
        /* Nuotraukos su loading="lazy" paslėptame (.pries, clip-path) bloke naršyklei „nematomos“ — ji jas pradėtų
           krauti tik atsiradimo metu, ir jos atsirastų tuščios. Pradedame krauti likus ~1,5 ekrano iki atsiradimo. */
        el.atpKrauta = true;
        el.querySelectorAll('img[loading="lazy"]').forEach(function(img){ img.loading = 'eager'; });
      }
    }
  }
  /* Pradinis paslėpimas — be perėjimo: atp.js vykdomas jau nupiešus puslapį, todėl kitaip elementai
     (ir paslaugų nuotraukos) pirma animuotai pasislėptų. Demo skriptas vykdomas prieš pirmą piešimą. */
  var be_perejimo = [];
  document.querySelectorAll('.rodyk, .atp-rodyk, #teiginys-linija').forEach(function(el){
    if (!rm.matches && el.getBoundingClientRect().top > innerHeight * 0.88){
      [el].concat([].slice.call(el.querySelectorAll('img'))).forEach(function(x){ x.style.transition = 'none'; be_perejimo.push(x); });
      el.classList.add('pries');
      if (el.id === 'teiginys-linija') el.style.setProperty('--tp', 0);
      laukia_rodymo.push(el);
    } else {
      el.classList.add('matomas');
    }
  });
  if (be_perejimo.length){
    void document.body.offsetWidth;   // pritaikome paslėptą būseną, tada grąžiname perėjimus
    be_perejimo.forEach(function(x){ x.style.transition = ''; });
  }

  /* ── Video: sumažintas judesys arba duomenų taupymas → tik poster;
        video su data-atp-paleisti (be autoplay) paleidžiamas tik priartėjus — taupomas srautas ── */
  function sustabdyk(v){ v.removeAttribute('autoplay'); v.autoplay = false; v.preload = 'none'; try { v.pause(); } catch (e) {} }
  function paleisk(v){ v.preload = 'auto'; var p = v.play(); if (p && p.catch) p.catch(function(){}); }
  function video_patikra(){
    for (var k = laukia_video.length - 1; k >= 0; k--){
      if (laukia_video[k].getBoundingClientRect().top < innerHeight * 1.5){ paleisk(laukia_video[k]); laukia_video.splice(k, 1); }
    }
  }
  document.querySelectorAll('#hero-video, .atp-hero video, .atp-video video').forEach(function(v){
    if (rm.matches || taupyti){ sustabdyk(v); return; }
    if (v.hasAttribute('data-atp-paleisti')) laukia_video.push(v);
  });
  if (rm.addEventListener) rm.addEventListener('change', function(){
    if (rm.matches) document.querySelectorAll('video').forEach(sustabdyk);
  });

  addEventListener('resize', function(){ rodymo_patikra(); video_patikra(); }, {passive: true});
  kadras();
  setTimeout(kadras, 400);

  /* ── DUK (Salient „Toggles“): pirmas klausimas atidarytas, kaip demo <details open>.
        Salient ne akordeono režimu visus laiko uždarytus; max-height nustatome kaip Salient paspaudus,
        tik suapvalinę į viršų (scrollHeight sveikas, todėl trupmeninis aukštis būtų apkirptas).
        Įkeliant — be 420 ms perėjimo: kitaip atsakymas „išsiskleistų“ jau naršyklei nuslinkus prie
        inkaro (/#uzsakymas, /#kontaktai) ir turinys žemiau DUK pasislinktų. ── */
  var dukPirmas = document.querySelector('.atp-duk .toggles:not(.accordion) > .toggle');
  if (dukPirmas){
    var dukSkydas = dukPirmas.querySelector(':scope > div'), dukMygtukas = dukPirmas.querySelector('.toggle-heading');
    var dukAukstis = function(){
      if (!dukSkydas || !dukPirmas.classList.contains('open')) return;
      var vidus = dukSkydas.firstElementChild;
      dukSkydas.style.maxHeight = Math.ceil(vidus ? vidus.getBoundingClientRect().height : dukSkydas.scrollHeight) + 'px';
    };
    if (dukSkydas) dukSkydas.style.transition = 'none';
    dukPirmas.classList.add('open');
    if (dukMygtukas) dukMygtukas.setAttribute('aria-expanded', 'true');
    dukAukstis();
    if (dukSkydas){ void dukSkydas.offsetHeight; dukSkydas.style.transition = ''; }   // pritaikome, tada grąžiname perėjimą
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(dukAukstis);
  }

  /* ── Failo zona: vilkimas ir pavadinimas (tik puslapyje su forma) ── */
  var zona = document.getElementById('failo-zona'), failas = document.getElementById('brezinys'), info = document.getElementById('failo-info');
  function rodykFaila(){ if (info) info.textContent = failas.files.length ? 'Pridėtas failas: ' + failas.files[0].name : 'PDF, DWG, DXF, XLSX, JPG, PNG · iki 20 MB'; }
  if (zona && failas){
    ['dragenter','dragover'].forEach(function(e){ zona.addEventListener(e, function(ev){ ev.preventDefault(); zona.classList.add('virs'); }); });
    ['dragleave','drop'].forEach(function(e){ zona.addEventListener(e, function(ev){ ev.preventDefault(); zona.classList.remove('virs'); }); });
    zona.addEventListener('drop', function(ev){ if (ev.dataTransfer && ev.dataTransfer.files.length){ failas.files = ev.dataTransfer.files; rodykFaila(); } });
    failas.addEventListener('change', rodykFaila);
  }

  /* ── Formos tikrinimas: klaidos blokuoja siuntimą, teisinga forma keliauja į WordPress ── */
  var forma = document.getElementById('uzklausa');
  if (forma) forma.addEventListener('submit', function(ev){
    var klaidu = 0, pirma = null;
    forma.querySelectorAll('[required]').forEach(function(laukas){
      var laukelis = laukas.closest('.laukas') || laukas.closest('.sutikimas');
      var gerai = laukas.type === 'checkbox' ? laukas.checked : laukas.value.trim().length > 0;
      if (gerai && laukas.type === 'email') gerai = /^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/.test(laukas.value.trim());
      if (laukelis && laukelis.classList) laukelis.classList.toggle('klaida', !gerai);
      laukas.setAttribute('aria-invalid', gerai ? 'false' : 'true');
      if (!gerai){ klaidu++; if (!pirma) pirma = laukas; }
    });
    if (klaidu){ ev.preventDefault(); if (pirma) pirma.focus(); }
  });
})();
