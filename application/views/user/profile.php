<style>
  .upload-card {
    border: 1px solid #e5e7eb; /* gray-200 */
    border-radius: .75rem;
    padding: .75rem;
    min-height: 260px; /* bikin kedua kartu terlihat seimbang */
    background: #fff;
  }
  .upload-profile {
    width: 160px; height: 160px; object-fit: cover;
  }
  .upload-cover {
    width: 100%; height: 200px; object-fit: cover; /* tetap wide tapi gak bikin tinggi kartu beda jauh */
  }
  /* Optional: kalo mau lebih rata tinggi, naikin height cover ke 220-240px */
  @media (max-width: 767.98px) {
    /* di layar kecil otomatis stack (col-md) */
    .upload-card { min-height: unset; }
  }
</style>

<style>
  .btn-actions{display:flex;gap:8px}
  .btn-action{
    width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;
    border:1px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;
    transition:.15s ease; box-shadow:0 1px 0 rgba(16,24,40,.02)
  }
  .btn-action:hover{background:#f9fafb;border-color:#dfe3e6}
  .btn-action:active{transform:translateY(1px)}
  .btn-action i{font-size:16px;line-height:1}
  .btn-action.edit{color:#0f766e;border-color:#ccfbf1;background:#ecfeff}   /* teal vibes */
  .btn-action.edit:hover{background:#e6fffb}
  .btn-action.danger{color:#b91c1c;border-color:#fee2e2;background:#fff}
  .btn-action.danger:hover{background:#fff5f5}
</style>

<style>
  .modal .form-label{font-weight:600}
  .modal .border{border-color:#e9ecef !important}
  .modal .form-text{margin-top:.25rem}
</style>




<?php
// ===== Helpers mini biar aman & enak dipakai

$nama = $cv['nama'] ?? $user['name'] ?? '';
$posisi  = $cv['posisi'] ?? '—';
$perush  = $cv['perusahaan'] ?? '';
$lokasi  = $user['domisili_kota']    ?? ($cv['domisili_kota']    ?? '');
$kewarg  = $user['domisili_negara']  ?? ($cv['domisili_negara']  ?? '');
$fotoUrl  = !empty($user['photo']) ? media_url($user['photo']) : '';
$coverUrl = !empty($user['cover']) ? media_url($user['cover'])
           : 'https://images.unsplash.com/photo-1503264116251-35a269479413?auto=format&fit=crop&w=1950&q=80';

$countExp = isset($counts['pengalaman']) ? (int)$counts['pengalaman'] : (is_array($pengalaman_kerja ?? null) ? count($pengalaman_kerja) : 0);
$countEdu = isset($counts['pendidikan']) ? (int)$counts['pendidikan'] : (is_array($pendidikan_formal ?? null) ? count($pendidikan_formal) : 0);
$countCert= isset($counts['sertifikasi']) ? (int)$counts['sertifikasi'] : (is_array($sertifikasi_profesi ?? null) ? count($sertifikasi_profesi) : 0);
?>
<style>
/* ===== LinkedIn-ish polish */
.profile-cover{height:200px; object-fit:cover; width:100%;}
.profile-avatar{
  width:104px;height:104px;border-radius:50%;border:3px solid #fff;
  box-shadow:0 8px 24px rgba(0,0,0,.08); background:#6c757d; color:#fff;
  display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:700;
}
.card-lite{background:#fff;border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,.04); border:1px solid #eef0f2;}
.item-sep{border:0;border-top:1px dashed #e5e7eb;margin:.75rem 0;}
.muted{color:#6c757d}
.chip{display:inline-block;padding:.25rem .58rem;border-radius:999px;background:#f1f3f5;font-size:.8rem;color:#495057}
.stat{min-width:110px}
.icon-sm{font-size:18px;vertical-align:-2px}
.link-muted{color:#6c757d;text-decoration:none}
.link-muted:hover{color:#0d6efd}
.kbd{background:#f8f9fa;border:1px solid #e9ecef;border-bottom-width:2px;border-radius:6px;padding:.1rem .35rem;font-size:.8rem}
@media (max-width: 767.98px){
  .profile-avatar{width:84px;height:84px;font-size:26px}
}

/* Card heading */
.section-title{font-weight:700;color:#111827}
.subhead{font-weight:600;color:#111827}

/* Chips */
.contact-chip{
  display:inline-flex;align-items:center;gap:.35rem;
  background:#f8fafc;border:1px solid #e5e7eb;border-radius:999px;
  padding:.25rem .6rem;font-size:.925rem;line-height:1.2;color:#374151;
  text-decoration:none; transition:.15s ease;
}
.contact-chip:hover{background:#f1f5f9;border-color:#dfe3e6;color:#111827}
.contact-chip.chip-muted{background:#fff;border-style:dashed;color:#6b7280}
.contact-chip svg{vertical-align:-2px}

/* (opsional) kecilin jarak di mobile */
@media (max-width: 575.98px){
  .contact-chip{font-size:.9rem}
}

</style>




<div class="container py-3">
  <div class="row">
    <!-- Sidebar kiri (punya lo) -->
    <div class="col-md-3 d-none d-md-block">
      <?php $this->load->view('templates/sidebar'); ?>
    </div>

    <!-- Main -->
    <div class="col-md-9">
      <!-- Header Profile -->
      <div class="card card-lite mb-3 overflow-hidden">
        <div class="position-relative">
          <img src="<?= h($coverUrl) ?>" alt="cover" class="profile-cover">
          <div class="position-absolute top-100 start-0 translate-middle-y ms-4">
            <?php if ($fotoUrl): ?>
              <img src="<?= h($fotoUrl) ?>" class="profile-avatar" alt="avatar">
            <?php else: ?>
              <div class="profile-avatar"><?= h(initials($nama)) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body pt-5">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="mb-3 mb-md-0">
              <h4 class="mb-1"><?= h($nama ?: '—') ?> <?php if(!empty($cv['verified'])): ?><i class="bi bi-patch-check-fill text-primary"></i><?php endif; ?></h4>
              <div class="muted">
                <?= h($posisi) ?>
                <?php if ($perush): ?> · <span class="chip"><?= h($perush) ?></span><?php endif; ?>
              </div>
              <div class="small muted mt-1">
                <?php if ($lokasi): ?><i class="bi bi-geo-alt icon-sm"></i> <?= h($lokasi) ?><?php endif; ?>
                <?php if ($kewarg): ?> · <i class="bi bi-flag icon-sm"></i> <?= h($kewarg) ?><?php endif; ?>
                  
              </div>
             
              
            </div>
            <div class="d-flex gap-2">
              <a href=""  data-bs-toggle="modal" data-bs-target="#modalEditProfile" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square me-1"></i>Edit Profil</a>
              <a href="<?= base_url('user/export_pdf_wb') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Format Internasional </a>
              <a href="<?= base_url('user/export_pdf_apbn') ?>" class="btn btn-outline-secondary btn-sm d-none d-lg-inline-flex"><i class="bi bi-file-earmark-pdf me-1"></i>Format Nasional</a>
            </div>
          </div>

          <!-- Stats -->
          <div class="d-flex gap-4 mt-3">
            <div class="text-center stat">
              <div class="fw-semibold"><?= $countExp ?></div>
              <div class="muted small">Pengalaman</div>
            </div>
            <div class="text-center stat">
              <div class="fw-semibold"><?= $countEdu ?></div>
              <div class="muted small">Pendidikan</div>
            </div>
            <div class="text-center stat">
              <div class="fw-semibold"><?= $countCert ?></div>
              <div class="muted small">Sertifikasi</div>
            </div>
            
          </div>
          

        </div>
      </div>

      <?php
// ===== helpers mini =====
if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
/** Chip link: bisa pakai Bootstrap Icon (bi-*) atau custom SVG */
function chip_link($url, $label, $bi = null, $svg = null, $mailto = false) {
  if (empty($url)) return '';
  $u = $mailto ? 'mailto:'.h($url) : h($url);
  $lbl = h($label);
  $icon = $svg ?: ($bi ? '<i class="bi '.$bi.' me-1"></i>' : '');
  return '<a class="contact-chip" target="'.($mailto ? '_self' : '_blank').'" rel="noopener" href="'.$u.'">'.$icon.$lbl.'</a>';
}

// ===== SVG custom (biar gak tergantung versi BI) =====
$SVG_X = '<svg width="16" height="16" viewBox="0 0 24 24" class="me-1" aria-hidden="true"><path fill="currentColor" d="M18.244 2H21l-6.47 7.39L22 22h-6.8l-4.64-5.64L4.2 22H1.44l6.9-7.89L2 2h6.86l4.2 5.1L18.24 2Zm-2.38 18h2.06L8.22 4h-2L15.86 20Z"/></svg>';
$SVG_TIKTOK = '<svg width="16" height="16" viewBox="0 0 256 256" class="me-1" aria-hidden="true"><path fill="currentColor" d="M227.9 92.8c-24.9-6.5-45.8-23.2-57.3-45.4V160a68 68 0 1 1-68-68a70.7 70.7 0 0 1 12 .9V121a40 40 0 1 0 28 38V24h28.7a76.7 76.7 0 0 0 56.6 48.2z"/></svg>';

// ===== email list: ambil dari emails_all (CSV) atau fallback ke single email
$emails_all = [];
$rawEmails = $user['emails_all'] ?? $user['email'] ?? '';
if (!empty($rawEmails)) {
  $emails_all = array_values(array_unique(array_filter(array_map('trim', explode(',', $rawEmails)))));
}

// ===== links utama
$linkedin = $user['linkedin'] ?? '';
$whatsapp = $user['whatsapp'] ?? '';

// ===== sosmed lainnya
$instagram = $user['instagram'] ?? '';
$twitter   = $user['twitter']   ?? '';  // X
$github    = $user['github']    ?? '';
$facebook  = $user['facebook']  ?? '';
$youtube   = $user['youtube']   ?? '';
$tiktok    = $user['tiktok']    ?? '';
$website   = $user['website']   ?? '';
$telegram  = $user['telegram']  ?? '';
$medium    = $user['medium']    ?? '';
$behance   = $user['behance']   ?? '';
$dribbble  = $user['dribbble']  ?? '';
?>

<!-- ===== CARD: Kontak & Media Sosial ===== -->
<div class="card card-lite mb-3">
  <div class="card-body">
    <h6 class="section-title mb-3"><i class="bi bi-person-rolodex me-2"></i>Kontak & Media Sosial</h6>

    <!-- Kontak Utama -->
    <div class="mb-2 d-flex align-items-center gap-2">
      <div class="subhead"><i class="bi bi-person-lines-fill me-2"></i>Kontak Utama</div>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-3">
      <?php if (!empty($emails_all)): ?>
        <?php foreach ($emails_all as $em): ?>
          <?= chip_link($em, $em, 'bi-envelope', null, true) ?>
        <?php endforeach; ?>
      <?php endif; ?>

      <?= !empty($linkedin) ? chip_link($linkedin, 'LinkedIn', 'bi-linkedin') : '' ?>
      <?= !empty($whatsapp) ? chip_link($whatsapp, 'WhatsApp', 'bi-whatsapp') : '' ?>
    </div>

    <!-- Media Sosial Lainnya -->
    <div class="mb-2 d-flex align-items-center gap-2">
      <div class="subhead"><i class="bi bi-share me-2"></i>Media Sosial Lainnya</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <?= !empty($instagram) ? chip_link((preg_match('~^https?://~', $instagram)?$instagram:'https://instagram.com/'.ltrim($instagram,'@')), '@'.ltrim(basename($instagram),'@'), 'bi-instagram') : '' ?>

      <?= !empty($twitter)   ? chip_link((preg_match('~^https?://~',$twitter)?$twitter:'https://twitter.com/'.ltrim($twitter,'@')), 'X', null, $SVG_X) : '' ?>

      <?= !empty($github)    ? chip_link((preg_match('~^https?://~',$github)?$github:'https://github.com/'.ltrim($github,'/')), 'GitHub', 'bi-github') : '' ?>

      <?= !empty($facebook)  ? chip_link((preg_match('~^https?://~',$facebook)?$facebook:'https://facebook.com/'.ltrim($facebook,'/')), 'Facebook', 'bi-facebook') : '' ?>

      <?= !empty($youtube)   ? chip_link($youtube, 'YouTube', 'bi-youtube') : '' ?>

      <?= !empty($tiktok)    ? chip_link((preg_match('~^https?://~',$tiktok)?$tiktok:'https://www.tiktok.com/@'.ltrim($tiktok,'@')), 'TikTok', null, $SVG_TIKTOK) : '' ?>

      <?= !empty($website)   ? chip_link($website, 'Website', 'bi-globe') : '' ?>

      <?= !empty($telegram)  ? chip_link((preg_match('~^https?://~',$telegram)?$telegram:'https://t.me/'.ltrim($telegram,'@')), 'Telegram', 'bi-telegram') : '' ?>

      <?= !empty($medium)    ? chip_link((preg_match('~^https?://~',$medium)?$medium:'https://medium.com/@'.ltrim($medium,'@')), 'Medium', 'bi-journal-text') : '' ?>

      <?= !empty($behance)   ? chip_link((preg_match('~^https?://~',$behance)?$behance:'https://www.behance.net/'.ltrim($behance,'/')), 'Behance', 'bi-palette') : '' ?>

      <?= !empty($dribbble)  ? chip_link((preg_match('~^https?://~',$dribbble)?$dribbble:'https://dribbble.com/'.ltrim($dribbble,'/')), 'Dribbble', 'bi-palette2') : '' ?>
    </div>
  </div>
</div>


      

<!-- ============================== -->
<!-- BAGIAN PEKERJAAN SAAT INI  -->
<!-- ============================== -->
<?php
// 1. Tentukan key (harus sama dengan DB)
$key_employment = 'cv';
// 2. Ambil status
$is_employment_visible = (int)($visibility_map[$key_employment] ?? 1);
?>
<div class="card card-lite mb-3">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div><i class="bi bi-person-workspace me-2"></i>Riwayat Pekerjaan Saat Ini</div>
    <div class="d-flex align-items-center">
      <button class="btn btn-sm btn-outline-secondary toggle-section me-2" 
              data-target="#employment-content"
              data-storage-key="<?= $key_employment ?>"
              data-visible-state="<?= $is_employment_visible ?>">
        <?php if ($is_employment_visible): ?>
          <i class="bi bi-eye-slash me-1"></i> Sembunyikan
        <?php else: ?>
          <i class="bi bi-eye me-1"></i> Tampilkan
        <?php endif; ?>
      </button>
      <a href="#" class="link-muted small" data-bs-toggle="modal" data-bs-target="#modalEmployment">
        <i class="bi bi-pencil me-1"></i>Edit
      </a>
    </div>
  </div>
  <div id="employment-content" class="card-body <?= !$is_employment_visible ? 'd-none' : '' ?>">
    <div class="row g-2 small">
      <div class="col-md-6">
        <div><span class="muted">Perusahaan:</span> <?= h($cv['employer'] ?? '—') ?></div>
        <div><span class="muted">Jabatan:</span> <?= h($cv['employment_position'] ?? '—') ?></div>
      </div>
      <div class="col-md-6 text-md-end">
        <div><span class="muted">Periode:</span>
          <?= dmy($cv['employment_from'] ?? null) ?> – 
          <?= !empty($cv['employment_to']) ? dmy($cv['employment_to']) : 'Sekarang' ?>
        </div>
      </div>
    </div>
    <?php
      $durasiNow = '';
      if (!empty($cv['employment_from']) && empty($cv['employment_to'])) {
        try {
          $d1 = new DateTime($cv['employment_from']); 
          $d2 = new DateTime('today');
          $diff = $d1->diff($d2);
          $parts = [];
          if ($diff->y > 0) $parts[] = $diff->y.' th';
          if ($diff->m > 0) $parts[] = $diff->m.' bln';
          if (empty($parts)) $parts[] = $diff->d.' hr';
          $durasiNow = implode(' ', $parts).' (ongoing)';
        } catch (Exception $e) {}
      }
    ?>
    <?php if ($durasiNow): ?>
      <div class="mt-1 small"><span class="muted">Durasi:</span> <?= h($durasiNow) ?></div>
    <?php endif; ?>
    <?php if (!empty($cv['employment_desc'])): ?>
      <div class="mt-2 small"><?= nl2br(h($cv['employment_desc'])) ?></div>
    <?php endif; ?>
  </div>
</div>


<!-- ============================== -->
<!-- BAGIAN PENGALAMAN -->
<!-- ============================== -->
<?php
$key_pengalaman = 'pengalaman_kerja';
$is_pengalaman_visible = (int)($visibility_map[$key_pengalaman] ?? 1);
?>
<div class="card card-lite mb-3">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div><i class="bi bi-briefcase me-2"></i>Pengalaman</div>
    <div class="d-flex align-items-center">
      <button class="btn btn-sm btn-outline-secondary toggle-section me-2" 
              data-target="#pengalaman-content"
              data-storage-key="<?= $key_pengalaman ?>"
              data-visible-state="<?= $is_pengalaman_visible ?>"> 
        <?php if ($is_pengalaman_visible): ?>
          <i class="bi bi-eye-slash me-1"></i> Sembunyikan
        <?php else: ?>
          <i class="bi bi-eye me-1"></i> Tampilkan
        <?php endif; ?>
      </button>
      <a href="#" class="link-muted small" data-bs-toggle="modal" data-bs-target="#modalPengalaman" data-mode="add">
        <i class="bi bi-plus-circle me-1"></i>Tambah
      </a>
    </div>
  </div>
  <div id="pengalaman-content" class="profile-section card-body <?= !$is_pengalaman_visible ? 'd-none' : '' ?>">
    <?php if (!empty($pengalaman_kerja)): ?>
      <?php foreach ($pengalaman_kerja as $i => $p): ?>
        <div class="d-flex justify-content-between align-items-start">
          <div class="pe-3"> <div class="fw-semibold"><?= h($p['nama_kegiatan'] ?? '—') ?></div>
            <div class="small muted">
              <?= h($p['pelaksana_proyek'] ?: ($p['perusahaan'] ?? '')) ?>
              <?php if (!empty($p['lokasi'])): ?> · <?= h($p['lokasi']) ?><?php endif; ?>
              <?php if (!empty($p['negara'])): ?>, <?= h($p['negara']) ?><?php endif; ?>
            </div>
            <?php if (!empty($p['uraian_proyek'])): ?>
              <div class="small mt-1"><?= nl2br(h($p['uraian_proyek'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($p['uraian_tugas'])): ?>
              <div class="small mt-1"><span class="muted">Tugas:</span> <?= nl2br(h($p['uraian_tugas'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($p['referensi_file'])): ?>
              <div class="mt-1">
                <a href="<?= base_url('' . h($p['referensi_file'])) ?>" target="_blank" class="link-primary">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Lihat Referensi
                </a>
              </div>
            <?php endif; ?>
          </div>
          <div class="text-end small muted" style="min-width:210px">
            <?= dmy($p['waktu_mulai'] ?? null) ?> – <?= !empty($p['waktu_akhir']) ? dmy($p['waktu_akhir']) : 'Skrg' ?><br>
            <?= h($p['durasi'] ?? '') ?>
            <div class="btn-actions mt-2 d-flex justify-content-end gap-2">
              <button type="button" class="btn-action edit" data-bs-toggle="modal" data-bs-target="#modalPengalaman"
                      data-mode="edit" data-id="<?= (int)$p['id'] ?>"
                      data-nama_kegiatan="<?= h($p['nama_kegiatan'] ?? '') ?>"
                      data-posisi="<?= h($p['posisi'] ?? '') ?>"
                      data-pemberi_pekerjaan="<?= h($p['pemberi_pekerjaan'] ?? '') ?>"
                      data-pelaksana_proyek="<?= h($p['pelaksana_proyek'] ?? '') ?>"
                      data-lokasi="<?= h($p['lokasi'] ?? '') ?>"
                      data-negara="<?= h($p['negara'] ?? '') ?>"
                      data-waktu_mulai="<?= h($p['waktu_mulai'] ?? '') ?>"
                      data-waktu_akhir="<?= h($p['waktu_akhir'] ?? '') ?>"
                      data-uraian_proyek="<?= h($p['uraian_proyek'] ?? '') ?>"
                      data-uraian_tugas="<?= h($p['uraian_tugas'] ?? '') ?>"
                      data-referensi_file="<?= h($p['referensi_file'] ?? '') ?>"
                      data-is_visible="<?= (int)($p['is_visible'] ?? 1) ?>">
                <i class="bi bi-pencil"></i>
              </button>
              <form class="m-0" method="post" action="<?= base_url('user/experience_delete/'.(int)$p['id']) ?>"
                    onsubmit="return confirm('Hapus pengalaman ini?');">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <button type="submit" class="btn-action danger" title="Hapus">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
        <?php if ($i < count($pengalaman_kerja)-1): ?><hr class="item-sep"><?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="muted">Belum ada data pengalaman.</div>
    <?php endif; ?>
  </div>
</div>


<!-- ============================== -->
<!-- BAGIAN PENDIDIKAN -->
<!-- ============================== -->
<?php
$key_pendidikan = 'pendidikan_formal';
$is_pendidikan_visible = (int)($visibility_map[$key_pendidikan] ?? 1);
?>
<div class="card card-lite mb-3">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div><i class="bi bi-mortarboard me-2"></i>Pendidikan</div>
    <div class="d-flex align-items-center">
      <button class="btn btn-sm btn-outline-secondary toggle-section me-2" 
              data-target="#pendidikan-content"
              data-storage-key="<?= $key_pendidikan ?>"
              data-visible-state="<?= $is_pendidikan_visible ?>">
        <?php if ($is_pendidikan_visible): ?>
          <i class="bi bi-eye-slash me-1"></i> Sembunyikan
        <?php else: ?>
          <i class="bi bi-eye me-1"></i> Tampilkan
        <?php endif; ?>
      </button>
      <a href="#" class="link-muted small" data-bs-toggle="modal" data-bs-target="#modalPendidikan" data-mode="add">
        <i class="bi bi-plus-circle me-1"></i>Tambah
      </a>
    </div>
  </div>
  <div id="pendidikan-content" class="profile-section card-body <?= !$is_pendidikan_visible ? 'd-none' : '' ?>">
    <?php if (!empty($pendidikan_formal)): ?>
      <?php foreach ($pendidikan_formal as $i => $r): ?>
        <div class="d-flex justify-content-between align-items-start">
          <div class="pe-3"> <div class="fw-semibold"><?= h(($r['tingkat'] ?? '').' '.($r['jurusan'] ?? '')) ?></div>
            <div class="small muted"><?= h($r['institusi'] ?? '—') ?> · Lulus: <?= h($r['tahun_lulus'] ?? '—') ?></div>
            <?php if (!empty($r['ijazah_file'])): ?>
              <div class="mt-1">
                <a href="<?= base_url('' . h($r['ijazah_file'])) ?>" target="_blank" class="link-primary">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Lihat Ijazah
                </a>
              </div>
            <?php endif; ?>
          </div>
          <div class="text-end" style="min-width:120px">
            <div class="btn-actions d-flex justify-content-end gap-2">
              <button type="button" class="btn-action edit" data-bs-toggle="modal" data-bs-target="#modalPendidikan"
                      data-mode="edit" data-id="<?= (int)$r['id'] ?>"
                      data-institusi="<?= h($r['institusi'] ?? '') ?>"
                      data-tingkat="<?= h($r['tingkat'] ?? '') ?>"
                      data-jurusan="<?= h($r['jurusan'] ?? '') ?>"
                      data-tahun_lulus="<?= h($r['tahun_lulus'] ?? '') ?>"
                      data-ijazah_file="<?= h($r['ijazah_file'] ?? '') ?>"
                      data-is_visible="<?= (int)($r['is_visible'] ?? 1) ?>">
                <i class="bi bi-pencil"></i>
              </button>
              <form class="m-0" method="post" action="<?= base_url('user/education_delete/'.(int)$r['id']) ?>"
                    onsubmit="return confirm('Hapus pendidikan ini?');">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <button type="submit" class="btn-action danger" title="Hapus">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
        <?php if ($i < count($pendidikan_formal)-1): ?><hr class="item-sep"><?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="muted">Belum ada data pendidikan.</div>
    <?php endif; ?>
  </div>
</div>



<!-- ============================== -->
<!-- BAGIAN SERTIFIKASI -->
<!-- ============================== -->
<?php
// Key ini HARUS SAMA PERSIS dengan isi kolom 'section' di DB Anda
$key_sertifikasi = 'sertifikasi_profesi'; // <-- INI YANG BENAR
// Ini akan mencari di $visibility_map['sertifikasi_profesi']
$is_sertifikasi_visible = (int)($visibility_map[$key_sertifikasi] ?? 1);
?>

<div class="card card-lite mb-3">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div><i class="bi bi-patch-check me-2"></i>Sertifikasi</div>
    <div class="d-flex align-items-center">
      
      <button class="btn btn-sm btn-outline-secondary toggle-section me-2" 
              data-target="#sertifikasi-content"
              data-storage-key="sertifikasi_profesi"
              data-visible-state="<?= $is_sertifikasi_visible ?>">
        
        <?php if ($is_sertifikasi_visible): ?>
          <i class="bi bi-eye-slash me-1"></i> Sembunyikan
        <?php else: ?>
          <i class="bi bi-eye me-1"></i> Tampilkan
        <?php endif; ?>

      </button>
      
      <a href="#" class="link-muted small" data-bs-toggle="modal" data-bs-target="#modalSertifikasi" data-mode="add">
        <i class="bi bi-plus-circle me-1"></i>Tambah
      </a>
    </div>
  </div>

  <div id="sertifikasi-content" class="profile-section card-body <?= !$is_sertifikasi_visible ? 'd-none' : '' ?>">
    <?php if (!empty($sertifikasi_profesi)): ?>
        <?php foreach ($sertifikasi_profesi as $i => $s): ?>
            
            <div class="d-flex justify-content-between align-items-start">
                
                <div class="pe-3">
                    <div class="fw-semibold"><?= h($s['nama'] ?? '—') ?></div>
                    <div class="small muted"><?= h($s['penerbit'] ?? '—') ?> · <?= h($s['tahun'] ?? '—') ?></div>
                    
                    <?php if (!empty($s['file_sertifikat'])): ?>
                        <div class="small mt-1">
                            <a href="<?= base_url('' . h($s['file_sertifikat'])) ?>" target="_blank" class="link-primary">
                                <i class="bi bi-file-earmark-arrow-down me-1"></i>Lihat Sertifikat
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="text-end" style="min-width:120px">
                    <div class="btn-actions d-flex justify-content-end gap-2">
                        
                        <button type="button" class="btn-action edit" data-bs-toggle="modal" data-bs-target="#modalSertifikasi"
                                data-mode="edit"
                                data-id="<?= (int)$s['id'] ?>"
                                data-nama="<?= h($s['nama'] ?? '') ?>"
                                data-penerbit="<?= h($s['penerbit'] ?? '') ?>"
                                data-tahun="<?= h($s['tahun'] ?? '') ?>"
                                data-is_visible="<?= (int)($s['is_visible'] ?? 1) ?>"
                                data-file_sertifikat="<?= h($s['file_sertifikat'] ?? '') ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        <form class="m-0" method="post" action="<?= base_url('user/cert_delete/'.(int)$s['id']) ?>"
                              onsubmit="return confirm('Hapus sertifikasi ini?');">
                            <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                            <button type="submit" class="btn-action danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if ($i < count($sertifikasi_profesi)-1): ?><hr class="item-sep"><?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="muted">Belum ada data sertifikasi.</div>
    <?php endif; ?>
  </div>
</div> 



<!-- ============================== -->
<!-- BAGIAN PELATIHAN -->
<!-- ============================== -->
<?php
// Gunakan key yang konsisten, misal 'pendidikan_nonformal'
$key_pelatihan = 'pendidikan_nonformal'; 
$is_pelatihan_visible = (int)($visibility_map[$key_pelatihan] ?? 1);
?>

<!-- Tambahkan id="pelatihan" di sini untuk anchor redirect -->
<div class="card card-lite mb-3" id="pelatihan">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div><i class="bi bi-person-workspace me-2"></i>Pelatihan & Kursus</div>
    <div class="d-flex align-items-center">
      
      <!-- Tombol Show/Hide Section -->
      <button class="btn btn-sm btn-outline-secondary toggle-section me-2" 
              data-target="#pelatihan-content"
              data-storage-key="<?= $key_pelatihan ?>"
              data-visible-state="<?= $is_pelatihan_visible ?>">
        
        <?php if ($is_pelatihan_visible): ?>
          <i class="bi bi-eye-slash me-1"></i> Sembunyikan
        <?php else: ?>
          <i class="bi bi-eye me-1"></i> Tampilkan
        <?php endif; ?>
      </button>
      
      <!-- Tombol Tambah -->
      <a href="#" class="link-muted small" data-bs-toggle="modal" data-bs-target="#modalPelatihan" data-mode="add">
        <i class="bi bi-plus-circle me-1"></i>Tambah
      </a>
    </div>
  </div>
  <div id="pelatihan-content" class="profile-section card-body <?= !$is_pelatihan_visible ? 'd-none' : '' ?>">
    <!-- Loop menggunakan $pendidikan_nonformal -->
    <?php if (!empty($pendidikan_nonformal)): ?>
      <?php foreach ($pendidikan_nonformal as $i => $p): ?>
        <div class="d-flex justify-content-between align-items-start">
          <div class="pe-3">
            
            <!-- Menampilkan 'nama_pelatihan' -->
            <div class="fw-semibold"><?= h($p['nama_pelatihan'] ?? '—') ?></div>
            <div class="small muted"><?= h($p['penyelenggara'] ?? '—') ?> · <?= h($p['tahun'] ?? '—') ?></div>
            
            <!-- Link ke Sertifikat (menggunakan 'sertifikat_file') -->
            <?php if (!empty($p['sertifikat_file'])): ?>
                <div class="small mt-1">
                    <a href="<?= base_url('' . h($p['sertifikat_file'])) ?>" target="_blank" class="link-primary">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Lihat Sertifikat
                    </a>
                </div>
            <?php endif; ?>
          </div>
          
          <div class="text-end" style="min-width:120px">
            <div class="btn-actions d-flex justify-content-end gap-2">
              
              <!-- 
                Tombol Edit (BERSIH)
                Komentar yang merusak sudah dihapus dari sini.
              -->
             <button type="button" class="btn-action edit" data-bs-toggle="modal" data-bs-target="#modalPelatihan"
                data-mode="edit"
                
                data-id="<?= h($p['id']) ?>"
                data-nama_pelatihan="<?= h($p['nama_pelatihan']) ?>"
                data-penyelenggara="<?= h($p['penyelenggara']) ?>"
                data-tahun="<?= h($p['tahun']) ?>"
                data-sertifikat_file="<?= h($p['sertifikat_file']) ?>"
                data-is_visible="<?= h($p['is_visible']) ?>">
                
                <i class="bi bi-pencil"></i>
            </button>
              
              <!-- Tombol Hapus (Action ke 'nonformal_delete') -->
              <form class="m-0" method="post" action="<?= base_url('user/nonformal_delete/'.(int)$p['id']) ?>"
                    onsubmit="return confirm('Hapus pelatihan ini?');">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <button type="submit" class="btn-action danger" title="Hapus">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
        <?php if ($i < count($pendidikan_nonformal)-1): ?><hr class="item-sep"><?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="muted">Belum ada data pelatihan atau kursus.</div>
    <?php endif; ?>
  </div>
</div>

<!-- ============================== -->
<!-- BAGIAN KEAHLIAN & BAHASA -->
<!-- ============================== -->
<?php
$key_bahasa = 'bahasa';
$is_bahasa_visible = (int)($visibility_map[$key_bahasa] ?? 1);
?>
<div class="card card-lite mb-3">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div><i class="bi bi-translate me-2"></i>Keahlian & Bahasa</div>
    
    <div class="d-flex align-items-center">
      <button class="btn btn-sm btn-outline-secondary toggle-section me-2" 
              data-target="#bahasa-content"
              data-storage-key="bahasa"> <i class="bi bi-eye-slash me-1"></i> Sembunyikan
      </button>
      <a href="#" class="link-muted small" data-bs-toggle="modal" data-bs-target="#modalBahasa" data-mode="add">
        <i class="bi bi-plus-circle me-1"></i>Tambah
      </a>
    </div>
  </div>

  <div id="bahasa-content" class="profile-section card-body">
  <?php if (!empty($bahasa_list)): ?>
    <div class="list-group list-group-flush">
      <?php foreach ($bahasa_list as $b): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div class="item-content"> 
            <div class="fw-semibold"><?= h($b['bahasa']) ?></div>
            <div class="small text-muted">S <?= h($b['speaking']) ?> / R <?= h($b['reading']) ?> / W <?= h($b['writing']) ?></div>
          </div>
          
          <div class="btn-actions">
            <button type="button" class="btn-action edit" data-bs-toggle="modal" data-bs-target="#modalBahasa"
                    data-mode="edit" data-id="<?= (int)$b['id'] ?>"
                    data-bahasa="<?= h($b['bahasa'] ?? '') ?>"
                    data-speaking="<?= h($b['speaking'] ?? '') ?>"
                    data-reading="<?= h($b['reading'] ?? '') ?>"
                    data-writing="<?= h($b['writing'] ?? '') ?>"
                    data-is_visible="<?= (int)($b['is_visible'] ?? 1) ?>"> <i class="bi bi-pencil"></i>
            </button>
            <form class="m-0" method="post" action="<?= base_url('user/lang_delete/'.(int)$b['id']) ?>"
                onsubmit="return confirm('Hapus bahasa ini?');">
            <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
            <button type="submit" class="btn-action danger" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="muted">Belum ada data bahasa.</div>
  <?php endif; ?>
  </div>
</div>


<!-- ============================== -->
<!-- BAGIAN LAMPIRAN CV (BARU) -->
<!-- ============================== -->
<?php
$key_lampiran = 'lampiran_cv';
$is_lampiran_visible = (int)($visibility_map[$key_lampiran] ?? 1); 

$lampiran_items = [
    'ktp_file'      => 'KTP/Passport/ID',
    'npwp_file'     => 'NPWP',
    'bukti_pajak'   => 'Bukti Pajak',
    'foto'          => 'Foto Formal',
    'lainnya'       => 'Lampiran Lainnya'
];
// Di profile.php (pemilik), $lampiran diambil langsung dari controller
$lampiran_data = $lampiran ?? []; 
?>

<div class="card card-lite mb-3" id="lampiran">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div><i class="bi bi-paperclip me-2"></i>Lampiran CV</div>
        <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-outline-secondary toggle-section me-2" 
                    data-target="#lampiran-content"
                    data-storage-key="<?= $key_lampiran ?>"
                    data-visible-state="<?= $is_lampiran_visible ?>">
                <?php if ($is_lampiran_visible): ?>
                    <i class="bi bi-eye-slash me-1"></i> Sembunyikan
                <?php else: ?>
                    <i class="bi bi-eye me-1"></i> Tampilkan
                <?php endif; ?>
            </button>
        </div>
    </div>
    
    <div id="lampiran-content" class="profile-section card-body <?= (!$is_lampiran_visible) ? 'd-none' : '' ?>"> 
        <div class="list-group list-group-flush">
            <?php foreach ($lampiran_items as $field_name => $label): ?>
                <?php
                  $file_value = $lampiran_data[$field_name] ?? null;
                  $file_exists = !empty($file_value);
                  $file_url = $file_exists ? base_url('' . $file_value) : '#'; 

                  $visible_key = 'is_visible_' . $field_name;
                  $is_visible = (int)($lampiran_data[$visible_key] ?? 1); 
                ?>

                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div class="item-content">
                        <div class="fw-semibold"><?= h($label) ?></div>
                        <?php if ($file_exists): ?>
                            <div class="small text-muted">
                                <a href="<?= h($file_url) ?>" target="_blank" class="link-primary" style="word-break: break-all;">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Lihat File
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="small text-muted">Belum di-upload</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="btn-actions">
                        <button type="button" class="btn-action edit" data-bs-toggle="modal" data-bs-target="#modalLampiranItem"
                            title="<?= $file_exists ? 'Ganti' : 'Upload' ?>"
                            data-field_name="<?= h($field_name) ?>"
                            data-label="<?= h($label) ?>"
                            data-file_value="<?= h($file_value ?? '') ?>"
                            data-is_visible="<?= $is_visible ?>">
                            <i class="bi bi-upload"></i>
                        </button>

                        <?php if ($file_exists): ?>
                            <form class="m-0" method="post" action="<?= base_url('user/lampiran_item_delete/' . h($field_name)) ?>"
                                onsubmit="return confirm('Hapus file <?= h($label) ?> ini?');">
                                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                                <button type="submit" class="btn-action danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>




<div class="modal fade" id="modalEditProfile" tabindex="-1" aria-labelledby="modalEditProfileLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="post" action="<?= base_url('user/update_profile') ?>" enctype="multipart/form-data">
      <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditProfileLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 align-items-start mb-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Foto Profil</label>
            <div class="upload-card h-100 text-center">
              <img id="previewProfile"
                src="<?= !empty($user['photo']) ? media_url($user['photo']) : 'https://placehold.co/160x160?text=Profile' ?>"
                data-current-src="<?= !empty($user['photo']) ? media_url($user['photo']) : 'https://placehold.co/160x160?text=Profile' ?>"
                class="rounded-circle mb-2 upload-profile"
                alt="Preview Foto Profil">
              <input class="form-control" type="file" name="photo_profile" accept="image/*" onchange="previewImg(this,'previewProfile')">
              <div class="form-text">Rekomendasi: <b>400×400 px</b> (1:1), JPG/PNG, maks 2MB.</div>
              <input type="hidden" name="photo_profile_old" value="<?= $user['photo'] ?? '' ?>">
            </div>
          </div>
          <div class="col-md-8">
            <label class="form-label fw-semibold">Foto Sampul</label>
            <div class="upload-card h-100">
              <img id="previewCover"
                src="<?= !empty($user['cover']) ? media_url($user['cover']) : 'https://placehold.co/1200x300?text=Cover' ?>"
                data-current-src="<?= !empty($user['cover']) ? media_url($user['cover']) : 'https://placehold.co/1200x300?text=Cover' ?>"
                class="upload-cover mb-2 rounded-3"
                alt="Preview Foto Sampul">
              <input class="form-control" type="file" name="photo_cover" accept="image/*" onchange="previewImg(this,'previewCover')">
              <div class="form-text">Rekomendasi: <b>1600×400 px</b> (4:1), JPG/PNG, maks 3MB.</div>
              <input type="hidden" name="photo_cover_old" value="<?= $user['cover'] ?? '' ?>">
            </div>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Domisili - Negara/Wilayah</label>
            <input type="text" class="form-control" name="domisili_negara" placeholder="mis. Indonesia" value="<?= set_value('domisili_negara', $cv['domisili_negara'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Domisili - Kota</label>
            <input type="text" class="form-control" name="domisili_kota" placeholder="mis. Bandung" value="<?= set_value('domisili_kota', $cv['domisili_kota'] ?? '') ?>">
          </div>
        </div>

        <hr class="my-3">

        <!-- ===== Kontak Utama -->
<div class="border rounded-3 p-3 mb-3">
  <div class="d-flex align-items-center mb-2">
    <i class="bi bi-person-lines-fill me-2 text-muted"></i>
    <span class="fw-semibold">Kontak Utama</span>
  </div>

  <div class="row g-3">
    <!-- Email (Tagify) -->
    <div class="col-md-6">
      <label class="form-label">Email</label>
      <input type="text" class="form-control"
             id="email_tags"
             data-role="email-tags"
             data-hidden-target="email_hidden"
             placeholder="email1@contoh.com, email2@contoh.com">
      <input type="hidden" name="email" id="email_hidden"
             value="<?= set_value('email', $user['email'] ?? '') ?>">
      <div class="form-text">Bisa banyak, pisahkan pakai koma.</div>
    </div>

    <!-- LinkedIn -->
    <div class="col-md-6">
      <label class="form-label">LinkedIn</label>
      <div class="input-group">
        <span class="input-group-text">https://</span>
        <input type="url" class="form-control" name="linkedin"
               placeholder="www.linkedin.com/in/username"
               value="<?= set_value('linkedin', $user['linkedin'] ?? '') ?>">
      </div>
    </div>

    <!-- WhatsApp -->
    <div class="col-12">
      <label class="form-label">WhatsApp</label>
      <div class="input-group">
        <span class="input-group-text">+62</span>
        <input type="text" class="form-control" name="whatsapp"
               placeholder="8xxxxxxxxx atau wa.me/62…"
               value="<?= set_value('whatsapp', $user['whatsapp'] ?? '') ?>">
      </div>
      <div class="form-text">Isi angka saja (tanpa 0 di depan) atau link wa.me.</div>
    </div>
  </div>
</div>

<!-- ===== Media Sosial Lainnya -->
<div class="border rounded-3 p-3">
  <div class="d-flex align-items-center mb-2">
    <i class="bi bi-share me-2 text-muted"></i>
    <span class="fw-semibold">Media Sosial Lainnya</span>
  </div>

  <div class="row g-3">
    <!-- Instagram -->
    <div class="col-md-6">
      <label class="form-label">Instagram</label>
      <div class="input-group">
        <span class="input-group-text">@</span>
        <input type="text" class="form-control" name="instagram" placeholder="username"
               value="<?= set_value('instagram', $user['instagram'] ?? '') ?>">
      </div>
    </div>

    <!-- X / Twitter -->
    <div class="col-md-6">
      <label class="form-label">X / Twitter</label>
      <div class="input-group">
        <span class="input-group-text">@</span>
        <input type="text" class="form-control" name="twitter" placeholder="username atau URL"
               value="<?= set_value('twitter', $user['twitter'] ?? '') ?>">
      </div>
    </div>

    <!-- GitHub -->
    <div class="col-md-6">
      <label class="form-label">GitHub</label>
      <div class="input-group">
        <span class="input-group-text">github.com/</span>
        <input type="text" class="form-control" name="github" placeholder="username atau URL"
               value="<?= set_value('github', $user['github'] ?? '') ?>">
      </div>
    </div>

    <!-- Facebook -->
    <div class="col-md-6">
      <label class="form-label">Facebook</label>
      <div class="input-group">
        <span class="input-group-text">facebook.com/</span>
        <input type="text" class="form-control" name="facebook" placeholder="username/page atau URL"
               value="<?= set_value('facebook', $user['facebook'] ?? '') ?>">
      </div>
    </div>

    <!-- YouTube -->
    <div class="col-md-6">
      <label class="form-label">YouTube</label>
      <div class="input-group">
        <span class="input-group-text">youtube.com/</span>
        <input type="text" class="form-control" name="youtube" placeholder="@handle / channel URL"
               value="<?= set_value('youtube', $user['youtube'] ?? '') ?>">
      </div>
    </div>

    <!-- TikTok -->
    <div class="col-md-6">
      <label class="form-label">TikTok</label>
      <div class="input-group">
        <span class="input-group-text">@</span>
        <input type="text" class="form-control" name="tiktok" placeholder="username atau URL"
               value="<?= set_value('tiktok', $user['tiktok'] ?? '') ?>">
      </div>
    </div>

    <!-- Website / Portfolio -->
    <div class="col-md-6">
      <label class="form-label">Website / Portfolio</label>
      <div class="input-group">
        <span class="input-group-text">https://</span>
        <input type="url" class="form-control" name="website" placeholder="domain kamu…"
               value="<?= set_value('website', $user['website'] ?? '') ?>">
      </div>
    </div>

    <!-- Telegram -->
    <div class="col-md-6">
      <label class="form-label">Telegram</label>
      <div class="input-group">
        <span class="input-group-text">@</span>
        <input type="text" class="form-control" name="telegram" placeholder="username atau URL"
               value="<?= set_value('telegram', $user['telegram'] ?? '') ?>">
      </div>
    </div>

    <!-- Medium -->
    <div class="col-md-6">
      <label class="form-label">Medium</label>
      <div class="input-group">
        <span class="input-group-text">@</span>
        <input type="text" class="form-control" name="medium" placeholder="username atau URL"
               value="<?= set_value('medium', $user['medium'] ?? '') ?>">
      </div>
    </div>

    <!-- Behance -->
    <div class="col-md-6">
      <label class="form-label">Behance</label>
      <div class="input-group">
        <span class="input-group-text">behance.net/</span>
        <input type="text" class="form-control" name="behance" placeholder="username atau URL"
               value="<?= set_value('behance', $user['behance'] ?? '') ?>">
      </div>
    </div>

    <!-- Dribbble -->
    <div class="col-md-6">
      <label class="form-label">Dribbble</label>
      <div class="input-group">
        <span class="input-group-text">dribbble.com/</span>
        <input type="text" class="form-control" name="dribbble" placeholder="username atau URL"
               value="<?= set_value('dribbble', $user['dribbble'] ?? '') ?>">
      </div>
    </div>
  </div>
</div>




        
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>



<script>
function previewImg(input, targetId, maxMB = 3) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];

  if (!file.type.startsWith('image/')) {
    alert('File harus gambar (JPG/PNG).');
    input.value = ''; return;
  }
  if (file.size > maxMB * 1024 * 1024) {
    alert(`Ukuran gambar maksimal ${maxMB}MB.`);
    input.value = ''; return;
  }

  const reader = new FileReader();
  reader.onload = e => { document.getElementById(targetId).src = e.target.result; };
  reader.readAsDataURL(file);
}

// khusus: 2MB utk profile, 3MB utk cover
document.querySelector('input[name="photo_profile"]')
  ?.addEventListener('change', e => previewImg(e.target, 'previewProfile', 2));
document.querySelector('input[name="photo_cover"]')
  ?.addEventListener('change', e => previewImg(e.target, 'previewCover', 3));

// reset preview kalau modal ditutup (batal)
const modal = document.getElementById('modalEditProfile');
modal?.addEventListener('hidden.bs.modal', () => {
  const p = document.getElementById('previewProfile');
  const c = document.getElementById('previewCover');
  if (p && p.dataset.currentSrc) p.src = p.dataset.currentSrc;
  if (c && c.dataset.currentSrc) c.src = c.dataset.currentSrc;
  const f1 = document.querySelector('input[name="photo_profile"]');
  const f2 = document.querySelector('input[name="photo_cover"]');
  if (f1) f1.value = '';
  if (f2) f2.value = '';
});
</script>


<!-- PEKERJAAN SETUP -->
<div class="modal fade" id="modalEmployment" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="post" action="<?= base_url('user/employment_save') ?>">
      <?php if (function_exists('csrf_field')) echo csrf_field(); ?>

      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-briefcase me-2"></i>Pekerjaan Saat Ini</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Identitas -->
        <div class="border rounded-3 p-3 mb-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-building me-2 text-muted"></i>
            <span class="fw-semibold">Identitas</span>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Perusahaan/Instansi <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="employer" required
                     value="<?= h($cv['employer'] ?? '') ?>" placeholder="Contoh: PT ABC">
            </div>
            <div class="col-md-6">
              <label class="form-label">Posisi/Jabatan <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="employment_position" required
                     value="<?= h($cv['employment_position'] ?? '') ?>" placeholder="Contoh: Web Programmer">
            </div>
          </div>
        </div>

        <!-- Periode & Domisili -->
        <div class="border rounded-3 p-3 mb-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-calendar-week me-2 text-muted"></i>
            <span class="fw-semibold">Periode & Domisili</span>
          </div>
          <div class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label">Mulai</label>
              <input type="date" class="form-control" name="employment_from"
                     value="<?= h($cv['employment_from'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Sampai</label>
              <input type="date" class="form-control" name="employment_to" id="em_to"
                     value="<?= h($cv['employment_to'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" id="em_ongoing" <?= empty($cv['employment_to']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="em_ongoing">Masih bekerja</label>
              </div>
              <small class="text-muted d-block">Centang bila masih aktif (tanggal selesai akan dinonaktifkan).</small>
            </div>

            <div class="col-md-6">
              <label class="form-label">Domisili Negara/Wilayah</label>
              <input type="text" class="form-control" name="domisili_negara"
                     value="<?= h($cv['domisili_negara'] ?? '') ?>" placeholder="Contoh: Indonesia">
            </div>
            <div class="col-md-6">
              <label class="form-label">Domisili Kota</label>
              <input type="text" class="form-control" name="domisili_kota"
                     value="<?= h($cv['domisili_kota'] ?? '') ?>" placeholder="Contoh: Bandung">
            </div>
          </div>
        </div>

        <!-- Deskripsi -->
        <div class="border rounded-3 p-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-journal-text me-2 text-muted"></i>
            <span class="fw-semibold">Deskripsi</span>
          </div>
          <label class="form-label">Deskripsi Pekerjaan</label>
          <textarea class="form-control" rows="3" name="employment_desc"
                    placeholder="Tanggung jawab utama, teknologi/alat yang digunakan"><?= h($cv['employment_desc'] ?? '') ?></textarea>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  const emOngoing = document.getElementById('em_ongoing');
  const emTo = document.getElementById('em_to');
  if (emOngoing && emTo) {
    const toggle = () => { emTo.disabled = emOngoing.checked; if (emOngoing.checked) emTo.value=''; };
    toggle(); emOngoing.addEventListener('change', toggle);
  }
})();
</script>


<!-- PENGALAMAN SETUP -->
<div class="modal fade" id="modalPengalaman" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <form class="modal-content" method="post" action="<?= base_url('user/experience_save') ?>" enctype="multipart/form-data">
      <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
      <input type="hidden" name="id" id="exp_id">

      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-briefcase me-2"></i>Pengalaman Kerja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <!-- Identitas Proyek -->
        <div class="border rounded-3 p-3 mb-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-card-text me-2 text-muted"></i>
            <span class="fw-semibold">Identitas Proyek</span>
          </div>
          <div class="row g-3">
            <div class="col-lg-8">
              <label class="form-label">Nama Kegiatan/Project <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama_kegiatan" id="exp_nama_kegiatan" required placeholder="Contoh: Sistem Informasi X">
              <div class="form-text">Nama resmi/judul pekerjaan.</div>
            </div>
            <div class="col-lg-4">
              <label class="form-label">Posisi/Jabatan</label>
              <input type="text" class="form-control" name="posisi" id="exp_posisi" placeholder="Contoh: Web Programmer">
            </div>
          </div>
        </div>

        <!-- Organisasi Proyek -->
        <div class="border rounded-3 p-3 mb-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-building me-2 text-muted"></i>
            <span class="fw-semibold">Organisasi</span>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Pemberi Kerja (Client/Owner)</label>
              <input type="text" class="form-control" name="pemberi_pekerjaan" id="exp_pemberi" placeholder="Nama instansi/klien">
            </div>
            <div class="col-md-6">
              <label class="form-label">Pelaksana Proyek (Implementor/Kontraktor)</label>
              <input type="text" class="form-control" name="pelaksana_proyek" id="exp_pelaksana" placeholder="Nama perusahaan pelaksana">
            </div>
          </div>
        </div>

        <!-- Lokasi & Periode -->
        <div class="border rounded-3 p-3 mb-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-geo-alt me-2 text-muted"></i>
            <span class="fw-semibold">Lokasi & Periode</span>
          </div>
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Kota</label>
              <input type="text" class="form-control" name="lokasi" id="exp_lokasi" placeholder="Contoh: Bandung">
            </div>
            <div class="col-md-4">
              <label class="form-label">Negara/Wilayah</label>
              <input type="text" class="form-control" name="negara" id="exp_negara" placeholder="Contoh: Indonesia">
            </div>
            <div class="col-md-2">
              <label class="form-label">Mulai</label>
              <input type="date" class="form-control" name="waktu_mulai" id="exp_mulai">
            </div>
            <div class="col-md-2">
              <label class="form-label">Selesai</label>
              <input type="date" class="form-control" name="waktu_akhir" id="exp_akhir">
            </div>
            <div class="col-12 d-flex align-items-center gap-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="exp_ongoing">
                <label class="form-check-label" for="exp_ongoing">Masih berlangsung</label>
              </div>
              <small class="text-muted">Centang bila proyek belum selesai (tanggal selesai akan dinonaktifkan).</small>
            </div>
          </div>
        </div>

        <!-- Deskripsi & Dokumen -->
        <div class="border rounded-3 p-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-journal-text me-2 text-muted"></i>
            <span class="fw-semibold">Deskripsi & Dokumen</span>
          </div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Ringkasan Proyek</label>
              <textarea class="form-control" rows="2" name="uraian_proyek" id="exp_uraian_proyek" placeholder="Garis besar tujuan dan ruang lingkup proyek"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Uraian Tugas</label>
              <textarea class="form-control" rows="4" name="uraian_tugas" id="exp_uraian_tugas" placeholder="Tanggung jawab, milestone, tools/tech yang digunakan"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Referensi (PDF/JPG/PNG)</label>
                
                <div id="exp_file_control">
                    <input type="file" class="form-control" name="referensi_file" id="exp_referensi_file_input" accept=".pdf,.jpg,.jpeg,.png">
                    
                    <div id="exp_file_visual_name" class="form-control" style="display:none; cursor:pointer; user-select:none;">
                        <span class="text-truncate"></span>
                        <small class="text-muted float-end">Klik untuk Ganti</small>
                    </div>
                </div>
                <div class="form-text">Opsional: TOR, surat tugas, sertifikat, dsb. Maks ~5MB.</div>
                <input type="hidden" name="referensi_file_old" id="exp_referensi_file_old">
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <!-- Hide/show item-->
        <div class="me-auto form-check form-switch"> 
            <input type="hidden" name="is_visible" value="0">
            
            <input class="form-check-input" type="checkbox" role="switch" 
                  id="exp_is_visible_modal" name="is_visible" value="1" checked> 
            <label class="form-check-label small text-muted" for="exp_is_visible_modal">Tampilkan di Profil</label>
        </div>
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>


<script>
// MODAL PENGALAMAN Setup (Sudah Diperbaiki + Sinkronisasi is_visible)
(function(){
  const mp = document.getElementById('modalPengalaman');
  if (!mp) return;

  const q = sel => mp.querySelector(sel);
  const endInput = () => q('#exp_akhir');
  const cbOngoing = () => q('#exp_ongoing');
  const cbVisible = () => q('#exp_is_visible_modal'); // ✅ Checkbox/toggle untuk visibilitas
  
  // === HELPER FILE CONTROLS BARU ===
  const fileInput = () => q('#exp_referensi_file_input');
  const oldPathInput = () => q('#exp_referensi_file_old');
  const visualNameDiv = () => q('#exp_file_visual_name'); // Target DIV tiruan
  // =================================
  
  const setVal = (id, val='') => { const el = q('#'+id); if (el) el.value = val; };

  const resetForm = () => {
    // Reset non-file
    [
      'exp_id','exp_nama_kegiatan','exp_posisi','exp_pemberi','exp_pelaksana',
      'exp_lokasi','exp_negara','exp_mulai','exp_akhir','exp_uraian_proyek','exp_uraian_tugas'
    ].forEach(id => setVal(id, ''));
    if (cbOngoing()) cbOngoing().checked = false;
    if (cbVisible()) cbVisible().checked = true; // ✅ default visible
    if (endInput()) { endInput().disabled = false; endInput().value = ''; }
    
    // Reset file
    if (fileInput()) try { fileInput().value = ''; } catch(e){}
    if (oldPathInput()) oldPathInput().value = '';
    
    // Reset tampilan file visual
    if (visualNameDiv()) visualNameDiv().style.display = 'none';
    if (fileInput()) fileInput().style.display = ''; // Tampilkan input file asli
  };

  const prefillFromBtn = (btn) => {
    const d = btn.dataset || {};
    
    // Logic set value non-file
    setVal('exp_id',             d.id || '');
    setVal('exp_nama_kegiatan',  d.nama_kegiatan || '');
    setVal('exp_posisi',         d.posisi || '');
    setVal('exp_pemberi',        d.pemberi_pekerjaan || d.pemberi || '');
    setVal('exp_pelaksana',      d.pelaksana_proyek   || d.pelaksana || '');
    setVal('exp_lokasi',         d.lokasi || '');
    setVal('exp_negara',         d.negara || '');
    setVal('exp_mulai',          d.waktu_mulai || d.mulai || '');
    setVal('exp_akhir',          d.waktu_akhir || d.akhir || '');
    setVal('exp_uraian_proyek',  d.uraian_proyek || '');
    setVal('exp_uraian_tugas',   d.uraian_tugas  || '');

    // ✅ Prefill is_visible
    if (cbVisible()) {
      cbVisible().checked = d.is_visible == "1" || d.is_visible === 1;
    }

    // File handler
    const filePath = d.referensi_file;
    if (filePath) {
      const fileName = filePath.substring(filePath.lastIndexOf('/') + 1);
      if (oldPathInput()) oldPathInput().value = filePath;
      if (fileInput()) fileInput().style.display = 'none';
      if (visualNameDiv()) {
          visualNameDiv().style.display = 'block'; 
          visualNameDiv().querySelector('span').textContent = fileName;
      }
    } else {
      if (oldPathInput()) oldPathInput().value = '';
    }

    // auto-centang ongoing
    const noEnd = !(d.waktu_akhir || d.akhir);
    if (cbOngoing()) cbOngoing().checked = !!noEnd;
    if (endInput()) endInput().disabled  = !!noEnd;
  };

  // Event buka modal
  mp.addEventListener('show.bs.modal', (e) => {
    const btn  = e.relatedTarget;
    const mode = btn?.getAttribute('data-mode') || (btn?.dataset?.id ? 'edit' : 'add');
    
    resetForm();
    if (mode === 'edit' && btn) prefillFromBtn(btn);
  });
  
  // Toggle ongoing
  cbOngoing()?.addEventListener('change', () => {
    const end = endInput(); if (!end) return;
    if (cbOngoing().checked) { end.value = ''; end.disabled = true; }
    else { end.disabled = false; }
  });

  // LOGIC KLIK: Jika div visual diklik, kembalikan ke input file asli
  visualNameDiv()?.addEventListener('click', () => {
    visualNameDiv().style.display = 'none';
    fileInput().style.display = '';
    fileInput().click();
  });
})();
</script>


<!-- PENDIDIKAN SETUP -->
<div class="modal fade" id="modalPendidikan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="post" action="<?= base_url('user/education_save') ?>" enctype="multipart/form-data">
      <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
      <input type="hidden" name="id" id="edu_id">

      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-mortarboard me-2"></i>Pendidikan Formal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Institusi & Program -->
        <div class="border rounded-3 p-3 mb-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-bank me-2 text-muted"></i>
            <span class="fw-semibold">Institusi & Program</span>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Institusi <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="institusi" id="edu_institusi" required placeholder="Contoh: Universitas XYZ">
            </div>
            <div class="col-md-3">
              <label class="form-label">Tingkat</label>
              <input type="text" class="form-control" name="tingkat" id="edu_tingkat" placeholder="S1/S2/D3">
            </div>
            <div class="col-md-3">
              <label class="form-label">Tahun Lulus</label>
              <input type="number" class="form-control" name="tahun_lulus" id="edu_tahun" min="1900" max="2099" placeholder="2022">
            </div>
            <div class="col-12">
              <label class="form-label">Jurusan / Program Studi</label>
              <input type="text" class="form-control" name="jurusan" id="edu_jurusan" placeholder="Contoh: Informatika">
            </div>
          </div>
        </div>

        <!-- Dokumen -->
        <div class="border rounded-3 p-3">
            <label class="form-label">Ijazah (opsional)</label>
            
            <div id="edu_file_control">
                <input type="file" class="form-control" name="ijazah_file" id="edu_ijazah_file_input" accept=".pdf,.jpg,.jpeg,.png">

                <div id="edu_file_visual_name" class="form-control" style="display:none; cursor:pointer; user-select:none;">
                    <span class="text-truncate"></span>
                    <small class="text-muted float-end">Klik untuk Ganti</small>
                </div>
            </div>
            
            <div class="form-text">PDF/JPG/PNG, maks ~5MB.</div>
            <input type="hidden" name="ijazah_file_old" id="edu_ijazah_file_old">
        </div>

      </div>

      <div class="modal-footer">
        <!-- Hide/show item -->
        <div class="me-auto form-check form-switch">
          <input type="hidden" name="is_visible" value="0">
          <input class="form-check-input" type="checkbox" role="switch"
                id="edu_is_visible_modal" name="is_visible" value="1" checked>
          <label class="form-check-label small text-muted" for="edu_is_visible_modal">
            Tampilkan di Profil
          </label>
        </div>
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
      </div>

    </form>
  </div>
</div>

<script>
// MODAL PENDIDIKAN Setup
(function(){
  const mp = document.getElementById('modalPendidikan');
  if (!mp) return;
  const q = sel => mp.querySelector(sel);

  const fileInput = () => q('#edu_ijazah_file_input');
  const oldPathInput = () => q('#edu_ijazah_file_old');
  const visualNameDiv = () => q('#edu_file_visual_name');
  const cbVisible = () => q('#edu_is_visible_modal'); // ✅ checkbox tampil di profil

  const setVal = (id, val='') => { const el = q('#'+id); if (el) el.value = val; };

  const resetForm = () => {
    ['edu_id','edu_institusi','edu_tingkat','edu_jurusan','edu_tahun'].forEach(id => setVal(id, ''));
    if (fileInput()) try { fileInput().value = ''; } catch(e){}
    if (oldPathInput()) oldPathInput().value = '';
    if (visualNameDiv()) visualNameDiv().style.display = 'none';
    if (fileInput()) fileInput().style.display = '';
    if (cbVisible()) cbVisible().checked = true; // ✅ default tampil
  };

  mp.addEventListener('show.bs.modal', (e) => {
    const btn  = e.relatedTarget;
    const mode = btn?.getAttribute('data-mode') || 'add';
    resetForm();

    if (mode === 'edit' && btn) {
      const d = btn.dataset || {};
      setVal('edu_id', d.id || '');
      setVal('edu_institusi', d.institusi || '');
      setVal('edu_tingkat', d.tingkat || '');
      setVal('edu_jurusan', d.jurusan || '');
      setVal('edu_tahun', d.tahun_lulus || '');

      if (cbVisible()) cbVisible().checked = d.is_visible == "1" || d.is_visible === 1; // ✅ prefill toggle

      const filePath = d.ijazah_file;
      if (filePath) {
        const fileName = filePath.substring(filePath.lastIndexOf('/') + 1);
        if (oldPathInput()) oldPathInput().value = filePath;
        if (fileInput()) fileInput().style.display = 'none';
        if (visualNameDiv()) {
          visualNameDiv().style.display = 'block';
          visualNameDiv().querySelector('span').textContent = fileName;
        }
      }
    }
  });

  visualNameDiv()?.addEventListener('click', () => {
    visualNameDiv().style.display = 'none';
    fileInput().style.display = '';
    fileInput().click();
  });
})();

</script>


<!-- SERIFIKASI SETUP -->
<div class="modal fade" id="modalSertifikasi" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    
    <form class="modal-content" method="post" action="<?= base_url('user/cert_save') ?>" enctype="multipart/form-data">
      
      <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
      <input type="hidden" name="id" id="cert_id">
      <input type="hidden" name="file_lama" id="cert_file_lama">

      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-patch-check me-2"></i>Sertifikasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="border rounded-3 p-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-award me-2 text-muted"></i>
            <span class="fw-semibold">Rincian Sertifikasi</span>
          </div>
          
          <div class="row g-3">
            
            <div class="col-md-6">
              <label class="form-label">Nama Sertifikasi <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama" id="cert_nama" required placeholder="Contoh: AWS Certified Developer">
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Penerbit</label>
              <input type="text" class="form-control" name="penerbit" id="cert_penerbit" placeholder="Contoh: Amazon">
            </div>
            
            <div class="col-md-2">
              <label class="form-label">Tahun</label>
              <input type="number" class="form-control" name="tahun" id="cert_tahun" min="1900" max="2099" placeholder="2024">
            </div>

            <div class="col-md-12">
              <label class="form-label">File Sertifikat (pdf/jpg/png, maks 2MB)</label>
              <input type="file" class="form-control" name="file_sertifikat" id="cert_file_input" accept=".pdf,.jpg,.jpeg,.png">
              <div class="form-text small" id="cert_file_info"></div>
            </div>

          </div> </div> </div> <div class="modal-footer">
        <div class="me-auto form-check form-switch"> 
          <input type="hidden" name="is_visible" value="0">
          <input class="form-check-input" type="checkbox" role="switch" 
                 id="cert_is_visible_modal" name="is_visible" value="1" checked> 
          <label class="form-check-label small text-muted" for="cert_is_visible_modal">Tampilkan di Profil</label>
        </div>
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
// MODAL SERTIFIKASI Setup (Sudah + file upload)
(function(){
  const ms = document.getElementById('modalSertifikasi');
  if (!ms) return;

  const q = sel => ms.querySelector(sel);
  const setVal = (id, v='') => { const el = q('#'+id); if (el) el.value = v; };
  const setInfo = (id, html='') => { const el = q('#'+id); if (el) el.innerHTML = html; }; // <-- Helper baru

  const checkbox = q('#cert_is_visible_modal');
  const fileInput = q('#cert_file_input'); // <-- Input file baru
  
  const resetForm = () => { 
    // Tambahkan 'cert_file_lama' ke daftar reset
    ['cert_id','cert_nama','cert_penerbit','cert_tahun', 'cert_file_lama'].forEach(id => setVal(id, '')); 
    if (fileInput) fileInput.value = ''; // Reset input file
    setInfo('cert_file_info', ''); // Hapus info file
  };

  ms.addEventListener('show.bs.modal', (e) => {
    const btn  = e.relatedTarget;
    const mode = btn?.getAttribute('data-mode') || (btn?.dataset?.id ? 'edit' : 'add');
    const itemId = btn?.dataset?.id || null;

    resetForm();
    if (checkbox) checkbox.checked = true;

    if (mode === 'edit' && btn && itemId) {
      const d = btn.dataset || {};

      setVal('cert_id',       itemId);
      setVal('cert_nama',     d.nama || '');
      setVal('cert_penerbit', d.penerbit || '');
      setVal('cert_tahun',    d.tahun || '');

      // Logika untuk 'is_visible'
      if (checkbox) {
        const isVisible = d.is_visible;
        setTimeout(() => { // (setTimeout-mu masih ada di sini, jadi saya biarkan)
          checkbox.checked = (isVisible !== '0');
        }, 50);
      }
      
      // ▼▼▼ TAMBAHKAN BLOK LOGIKA FILE INI ▼▼▼
      const fileValue = d.file_sertifikat || '';
      setVal('cert_file_lama', fileValue);
      
      if (fileValue) {
        setInfo('cert_file_info', `File saat ini: <strong>${fileValue}</strong>. Upload file baru untuk menggantinya.`);
      } else {
        setInfo('cert_file_info', 'Belum ada file sertifikat.');
      }
      // ▲▲▲ AKHIR BLOK FILE ▲▲▲

    } else {
      // Mode 'add'
      setInfo('cert_file_info', 'File belum di-upload.');
    }
  });
})();
</script>


<!-- Pendidikan Non Formal Setup(Pelatihan) -->
<div class="modal fade" id="modalPelatihan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    
    <form class="modal-content" method="post" action="<?= base_url('user/nonformal_save') ?>" enctype="multipart/form-data">
      
      <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
      <input type="hidden" name="id" id="pelatihan_id">
      <input type="hidden" name="file_lama" id="pelatihan_file_lama">

      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-workspace me-2"></i>Pelatihan & Kursus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        
        <div class="border rounded-3 p-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-info-circle me-2 text-muted"></i>
            <span class="fw-semibold">Rincian Pelatihan</span>
          </div>
          
          <div class="row g-3">
            
            <div class="col-md-6">
              <label class="form-label">Nama Pelatihan <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama_pelatihan" id="pelatihan_nama" required placeholder="Contoh: Digital Marketing Bootcamp">
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="penyelenggara" id="pelatihan_penyelenggara" required placeholder="Contoh: Google">
            </div>
            
            <div class="col-md-2">
              <label class="form-label">Tahun <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="tahun" id="pelatihan_tahun" min="1900" max="2099" required placeholder="2024">
            </div>
            
            <div class="col-md-12">
              <label class="form-label">Sertifikat (pdf/jpg/png, maks 2MB)</label>
              <input type="file" class="form-control" name="sertifikat_file" id="pelatihan_file_sertifikat" accept=".pdf,.jpg,.jpeg,.png">
              <div class="form-text small" id="pelatihan_file_info"></div>
            </div>
            
          </div> </div> </div> <div class="modal-footer">
        <div class="me-auto form-check form-switch"> 
          <input type="hidden" name="is_visible" value="0">
          <input class="form-check-input" type="checkbox" role="switch" 
                 id="pelatihan_is_visible_modal" name="is_visible" value="1" checked> 
          <label class="form-check-label small text-muted" for="pelatihan_is_visible_modal">Tampilkan di Profil</label>
        </div>
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
      </div>

    </form>
  </div>
</div>


<script>
// MODAL PELATIHAN Setup (Sudah dimodifikasi)
(function(){
  const modal = document.getElementById('modalPelatihan');
  if (!modal) return;

  const q = sel => modal.querySelector(sel);
  const setVal = (id, v='') => { const el = q('#'+id); if (el) el.value = v; };
  const setInfo = (id, html='') => { const el = q('#'+id); if (el) el.innerHTML = html; };

  const checkbox = q('#pelatihan_is_visible_modal'); // <-- Selector untuk switch
  const fileInput = q('#pelatihan_file_sertifikat');
  
  const resetForm = () => { 
    ['pelatihan_id', 'pelatihan_nama', 'pelatihan_penyelenggara', 'pelatihan_tahun', 'pelatihan_file_lama'].forEach(id => setVal(id, '')); 
    if (fileInput) fileInput.value = '';
    setInfo('pelatihan_file_info', '');
  };

  modal.addEventListener('show.bs.modal', (e) => {
    const btn  = e.relatedTarget;
    const mode = btn?.getAttribute('data-mode') || 'add';
    const itemId = btn?.dataset?.id || null;

    resetForm();
    
    if (checkbox) checkbox.checked = true; // <-- Default 'checked' untuk mode 'add'

    if (mode === 'edit' && btn && itemId) {
      const d = btn.dataset || {};

      setVal('pelatihan_id',            itemId);
      setVal('pelatihan_nama',          d.nama_pelatihan || ''); 
      setVal('pelatihan_penyelenggara', d.penyelenggara || '');
      setVal('pelatihan_tahun',         d.tahun || '');
      setVal('pelatihan_file_lama',     d.sertifikat_file || '');

      // Info file
      if (d.sertifikat_file) {
        setInfo('pelatihan_file_info', `File saat ini: <strong>${d.sertifikat_file}</strong>. Upload file baru untuk menggantinya.`);
      } else {
        setInfo('pelatihan_file_info', 'Belum ada file sertifikat.');
      }
      
      // <-- Blok untuk membaca status switch
      if (checkbox) {
        // Asumsi tombol edit memiliki 'data-is_visible'
        const isVisible = d.is_visible; 
        checkbox.checked = (isVisible !== '0');
      }
      
  T } else {
      // Mode 'add'
      setInfo('pelatihan_file_info', 'File belum di-upload.');
    }
  });
})();
</script>

<!-- BAHASA SETUP -->
<div class="modal fade" id="modalBahasa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="post" action="<?= base_url('user/lang_save') ?>">
      <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
      <input type="hidden" name="id" id="lang_id">
      <div class="modal-header"><h5 class="modal-title">Bahasa & Kemampuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="border rounded-3 p-3">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Bahasa</label><input type="text" class="form-control" name="bahasa" id="lang_bahasa" required></div>
            <div class="col-md-2"><label class="form-label">Speaking</label><input type="text" class="form-control" name="speaking" id="lang_speaking"></div>
            <div class="col-md-2"><label class="form-label">Reading</label><input type="text" class="form-control" name="reading" id="lang_reading"></div>
            <div class="col-md-2"><label class="form-label">Writing</label><input type="text" class="form-control" name="writing" id="lang_writing"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="me-auto form-check form-switch"> 
            <input type="hidden" name="is_visible" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="lang_is_visible_modal" name="is_visible" value="1" checked> 
            <label class="form-check-label small text-muted" for="lang_is_visible_modal">Tampilkan</label>
        </div>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
    </div>
</div>

<script>
// MODAL BAHASA Setup (KODE TERAKHIR DENGAN TIMING FIX)
(function(){
  const mb = document.getElementById('modalBahasa');
  if (!mb) return;

  const q = sel => mb.querySelector(sel);
  const setVal = (id, v='') => { const el = q('#'+id); if (el) el.value = v; };

  // Target checkbox
  const checkbox = mb.querySelector('#lang_is_visible_modal'); 

  const resetForm = () => { 
    ['lang_id','lang_bahasa','lang_speaking','lang_reading','lang_writing'].forEach(id => setVal(id, '')); 
  };

  mb.addEventListener('show.bs.modal', e => {
    const btn  = e.relatedTarget;
    const mode = btn?.getAttribute('data-mode') || (btn?.dataset?.id ? 'edit' : 'add');
    const itemId = btn?.dataset?.id || null;

    resetForm();
    
    // Pengaturan Default untuk Mode ADD
    if (checkbox) checkbox.checked = true;

    if (mode === 'edit' && btn && itemId) { 
        const d = btn.dataset || {};
        
        setVal('lang_id', itemId); 
        setVal('lang_bahasa',    d.bahasa || '');
        setVal('lang_speaking',  d.speaking || '');
        setVal('lang_reading',   d.reading || '');
        setVal('lang_writing',   d.writing || '');

        // KODE KRITIS: SINKRONISASI TOGGLE DENGAN DELAY
        const isVisible = d.is_visible; // gunakan nama atribut sebenarnya
        
        if (checkbox) {
            // Tambahkan sedikit delay (misal 50ms) untuk menghindari konflik timing
            setTimeout(() => {
                checkbox.checked = (isVisible !== '0');
            }, 50); 
        }
      }
    });
})();
</script>


<!-- ============================== -->
<!-- MODAL BARU: LAMPIRAN ITEM -->
<!-- ============================== -->
<div class="modal fade" id="modalLampiranItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" method="post" action="<?= base_url('user/lampiran_item_save') ?>" enctype="multipart/form-data">
            <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
            <input type="hidden" name="field_name" id="lampiran_field_name">
            <input type="hidden" name="file_lama" id="lampiran_file_lama">

            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload me-2"></i><span id="lampiran_modal_title">Upload Lampiran</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="border rounded-3 p-3">
                    <div class="mb-3">
                        <label class="form-label">File (pdf/jpg/jpeg/png, maks 5MB)</label>
                        <input type="file" class="form-control" name="file_lampiran" id="lampiran_file_input" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text mt-2" id="lampiran_file_info"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="me-auto form-check form-switch"> 
                    <input type="hidden" name="is_visible" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="lampiran_is_visible_modal" name="is_visible" value="1" checked> 
                    <label class="form-check-label small text-muted" for="lampiran_is_visible_modal">Tampilkan di Profil</label>
                </div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================== -->
<!-- JAVASCRIPT BARU: MODAL LAMPIRAN -->
<!-- ============================== -->
<script>
(function(){
    const modalEl = document.getElementById('modalLampiranItem');
    if (!modalEl) return;
    
    const q = sel => modalEl.querySelector(sel);
    const titleSpan = q('#lampiran_modal_title');
    const fileLamaInput = q('#lampiran_file_lama');
    const fileInfo = q('#lampiran_file_info');
    const fileInput = q('#lampiran_file_input');
    const checkbox = q('#lampiran_is_visible_modal');

    modalEl.addEventListener('show.bs.modal', (e) => {
        const btn = e.relatedTarget;
        if (!btn) return;
        const d = btn.dataset || {};

        // 1. Reset form & data
        fileInput.value = '';
        
        const fieldName = d.field_name || '';
        const label = d.label || 'Lampiran';
        const fileValue = d.file_value || '';
        const isVisible = d.is_visible;

        // Isi data tersembunyi
        if (q('#lampiran_field_name')) q('#lampiran_field_name').value = fieldName;
        fileLamaInput.value = fileValue;
        if (titleSpan) titleSpan.textContent = label;
        
        // 2. Atur required dan info file
        if (fileValue) {
            // Jika ada file lama, input file tidak wajib diisi
            fileInput.removeAttribute('required');
            
            // Buat link ke file lama
            const fileUrl = '<?= base_url() ?>' + fileValue;
            fileInfo.innerHTML = `File saat ini: <a href="${fileUrl}" target="_blank" class="link-primary" style="word-break: break-all;"><i class="bi bi-file-earmark-arrow-down me-1"></i>${fileValue}</a>. Upload file baru untuk menggantinya.`;
            
        } else {
            // Jika tidak ada file lama, input file wajib diisi
            fileInput.setAttribute('required', 'required');
            fileInfo.innerHTML = 'Belum ada file yang di-upload.';
        }

        // 3. Set status visibilitas
        if (checkbox) {
            setTimeout(() => {
                // Default ke 'checked' (true/1) jika file belum ada atau jika isVisible bukan '0'
                checkbox.checked = (isVisible !== '0');
            }, 50); 
        }
    });
})();
</script>


<script>
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el=>{
    new bootstrap.Tooltip(el);
  });
</script>

<script>
(function initEmailTagify() {
  // nunggu Tagify siap; kalau belum, retry
  const boot = () => {
    if (typeof Tagify === 'undefined') { setTimeout(boot, 150); return; }

    // Cari semua input yang butuh Tagify
    document.querySelectorAll('[data-role="email-tags"]').forEach(el => {
      if (el._tagify) return; // sudah di-init

      const hiddenId = el.dataset.hiddenTarget || 'email_hidden';
      const hidden   = document.getElementById(hiddenId);

      // Init Tagify
      const tag = new Tagify(el, {
        delimiters: ",",
        editTags: 1,
        duplicates: false,
        trim: true,
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, // validasi email basic
        dropdown: { enabled: 0 },
        placeholder: "email1@contoh.com, email2@contoh.com"
      });
      el._tagify = tag;

      // Prefill dari hidden (comma separated => chips)
      const raw = (hidden?.value || '').trim();
      if (raw) {
        const arr = raw.split(',')
          .map(s => s.trim())
          .filter(Boolean)
          .map(v => ({ value: v }));
        tag.addTags(arr);
      }

      // Sinkron setiap ada perubahan
      const sync = () => {
        if (!hidden) return;
        hidden.value = tag.value.map(t => t.value).join(', ');
      };
      tag.on('add', sync);
      tag.on('remove', sync);
      el.addEventListener('change', sync);
      el.addEventListener('blur', sync);
      el.form?.addEventListener('submit', sync);
    });
  };

  // Init saat DOM siap
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  // Bonus: kalau kamu buka modal via Bootstrap, pastikan init lagi kalau perlu
  const modal = document.getElementById('modalEditProfile');
  modal?.addEventListener('shown.bs.modal', boot);
})();
</script>

<script>
(function() {
  const form = document.querySelector('#modalEditProfile form');
  if (!form) return;

  const norm = {
    instagram: v => v ? 'https://instagram.com/' + v.replace(/^@/, '').replace(/^https?:\/\/(www\.)?instagram\.com\//i,'') : '',
    twitter:   v => v ? 'https://twitter.com/'   + v.replace(/^@/, '').replace(/^https?:\/\/(www\.)?(x|twitter)\.com\//i,'') : '',
    github:    v => v ? 'https://github.com/'    + v.replace(/^https?:\/\/(www\.)?github\.com\//i,'') : '',
    facebook:  v => v ? 'https://facebook.com/'  + v.replace(/^https?:\/\/(www\.)?facebook\.com\//i,'') : '',
    youtube:   v => {
      if (!v) return '';
      // kalau sudah URL, biarin
      if (/^https?:\/\//i.test(v)) return v;
      // channel handle @nama
      if (v.startsWith('@')) return 'https://www.youtube.com/' + v;
      return 'https://www.youtube.com/@' + v;
    },
    tiktok:    v => v ? 'https://www.tiktok.com/@' + v.replace(/^@/,'').replace(/^https?:\/\/(www\.)?tiktok\.com\/@/i,'') : '',
    website:   v => !v ? '' : (/^https?:\/\//i.test(v) ? v : 'https://' + v),
    telegram:  v => v ? 'https://t.me/' + v.replace(/^@/,'').replace(/^https?:\/\/t\.me\//i,'') : '',
    medium:    v => v ? 'https://medium.com/@' + v.replace(/^@/,'').replace(/^https?:\/\/(www\.)?medium\.com\/@/i,'') : '',
    behance:   v => v ? 'https://www.behance.net/' + v.replace(/^https?:\/\/(www\.)?behance\.net\//i,'') : '',
    dribbble:  v => v ? 'https://dribbble.com/' + v.replace(/^https?:\/\/(www\.)?dribbble\.com\//i,'') : '',
    whatsapp:  v => {
      if (!v) return '';
      // kalau URL udah wa.me, keep
      if (/^https?:\/\/(www\.)?wa\.me\//i.test(v)) return v;
      // ambil digit
      const digits = v.replace(/\D/g,'');
      // kalau kosong, return kosong
      if (!digits) return '';
      // pastikan 62 diawal (sesuai kebutuhan Indo)
      const num = digits.startsWith('62') ? digits : ('62' + digits.replace(/^0+/,''));
      return 'https://wa.me/' + num;
    }
  };

  form.addEventListener('submit', () => {
    for (const key of Object.keys(norm)) {
      const el = form.querySelector(`[name="${key}"]`);
      if (el && el.value) el.value = norm[key](el.value.trim());
    }
  });
})();




</script>

<!-- ============================== -->
<!-- Link -->
<!-- ============================== -->

<link rel="stylesheet" href="<?= base_url('assets/css/profile.css') ?>">

<script>
  window.API_URL = "<?= base_url('user/toggle_section_visibility') ?>";
  window.CSRF_NAME = "<?= $this->security->get_csrf_token_name() ?>";
  window.CSRF_HASH = "<?= $this->security->get_csrf_hash() ?>";
</script>

<script src="<?= base_url('assets/js/profile-toggle.js') ?>"></script>
