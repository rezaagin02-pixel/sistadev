<?php
// require_once APPPATH . 'third_party/dompdf/autoload.inc.php';
require_once FCPATH . 'vendor/autoload.php';
defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf;
use Dompdf\Options;
class User extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
        date_default_timezone_set("Asia/Bangkok");
        $this->load->helper('string');
        $this->load->model('Cv_model');
        $this->load->model('User_model');
        $this->load->library('PHPWordLibrary');
        
        
    }

    private function flash($type, $msg){
    // type: success | error | info | warning
    $this->session->set_flashdata('alert_type', $type);
    $this->session->set_flashdata('alert_msg',  $msg);
    }


     /* ===================== PROFILE PAGE ===================== */
    public function profile()
    {
        $data['title'] = 'Profil';
        $data['user']  = $this->db->get_where('user', [
            'email' => $this->session->userdata('email')
        ])->row_array();
        if (!$data['user']) { redirect('auth'); return; }

        $user_id = (int) $data['user']['id'];
        $cv = $this->db->get_where('cv', ['user_id' => $user_id])->row_array();
        $data['cv'] = $cv ?: [];
        $cv_id = $cv['id'] ?? null;

        $hasCv = $this->db->where('user_id', $user_id)->count_all_results('cv') > 0;

        

        if ($cv_id) {
            // Pengalaman (terbaru)
            $this->db->order_by('COALESCE(waktu_akhir, NOW())', 'DESC', false);
            $this->db->order_by('waktu_mulai', 'DESC');
            $pengalaman_kerja = $this->db->get_where('pengalaman_kerja', ['cv_id' => $cv_id])->result_array();

            // Pendidikan formal
            $this->db->order_by('tahun_lulus', 'DESC');
            $pendidikan_formal = $this->db->get_where('pendidikan_formal', ['cv_id' => $cv_id])->result_array();

            // Sertifikasi
            $this->db->order_by('tahun', 'DESC');
            $sertifikasi_profesi = $this->db->get_where('sertifikasi_profesi', ['cv_id' => $cv_id])->result_array();

            // Pelatihan
            $this->db->order_by('tahun', 'DESC');
            $pendidikan_nonformal = $this->db->get_where('pendidikan_nonformal', ['cv_id' => $cv_id])->result_array();

            // Bahasa
            $bahasa_list = $this->db->table_exists('bahasa')
                ? $this->db->get_where('bahasa', ['cv_id' => $cv_id])->result_array()
                : [];

            // Lampiran (opsional)
            $lampiran = $this->db->get_where('lampiran_cv', ['cv_id' => $cv_id])->row_array();

            $counts = [
                'pengalaman' => count($pengalaman_kerja),
                'pendidikan' => count($pendidikan_formal),
                'sertifikasi'=> count($sertifikasi_profesi),
                'pelatihan'  => count($pendidikan_nonformal)
            ];
        } else {
            $pengalaman_kerja = $pendidikan_formal = $sertifikasi_profesi = $bahasa_list = [];
            $lampiran = [];
            $counts = ['pengalaman'=>0,'pendidikan'=>0,'sertifikasi'=>0];
        }
        // TAMBAHKAN BLOK INI:
        // ----------------------------------------------------
        // Ambil semua setting visibilitas untuk user ini
        $visibility_rows = $this->db
            ->get_where('user_visibility', ['user_id' => $user_id])
            ->result_array();

        // Ubah jadi array yang gampang dibaca: [ 'sertifikasi' => 0, 'pengalaman' => 1 ]
        $visibility_map = [];
        foreach ($visibility_rows as $row) {
            $visibility_map[$row['section']] = (int)$row['is_visible'];
        }
        
        $data['visibility_map'] = $visibility_map; // Kirim ke view
        // ----------------------------------------------------
        // AKHIR BLOK TAMBAHAN
        $data['pengalaman_kerja']    = $pengalaman_kerja;
        $data['pendidikan_formal']   = $pendidikan_formal;
        $data['sertifikasi_profesi'] = $sertifikasi_profesi;
        $data['bahasa_list']         = $bahasa_list;
        $data['lampiran']            = $lampiran;
        $data['pendidikan_nonformal'] = $pendidikan_nonformal;
        // $data['counts']              = $counts;

        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/profile', $data);
        $this->load->view('templates/footer');
    }

    /* ===================== UPDATE PROFILE (modal) ===================== */
    public function update_profile()
    {
        $user_id = (int) $this->session->userdata('id');
        if (!$user_id) show_error('Unauthorized', 401);

        // ==== Ambil user sekarang (buat banding email utama)
        $cur = $this->db->get_where('user', ['id' => $user_id])->row_array();

        // ==== Ambil input
        $instagram       = trim((string) $this->input->post('instagram', TRUE));
        $emails_csv      = trim((string) $this->input->post('email', TRUE)); // dari Tagify: "a@x.com, b@y.com"
        $linkedin_raw    = trim((string) $this->input->post('linkedin', TRUE));
        $domisili_negara = trim((string) $this->input->post('domisili_negara', TRUE));
        $domisili_kota   = trim((string) $this->input->post('domisili_kota', TRUE));

        // Sosmed tambahan
        $twitter   = trim((string) $this->input->post('twitter', TRUE));
        $github    = trim((string) $this->input->post('github', TRUE));
        $facebook  = trim((string) $this->input->post('facebook', TRUE));
        $youtube   = trim((string) $this->input->post('youtube', TRUE));
        $tiktok    = trim((string) $this->input->post('tiktok', TRUE));
        $website   = trim((string) $this->input->post('website', TRUE));
        $whatsapp  = trim((string) $this->input->post('whatsapp', TRUE));
        $telegram  = trim((string) $this->input->post('telegram', TRUE));
        $medium    = trim((string) $this->input->post('medium', TRUE));
        $behance   = trim((string) $this->input->post('behance', TRUE));
        $dribbble  = trim((string) $this->input->post('dribbble', TRUE));

        // ==== Upload foto/cover (tetap)
        $oldPhoto = $this->input->post('photo_profile_old', TRUE) ?: null;
        $oldCover = $this->input->post('photo_cover_old', TRUE) ?: null;

        $newPhotoRel = $this->saveAndFitUser('photo_profile', 'profile', 400, 400, 90, 2);
        $newCoverRel = $this->saveAndFitUser('photo_cover',  'cover',   1600, 400, 90, 3);

        if ($newPhotoRel && $oldPhoto && is_file(FCPATH.$oldPhoto)) @unlink(FCPATH.$oldPhoto);
        if ($newCoverRel && $oldCover && is_file(FCPATH.$oldCover)) @unlink(FCPATH.$oldCover);

        // ==== Helper normalisasi URL medsos
        $norm = function($key, $val) {
            $v = trim((string)$val);
            if ($v === '') return '';
            switch ($key) {
                case 'instagram':
                    $v = preg_replace('~^https?://(www\.)?instagram\.com/~i', '', ltrim($v, '@'));
                    return 'https://instagram.com/'.$v;
                case 'twitter':
                    $v = preg_replace('~^https?://(www\.)?(x|twitter)\.com/~i', '', ltrim($v, '@'));
                    return 'https://twitter.com/'.$v;
                case 'github':
                    $v = preg_replace('~^https?://(www\.)?github\.com/~i', '', $v);
                    return 'https://github.com/'.$v;
                case 'facebook':
                    $v = preg_replace('~^https?://(www\.)?facebook\.com/~i', '', $v);
                    return 'https://facebook.com/'.$v;
                case 'youtube':
                    if (preg_match('~^https?://~i', $v)) return $v;
                    return $v[0] === '@' ? 'https://www.youtube.com/'.$v : 'https://www.youtube.com/@'.$v;
                case 'tiktok':
                    $v = preg_replace('~^https?://(www\.)?tiktok\.com/@~i', '', ltrim($v, '@'));
                    return 'https://www.tiktok.com/@'.$v;
                case 'website':
                    return preg_match('~^https?://~i', $v) ? $v : 'https://'.$v;
                case 'telegram':
                    $v = preg_replace('~^https?://t\.me/~i', '', ltrim($v, '@'));
                    return 'https://t.me/'.$v;
                case 'medium':
                    $v = preg_replace('~^https?://(www\.)?medium\.com/@~i', '', ltrim($v, '@'));
                    return 'https://medium.com/@'.$v;
                case 'behance':
                    $v = preg_replace('~^https?://(www\.)?behance\.net/~i', '', $v);
                    return 'https://www.behance.net/'.$v;
                case 'dribbble':
                    $v = preg_replace('~^https?://(www\.)?dribbble\.com/~i', '', $v);
                    return 'https://dribbble.com/'.$v;
                case 'whatsapp':
                    if (preg_match('~^https?://(www\.)?wa\.me/~i', $v)) return $v;
                    $digits = preg_replace('~\D~', '', $v);
                    if ($digits === '') return '';
                    $num = (strpos($digits, '62') === 0) ? $digits : ('62'.ltrim($digits, '0'));
                    return 'https://wa.me/'.$num;
            }
            return $v;
        };

        // ==== Multi-email: validasi & tentukan email utama
        $emails = array_filter(array_map('trim', explode(',', $emails_csv)));
        $emails_valid = [];
        foreach ($emails as $em) {
            if (filter_var($em, FILTER_VALIDATE_EMAIL)) $emails_valid[] = strtolower($em);
        }
        if (empty($emails_valid)) {
            $this->session->set_flashdata('error', 'Minimal isi satu email yang valid (pisahkan dengan koma).');
            return redirect('user/profile');
        }
        $email_primary = $emails_valid[0];
        $emails_all    = implode(', ', $emails_valid);

        // ==== LinkedIn: normalisasi lalu validasi URL
        $linkedin = $linkedin_raw;
        if ($linkedin !== '') {
            $linkedin = preg_match('~^https?://~i', $linkedin) ? $linkedin : ('https://' . ltrim($linkedin, '/'));
            if (!filter_var($linkedin, FILTER_VALIDATE_URL)) {
                $this->session->set_flashdata('error', 'URL LinkedIn tidak valid.');
                return redirect('user/profile');
            }
        }

        // ==== Payload update
        $payloadUser = [
            'instagram'       => ltrim($instagram, '@'),
            'email'           => $email_primary,   // email utama
            'emails_all'      => $emails_all,      // simpan semua email (CSV)
            'linkedin'        => $linkedin,
            'domisili_negara' => $domisili_negara ?: null,
            'domisili_kota'   => $domisili_kota   ?: null,

            // medsos normalized URL
            'twitter'   => $norm('twitter',  $twitter),
            'github'    => $norm('github',   $github),
            'facebook'  => $norm('facebook', $facebook),
            'youtube'   => $norm('youtube',  $youtube),
            'tiktok'    => $norm('tiktok',   $tiktok),
            'website'   => $norm('website',  $website),
            'whatsapp'  => $norm('whatsapp', $whatsapp),
            'telegram'  => $norm('telegram', $telegram),
            'medium'    => $norm('medium',   $medium),
            'behance'   => $norm('behance',  $behance),
            'dribbble'  => $norm('dribbble', $dribbble),

            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        if ($newPhotoRel) $payloadUser['photo'] = $newPhotoRel;
        if ($newCoverRel) $payloadUser['cover'] = $newCoverRel;

        // ==== Jika email utama berubah → paksa re-verify
        if (!empty($cur) && !empty($cur['email']) && $email_primary !== $cur['email']) {
            $payloadUser['status'] = 'pending_email';
            $payloadUser['email_verified_at'] = null;
        }

        // ==== Update table user
        $this->db->where('id', $user_id)->update('user', $payloadUser);

        // ==== Domisili ke CV (tetap)
        $cv_row = $this->getByUser($user_id);
        if ($cv_row) {
            $this->db->where('id', (int)$cv_row['id'])->update('cv', [
                'domisili_negara' => $domisili_negara ?: null,
                'domisili_kota'   => $domisili_kota   ?: null,
                'updated_at'      => date('Y-m-d H:i:s')
            ]);
        }

        $this->session->set_flashdata('success', 'Profil berhasil diperbarui.');
        redirect('user/profile');
    }


    /**
     * Upload + resize cover-fit + center-crop ke target size (tanpa library CI, pakai GD)
     * @param string $field    nama input file
     * @param string $destDir  folder tujuan
     * @param int    $targetW  width target
     * @param int    $targetH  height target
     * @param int    $quality  JPEG quality 0-100
     * @param int    $maxMB    batas ukuran file (MB)
     * @return string|null     nama file tersimpan atau null kalau gagal/tidak ada file
     */
    private function saveAndFit($field, $destDir, $targetW, $targetH, $quality = 90, $maxMB = 3)
    {
        if (empty($_FILES[$field]['name'])) return null;

        // Validasi size & type
        $size = (int)($_FILES[$field]['size'] ?? 0);
        if ($size <= 0 || $size > ($maxMB * 1024 * 1024)) return null;

        $tmp  = $_FILES[$field]['tmp_name'];
        $info = @getimagesize($tmp);
        if (!$info) return null;
        list($w, $h, $type) = $info;

        switch ($type) {
            case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($tmp); $ext='jpg'; break;
            case IMAGETYPE_PNG:  $src = @imagecreatefrompng($tmp);  $ext='png'; break;
            default: return null;
        }
        if (!$src) return null;

        // Scale cover-fit: sisi pendek >= target
        $scale = max($targetW / $w, $targetH / $h);
        $newW  = (int)ceil($w * $scale);
        $newH  = (int)ceil($h * $scale);

        $resized = imagecreatetruecolor($newW, $newH);
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        // Center-crop ke target
        $x = (int)max(0, ($newW - $targetW) / 2);
        $y = (int)max(0, ($newH - $targetH) / 2);
        $canvas = imagecreatetruecolor($targetW, $targetH);
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
        }
        imagecopy($canvas, $resized, 0, 0, $x, $y, $targetW, $targetH);

        // Simpan
        if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
        $name = $field . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $path = rtrim($destDir, '/').'/'.$name;

        $ok = ($type === IMAGETYPE_PNG) ? imagepng($canvas, $path) : imagejpeg($canvas, $path, $quality);

        imagedestroy($src); imagedestroy($resized); imagedestroy($canvas);

        return $ok ? $name : null;
    }

    private function deleteFile($path)
    {
        if (is_file($path)) @unlink($path);
    }

    

    public function index()
    {
        $data['title'] = 'Beranda';
        $data['user']  = $this->db->get_where('user', [
            'email' => $this->session->userdata('email')
        ])->row_array();

        // pakai id dari user yang baru diambil (paling konsisten)
        $meId = (int)($data['user']['id'] ?? 0);

        $this->load->helper('cv'); // helper progress

        // Hitung progress + missing untuk user yang login
        list($cv_progress, $missing_sections) = cv_compute_progress($this->db, $meId);

        $data['cv_progress']      = $cv_progress;
        $data['missing_sections'] = $missing_sections;
        $data['show_cv_nudge']    = ($cv_progress < 100);

        // Search
        $q = trim($this->input->get('q', true) ?? '');
        $data['search'] = $q;

        // --- Subquery: latest CV per user ---
        // SELECT user_id, MAX(id) AS last_cv_id FROM cv GROUP BY user_id
        $latestCvSub = $this->db->select('user_id, MAX(id) AS last_cv_id', false)
                                ->from('cv')
                                ->group_by('user_id')
                                ->get_compiled_select();

        // Query people
        $this->db->select('DISTINCT u.id, u.name, u.email, u.photo, u.cover, u.last_login,
                c.posisi, c.perusahaan, c.domisili_kota, c.domisili_negara', false);
        $this->db->from('user u');

        // join ke subquery latest cv id per user
        $this->db->join("($latestCvSub) lc", 'lc.user_id = u.id', 'left', false);
        // join ke cv pakai id terakhir
        $this->db->join('cv c', 'c.id = lc.last_cv_id', 'left');

        $this->db->where('u.id !=', $meId);
        $this->db->where('u.is_active', 1);

        if ($q !== '') {
            $this->db->group_start()
                ->like('u.name', $q)
                ->or_like('c.posisi', $q)
                ->or_like('c.perusahaan', $q)
            ->group_end();
        }

        $this->db->order_by('u.last_login', 'DESC');

        // (opsional) pagination/limit
        // $perPage = 24;
        // $this->db->limit($perPage, (int)$this->input->get('offset'));

        $data['people'] = $this->db->get()->result_array();

        // render
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/index', $data);
        $this->load->view('templates/footer');
    }



    public function edit($id)
    {
        // $data['title'] = 'My Profile';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $data = [
            'name' => $this->input->post('name'),
            'whatsapp' => $this->input->post('whatsapp'),
            'alamat' => $this->input->post('alamat')
        ];

        $this->db->where('id', $id);
        $this->db->update('user', $data);

        redirect('user');
        
    }


    
    public function changePassword()
    {
        $data['title'] = 'Change Password';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]');
        $this->form_validation->set_rules('new_password2', 'Confirm New Password', 'required|trim|min_length[3]|matches[new_password1]');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/changepassword', $data);
            $this->load->view('templates/footer');
        } else {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password1');
            if (!password_verify($current_password, $data['user']['password'])) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong current password!</div>');
                redirect('user/changepassword');
            } else {
                if ($current_password == $new_password) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">New password cannot be the same as current password!</div>');
                    redirect('user/changepassword');
                } else {
                    // password sudah ok
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                    $this->db->set('password', $password_hash);
                    $this->db->where('email', $this->session->userdata('email'));
                    $this->db->update('user');

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password changed!</div>');
                    redirect('auth/logout');
                }
            }
        }
    }

    public function upload(){
        
        $data['title'] = 'Share Work';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->load->library('upload');
    
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size']      = '10240';
            $config['upload_path'] = './assets/img/work/';
    
            $this->upload->initialize($config);
    
            if ($this->upload->do_upload('image')) {
                // Image uploaded successfully
                $imageData = $this->upload->data();
    
                $originalName = $imageData['file_name']; // Original uploaded file name
                $newName = 'ZED_' . $originalName; // New file name with "ZED_" prefix
    
                $newPath = './assets/img/work/' . $newName;
                rename($imageData['full_path'], $newPath);
    
                $id = $this->input->post('id');
                $name = $this->input->post('name');
                $description = $this->input->post('description');
    
                $data = [
                    'image' => $newName,
                    'name'  => $name,
                    'desc'  => $description,
                    'id_user' => $id,
                    'date_created' => time() 
                ];
    
                $this->db->insert('work', $data);
                $this->session->set_flashdata('success_message', 'Image uploaded successfully.');
                redirect('user');
            } else {
                // Error uploading image
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error_message', $error);
            }
        }
    
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/upload', $data);
        $this->load->view('templates/footer');
    }

    public function deleteWork() {

        $image = $this->input->post('image');

        unlink(FCPATH . 'assets/img/work/' . $image);

        $this->db->where('image', $image);
        $this->db->delete('work');

        redirect('user');

    }

    public function form()
    {
        $data['title'] = 'Input CV';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $user_id = $this->session->userdata('id');
        $data['cv']                  = $this->Cv_model->getByUser($user_id) ?: [];
        $data['pendidikan_formal']   = $this->db->get_where('pendidikan_formal', ['cv_id' => $data['cv']['id'] ?? 0])->result_array();
        $data['pendidikan_nonformal']= $this->db->get_where('pendidikan_nonformal', ['cv_id' => $data['cv']['id'] ?? 0])->result_array();
        $data['pengalaman_kerja']    = $this->db->order_by('waktu_mulai','DESC')->get_where('pengalaman_kerja', ['cv_id' => $data['cv']['id'] ?? 0])->result_array();
        $data['project_relevan']     = $this->db->get_where('project_relevan', ['cv_id' => $data['cv']['id'] ?? 0])->result_array();
        $data['bahasa_list']         = $this->db->get_where('bahasa', ['cv_id' => $data['cv']['id'] ?? 0])->result_array(); // kalau ada tabel bahasa
        $data['sertifikasi_profesi'] = $this->db->get_where('sertifikasi_profesi', ['cv_id' => $data['cv']['id'] ?? 0])->result_array();
        $data['lampiran']            = $this->db->get_where('lampiran_cv', ['cv_id' => $data['cv']['id'] ?? 0])->row_array();


        $this->load->view('templates/header', $data);
        // $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/form', $data);
        $this->load->view('templates/footer');
    }

    public function save()
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required');
        $this->form_validation->set_rules('posisi', 'Posisi', 'required');

        if ($this->form_validation->run() == false) {
            $this->index();
        } else {
            $this->Cv_model->saveOrUpdate();
            $this->session->set_flashdata('success', 'Data CV berhasil disimpan.');
            redirect('user');
        }
    }

    public function export_apbn()
    {
        $user_id = $this->session->userdata('id');

        $cv = $this->Cv_model->getCv($user_id);
        $cv_id = $cv['id'];

        $data['cv'] = $cv;
        $data['pengalaman'] = $this->Cv_model->getPengalaman($cv_id);

        $template = new TemplateProcessor('assets/template/APBN.docx');

        // Isi nilai data tunggal
        $template->setValue('nama', $cv['nama']);
        $template->setValue('posisi', $cv['posisi']);
        $template->setValue('perusahaan', $cv['perusahaan']);
        $template->setValue('kewarganegaraan', $cv['kewarganegaraan']);
        $template->setValue('tempat_lahir', $cv['tempat_lahir']);
        $template->setValue('tanggal_lahir', $cv['tanggal_lahir']);
        $template->setValue('status_kepegawaian', $cv['status_kepegawaian']);
        $template->setValue('pernah_di_wb', $cv['pernah_di_wb']);

        // Clone blok pengalaman
        $template->cloneBlock('PENGALAMAN_LOOP', count($data['pengalaman']), true, true);

        foreach ($data['pengalaman'] as $i => $row) {
            $index = $i + 1;
            $template->setValue("pengalaman_tahun#{$index}", $row['tahun_pengalaman']);
            $template->setValue("pengalaman_nama#{$index}", $row['nama_kegiatan']);
            $template->setValue("pengalaman_lokasi#{$index}", $row['lokasi']);
            $template->setValue("pengalaman_pemberi#{$index}", $row['pemberi_pekerjaan']);
            $template->setValue("pengalaman_perusahaan#{$index}", $row['perusahaan']);
            $template->setValue("pengalaman_uraian#{$index}", $row['uraian_tugas']);
            $template->setValue("pengalaman_waktu#{$index}", $row['waktu']);
            $template->setValue("pengalaman_posisi#{$index}", $row['posisi_pengalaman']);
            $template->setValue("pengalaman_status#{$index}", $row['status_pegawai']);
            $template->setValue("pengalaman_referensi#{$index}", $row['surat_referensi']);
        }

        $filename = 'CV_APBN_' . str_replace(' ', '_', $cv['nama']) . '.docx';

        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Cache-Control: max-age=0');
        $template->saveAs('php://output');
    }



    public function export_pdf_apbn()
    {
        $this->load->model('Cv_model');

        $user_id = $this->session->userdata('id');
        $data = $this->Cv_model->getCvData($user_id);

        // 1. Load HTML (Pastikan 'Halaman {PAGENO}...' SUDAH DIHAPUS dari file view ini)
        $html = $this->load->view('user/export_apbn', $data, true);

        $options = new Options();
        $options->set('defaultFont', 'verdana');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        
        // 2. Render PDF (PENTING: JANGAN ubah baris ini)
        $dompdf->render();

        // 3. === MULAI KODE TAMBAHAN UNTUK NOMOR HALAMAN ===
        // ------------------------------------------------
        
        // Ambil canvas (halaman) PDF
        $canvas = $dompdf->get_canvas();
        
        // Dapatkan FONT OBJECT yang akan kita gunakan
        $font = $dompdf->getFontMetrics()->get_font("verdana", "normal");
        
        // Tentukan FONT SIZE yang akan kita gunakan
        $font_size = 8; // 8pt
        
        // Skrip untuk menggambar nomor halaman di SETIAP halaman
        // ---- PERBAIKAN ADA DI SINI ----
        $canvas->page_script(
            
            // 1. Tanda tangan fungsi (signature) harus menerima 4 argumen ini
            function ($pageNo, $totalPages, $canvas, $fontMetrics) use ($font, $font_size) {
                // 2. Kita 'use' $font (objek font) dan $font_size (angka) dari luar
                
                // Teks yang akan ditampilkan
                $text = "Halaman " . $pageNo . " dari " . $totalPages;
                
                // Hitung lebar teks agar bisa rata kanan
                // Sekarang $font adalah FONT OBJECT yang benar (dari 'use')
                $text_width = $canvas->get_text_width($text, $font, $font_size);
                
                // Dapatkan dimensi halaman
                $page_width = $canvas->get_width();
                $page_height = $canvas->get_height();
                
                // --- Tentukan Posisi ---
                $x = $page_width - $text_width - 28.34; // 1cm (28.34pt) dari tepi kanan
                $y = $page_height - 37.17; // Posisi Y dari bawah (sudah dihitung)
                
                // Gambar teks di canvas
                // Sekarang $font adalah FONT OBJECT yang benar (dari 'use')
                $canvas->text($x, $y, $text, $font, $font_size, [0, 0, 0]); // [0,0,0] = hitam
            }
            // 3. Kita tidak perlu passing $font sebagai argumen di sini lagi
        );
        
        // ------------------------------------------------
        // === SELESAI KODE TAMBAHAN ===
        
        // 4. Tampilkan ke browser
        $dompdf->stream('CV_APBN.pdf', ['Attachment' => false]);
    }

    public function export_pdf_wb($lang = 'en') // 'en' atau 'id'
    {
        $this->load->model('Cv_model');
        $this->load->library('Translator_free'); 

        $user_id = $this->session->userdata('id');
        $data    = $this->Cv_model->getCvData($user_id); 

        // --- AUTO TRANSLATE (gratis) ---
        if ($lang === 'en') {
            // 1) Field-level di CV
            if (!empty($data['cv']) && is_array($data['cv'])) {
                $fieldsCv = ['posisi','perusahaan','detailed_tasks','country_work','country_experience', 'employer', 'employment_position', 'employment_desc'];
                foreach ($fieldsCv as $f) {
                    if (!empty($data['cv'][$f])) {
                        $data['cv'][$f] = $this->translator_free->translate($data['cv'][$f], 'en', 'auto');
                    }
                }
            }

            // 2) Pendidikan formal
            if (!empty($data['pendidikan_formal'])) {
                foreach ($data['pendidikan_formal'] as &$p) {
                    foreach (['tingkat','jurusan','institusi','gelar'] as $f) {
                        if (!empty($p[$f])) {
                            $p[$f] = $this->translator_free->translate($p[$f], 'en', 'auto');
                        }
                    }
                }
                unset($p);
            }

            // 2b) Other Relevant Training (pendidikan_nonformal)
            if (!empty($data['pendidikan_nonformal'])) {
                foreach ($data['pendidikan_nonformal'] as &$t) {
                    foreach (['nama_pelatihan','penyelenggara'] as $f) {
                        if (!empty($t[$f])) {
                            $t[$f] = $this->translator_free->translate($t[$f], 'en', 'auto');
                        }
                    }
                    // Tahun & file nggak perlu diterjemahin
                }
                unset($t);
            }

            if ($lang === 'en' && !empty($data['country_experience'])) {
                foreach ($data['country_experience'] as &$cn) {
                    $cn = $this->translator_free->translate($cn, 'en', 'auto');
                }
                unset($cn);
            }

            // 3) Pengalaman
            if (!empty($data['pengalaman'])) {
                foreach ($data['pengalaman'] as &$e) {
                    foreach (['nama_kegiatan','lokasi','waktu_mulai', 'waktu_akhir', 'negara', 'pemberi_pekerjaan','posisi','uraian_tugas','uraian_proyek','durasi'] as $f) {
                        if (!empty($e[$f])) {
                            $e[$f] = $this->translator_free->translate($e[$f], 'en', 'auto');
                        }
                    }
                }
                unset($e);
            }

            // 4) Sertifikasi
            if (!empty($data['sertifikasi'])) {
                foreach ($data['sertifikasi'] as &$s) {
                    foreach (['nama','penerbit'] as $f) {
                        if (!empty($s[$f])) {
                            $s[$f] = $this->translator_free->translate($s[$f], 'en', 'auto');
                        }
                    }
                }
                unset($s);
            }

            // di export_pdf_wb(), setelah blok translate lain
            if ($lang === 'en' && !empty($data['employment_record'])) {
                foreach ($data['employment_record'] as &$er) {
                    if (!empty($er['employer'])) {
                        $er['employer'] = $this->translator_free->translate($er['employer'], 'en', 'auto');
                    }
                    if (!empty($er['positions'])) {
                        foreach ($er['positions'] as &$p) {
                            $p = $this->translator_free->translate($p, 'en', 'auto');
                        }
                        unset($p);
                    }
                }
                unset($er);
            }

            $data['__lang'] = 'en';
        } else {
            $data['__lang'] = 'id';
        }

        // --- Render view ---
        // 1. Load view yang sudah kita edit (export_wb.php)
        $html = $this->load->view('user/export_wb', $data, true);

        // --- Dompdf setup ---
        $options = new Options();
        
        // PERUBAHAN DI SINI: Samakan font dengan APBN
        $options->set('defaultFont', 'verdana'); 
        
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        
        // 2. Render PDF
        $dompdf->render();


        // 3. === BLOK KODE NOMOR HALAMAN YANG DIUBAH ===
        // ------------------------------------------------
        $canvas = $dompdf->get_canvas();
        
        // Pastikan font "verdana" (sesuai defaultFont di atas)
        $font = $dompdf->getFontMetrics()->get_font("verdana", "normal");
        $font_size = 8; // 8pt

        $canvas->page_script(
            
            function ($pageNo, $totalPages, $canvas, $fontMetrics) use ($font, $font_size, $lang) {
                
                // Cek bahasa dan atur teks yang sesuai
                if ($lang === 'en') {
                    $text = "Page " . $pageNo . " of " . $totalPages;
                } else {
                    $text = "Halaman " . $pageNo . " dari " . $totalPages;
                }
                
                $text_width = $canvas->get_text_width($text, $font, $font_size);
                
                $page_width = $canvas->get_width();
                $page_height = $canvas->get_height();
                
                // Posisi X (1cm dari kanan, disesuaikan dengan lebar teks)
                $x = $page_width - $text_width - 28.34; 
                
                // Posisi Y (dihitung dari bawah agar pas di atas footer)
                $y = $page_height - 37.17; 
                
                $canvas->text($x, $y, $text, $font, $font_size, [0, 0, 0]); // [0,0,0] = hitam
            }
        );
        // ------------------------------------------------
        // === SELESAI BLOK KODE ===


        // 4. Tampilkan PDF ke browser
        $dompdf->stream('CV_WB_'.$lang.'.pdf', ['Attachment' => false]);
    }

    /* ===================== EMPLOYMENT (current box) ===================== */
    public function employment_save()
    {
        $cv_id = $this->getCvIdOrCreate();
        $payload = [
            'employment_from'     => $this->input->post('employment_from', true),
            'employment_to'       => $this->input->post('employment_to') ?: null,
            'employer'            => $this->input->post('employer', true),
            'employment_position' => $this->input->post('employment_position', true),
            'employment_desc'     => $this->input->post('employment_desc', true),
            'domisili_negara'     => $this->input->post('domisili_negara', true) ?: null,
            'domisili_kota'       => $this->input->post('domisili_kota', true) ?: null,
            'updated_at'          => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $cv_id)->update('cv', $payload);

        $this->session->set_flashdata('success', 'Pekerjaan saat ini disimpan.');
        redirect('user/profile#employment');
    }

    /* ===================== PENGALAMAN ===================== */
    public function experience_save()
    {
        $cv_id = $this->getCvIdOrCreate();
        $id    = (int)$this->input->post('id');

        $mulai  = $this->input->post('waktu_mulai');
        $akhir  = $this->input->post('waktu_akhir');
        $durasi = trim($this->input->post('durasi') ?? '');
        if ($durasi === '') $durasi = $this->computeDurationIndo($mulai, $akhir);

        $oldRef = $this->input->post('referensi_file_old', true) ?: null;
        $up     = $this->handleUploadUser('referensi_file', 'referensi', 'pdf|jpg|jpeg|png', 5, $oldRef);
        if (!$up['ok']) {
            $this->session->set_flashdata('error', 'Upload referensi gagal: '.$up['error']);
            return redirect('user/profile#pengalaman');
        }

                // Ambil nilai toggle (default 0 jika unchecked)
        $isVisible = (int)$this->input->post('is_visible');

        $row = [
            'cv_id'              => $cv_id,
            'nama_kegiatan'      => $this->input->post('nama_kegiatan', true),
            'posisi'             => $this->input->post('posisi', true),
            'pemberi_pekerjaan'  => $this->input->post('pemberi_pekerjaan', true),
            'pelaksana_proyek'   => $this->input->post('pelaksana_proyek', true),
            'lokasi'             => $this->input->post('lokasi', true),
            'negara'             => $this->input->post('negara', true),
            'waktu_mulai'        => $mulai ?: null,
            'waktu_akhir'        => $akhir ?: null,
            'durasi'             => $durasi,
            'uraian_proyek'      => $this->input->post('uraian_proyek', true),
            'uraian_tugas'       => $this->input->post('uraian_tugas', true),
            'referensi_file'     => $up['path'], // rel path
            'is_visible'         => $isVisible,  // ✅ tambahkan ini
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->db->where(['id'=>$id,'cv_id'=>$cv_id])->update('pengalaman_kerja',$row);
            $msg = 'Pengalaman diperbarui.';
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('pengalaman_kerja',$row);
            $msg = 'Pengalaman ditambahkan.';
        }
        $this->session->set_flashdata('success', $msg);
        redirect('user/profile#pengalaman');
    }

    

    /* ===================== PENDIDIKAN ===================== */
    /* ===================== PENDIDIKAN ===================== */
    public function education_save()
    {
        $cv_id = $this->getCvIdOrCreate();
        $id    = (int)$this->input->post('id');

        $oldIjazah = $this->input->post('ijazah_file_old', true) ?: null;

        $up = $this->handleUploadUser('ijazah_file', 'ijazah', 'pdf|jpg|jpeg|png', 5, $oldIjazah);
        if (!$up['ok']) {
            $this->session->set_flashdata('error', 'Upload ijazah gagal: '.$up['error']);
            return redirect('user/profile#pendidikan');
        }

        // ========================================================
        // 👇 PERBAIKAN: Tambahkan baris ini untuk mengambil nilai
        //    'is_visible' dari form modal Anda.
        // ========================================================
        $isVisible = (int)$this->input->post('is_visible');

        $row = [
            'cv_id'       => $cv_id,
            'institusi'   => $this->input->post('institusi', true),
            'tingkat'     => $this->input->post('tingkat', true),
            'jurusan'     => $this->input->post('jurusan', true),
            'tahun_lulus' => $this->input->post('tahun_lulus', true),
            'ijazah_file' => $up['path'],
            'is_visible'  => $isVisible, // <-- Sekarang variabel ini sudah aman
        ];

        if ($id) {
            $this->db->where(['id'=>$id,'cv_id'=>$cv_id])->update('pendidikan_formal',$row);
            $msg = 'Pendidikan diperbarui.';
        } else {
            $this->db->insert('pendidikan_formal',$row);
            $msg = 'Pendidikan ditambahkan.';
        }
        $this->session->set_flashdata('success', $msg);
        redirect('user/profile#pendidikan');
    }

    /* ===================== SERTIFIKASI ===================== */
    // public function cert_save()
    // {
    //     $cv_id = $this->getCvIdOrCreate();
    //     $id    = (int)$this->input->post('id');

    //     // Ambil nilai toggle (default 0 jika unchecked)
    //     $isVisible = (int)$this->input->post('is_visible');

    //     $row = [
    //         'cv_id'      => $cv_id,
    //         'nama'       => $this->input->post('nama', true),
    //         'penerbit'   => $this->input->post('penerbit', true),
    //         'tahun'      => $this->input->post('tahun', true),
    //         'is_visible' => $isVisible, // ← Tambahkan ini
    //     ];

    //     if ($id) {
    //         $this->db->where(['id'=>$id,'cv_id'=>$cv_id])->update('sertifikasi_profesi',$row);
    //         $msg = 'Sertifikasi diperbarui.';
    //     } else {
    //         $this->db->insert('sertifikasi_profesi',$row);
    //         $msg = 'Sertifikasi ditambahkan.';
    //     }
    //     $this->session->set_flashdata('success', $msg);
    //     redirect('user/profile#sertifikasi');
    // }

    public function cert_save()
    {
        $cv_id = $this->getCvIdOrCreate(); 
        if (!$cv_id) {
            $this->session->set_flashdata('error', 'CV tidak ditemukan.');
            redirect('user/profile');
            return;
        }
        
        $id        = (int)$this->input->post('id');
        $file_lama = $this->input->post('file_lama'); // Ambil path/nama file lama
        
        // Ambil nilai toggle
        $isVisible = (int)$this->input->post('is_visible');

        $row = [
            'cv_id'      => $cv_id,
            'nama'       => $this->input->post('nama', true),
            'penerbit'   => $this->input->post('penerbit', true),
            'tahun'      => $this->input->post('tahun', true),
            'is_visible' => $isVisible,
        ];

        // --- LOGIKA UPLOAD FILE (Disinkronkan dengan path CV) ---
        $user_id = $this->session->userdata('id'); // Menggunakan 'id' untuk konsistensi
        $user_folder = 'user_' . $user_id;

        // Tentukan ABSOLUTE path untuk upload
        $upload_path = FCPATH . 'uploads/cv/' . $user_folder . '/'; 
        // Tentukan RELATIVE path untuk disimpan di DB
        $path_prefix = 'uploads/cv/' . $user_folder . '/'; 

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, TRUE);
        }
        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size']      = 2048; // 2MB
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config); // Re-initialize diperlukan jika library sudah pernah dimuat

        if (!empty($_FILES['file_sertifikat']['name'])) {
            if ($this->upload->do_upload('file_sertifikat')) {
                $upload_data = $this->upload->data();
                // SIMPAN PATH RELATIF PENUH KE DB
                $row['file_sertifikat'] = $path_prefix . $upload_data['file_name'];
                
                // Hapus file lama jika ada (menggunakan path penuh yang disimpan di DB)
                if ($id && !empty($file_lama) && file_exists(FCPATH . $file_lama)) {
                    unlink(FCPATH . $file_lama);
                }
            } else {
                $error = $this->upload->display_errors('', '');
                $this->session->set_flashdata('error', 'Upload Sertifikasi gagal: ' . $error);
                redirect('user/profile#sertifikasi'); // Arahkan ke section yang benar
                return;
            }
        } else {
            if ($id) {
                // Pertahankan file lama (yang harusnya sudah path penuh)
                $row['file_sertifikat'] = $file_lama;
            }
        }
        // --- END LOGIKA UPLOAD FILE ---

        if ($id) {
            $this->db->where(['id' => $id, 'cv_id' => $cv_id])->update('sertifikasi_profesi', $row);
            $msg = 'Sertifikasi diperbarui.';
        } else {
            $this->db->insert('sertifikasi_profesi', $row);
            $msg = 'Sertifikasi ditambahkan.';
        }
        
        $this->session->set_flashdata('success', $msg);
        redirect('user/profile#sertifikasi'); 
    }

    
    
    public function nonformal_save()
    {
        $cv_id = $this->getCvIdOrCreate(); 
        if (!$cv_id) {
            $this->session->set_flashdata('error', 'CV tidak ditemukan.');
            redirect('user/profile');
            return;
        }

        $id        = (int)$this->input->post('id');
        $file_lama = $this->input->post('file_lama'); // Ambil path/nama file lama
        
        $isVisible = (int)$this->input->post('is_visible');

        $row = [
            'cv_id'          => $cv_id,
            'nama_pelatihan' => $this->input->post('nama_pelatihan', true),
            'penyelenggara'  => $this->input->post('penyelenggara', true),
            'tahun'          => $this->input->post('tahun', true),
            'is_visible'     => $isVisible,
        ];

        // --- LOGIKA UPLOAD FILE (Disinkronkan dengan path CV) ---
        $user_id = $this->session->userdata('id');
        $user_folder = 'user_' . $user_id;
        
        // Tentukan ABSOLUTE path untuk upload
        $upload_path = FCPATH . 'uploads/cv/' . $user_folder . '/'; // <--- Path Konsisten
        // Tentukan RELATIVE path untuk disimpan di DB
        $path_prefix = 'uploads/cv/' . $user_folder . '/'; 

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, TRUE);
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size']      = 2048; // 2MB
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config); // Re-initialize

        if (!empty($_FILES['sertifikat_file']['name'])) {
            if ($this->upload->do_upload('sertifikat_file')) {
                $upload_data = $this->upload->data();
                // SIMPAN PATH RELATIF PENUH KE DB
                $row['sertifikat_file'] = $path_prefix . $upload_data['file_name'];
                
                // Hapus file lama jika ada (menggunakan path penuh yang disimpan di DB)
                if ($id && !empty($file_lama) && file_exists(FCPATH . $file_lama)) {
                    unlink(FCPATH . $file_lama);
                }
            } else {
                $error = $this->upload->display_errors('', '');
                $this->session->set_flashdata('error', 'Upload Pelatihan gagal: ' . $error);
                redirect('user/profile#pelatihan');
                return;
            }
        } else {
            if ($id) {
                $row['sertifikat_file'] = $file_lama;
            }
        }
        // --- END LOGIKA UPLOAD FILE ---

        if ($id) {
            $this->db->where(['id' => $id, 'cv_id' => $cv_id])->update('pendidikan_nonformal', $row);
            $msg = 'Pelatihan diperbarui.';
        } else {
            $this->db->insert('pendidikan_nonformal', $row);
            $msg = 'Pelatihan ditambahkan.';
        }
        
        $this->session->set_flashdata('success', $msg);
        redirect('user/profile#pelatihan'); 
    }

    /* ===================== BAHASA ===================== */
    public function lang_save()
    {
        $cv_id = $this->getCvIdOrCreate();
        $id    = (int)$this->input->post('id');
        
        // Ambil nilai is_visible. Karena di profile.php sudah diatur hidden input, 
        // kita bisa langsung ambil nilainya, defaultnya akan 0 jika checkbox tidak dicentang.
        $isVisible = (int)$this->input->post('is_visible'); // Akan jadi 1 atau 0

        $row = [
            'cv_id'    => $cv_id,
            'bahasa'   => $this->input->post('bahasa', true),
            'speaking' => $this->input->post('speaking', true),
            'reading'  => $this->input->post('reading', true),
            'writing'  => $this->input->post('writing', true),
            'is_visible' => $isVisible, // <-- TAMBAHKAN BARIS INI
        ];

        if ($id) {
            $this->db->where(['id'=>$id,'cv_id'=>$cv_id])->update('bahasa',$row);
            $msg = 'Bahasa diperbarui.';
        } else {
            $this->db->insert('bahasa',$row);
            $msg = 'Bahasa ditambahkan.';
        }
        $this->session->set_flashdata('success', $msg);
        redirect('user/profile#bahasa');
    }

    // /* ===================== BAHASA ===================== */
    // public function lang_save()
    // {
    //     $cv_id = $this->getCvIdOrCreate();
    //     $id    = (int)$this->input->post('id');

    //     $row = [
    //         'cv_id'    => $cv_id,
    //         'bahasa'   => $this->input->post('bahasa', true),
    //         'speaking' => $this->input->post('speaking', true),
    //         'reading'  => $this->input->post('reading', true),
    //         'writing'  => $this->input->post('writing', true),
    //     ];

    //     if ($id) {
    //         $this->db->where(['id'=>$id,'cv_id'=>$cv_id])->update('bahasa',$row);
    //         $msg = 'Bahasa diperbarui.';
    //     } else {
    //         $this->db->insert('bahasa',$row);
    //         $msg = 'Bahasa ditambahkan.';
    //     }
    //     $this->session->set_flashdata('success', $msg);
    //     redirect('user/profile#bahasa');
    // }

    
        /* ===================== LAMPIRAN CV (per item) ===================== */

    /**
     * Mengganti/upload 1 file lampiran dari modal di profile page
     * Ini menangani modal 'Ganti/Upload'
     */
    public function lampiran_item_save()
    {
        $cv_id = $this->getCvIdOrCreate();
        if (!$cv_id) {
            $this->session->set_flashdata('error', 'CV tidak ditemukan.');
            redirect('user/profile#lampiran');
            return;
        }

        $field_name = $this->input->post('field_name', true);
        $file_lama = $this->input->post('file_lama', true); // Path penuh lama
        $is_visible = (int)$this->input->post('is_visible'); // Menerima 0 atau 1

        $allowed_fields = ['ktp_file', 'npwp_file', 'bukti_pajak', 'foto', 'lainnya'];
        if (!$field_name || !in_array($field_name, $allowed_fields)) {
            $this->session->set_flashdata('error', 'Field lampiran tidak valid.');
            redirect('user/profile#lampiran');
            return;
        }
        
        $visible_field_name = 'is_visible_' . $field_name;

        // --- Logika Upload File ---
        $user_id = $this->session->userdata('id');
        $user_folder = 'user_' . $user_id;

        $upload_path = FCPATH . 'uploads/cv/' . $user_folder . '/';
        $path_prefix = 'uploads/cv/' . $user_folder . '/'; 

        if (!is_dir($upload_path)) {
            @mkdir($upload_path, 0777, TRUE);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $new_file_name = null;

        if (!empty($_FILES['file_lampiran']['name'])) {
            if ($this->upload->do_upload('file_lampiran')) {
                $upload_data = $this->upload->data();
                $new_file_name = $path_prefix . $upload_data['file_name']; 

                if (!empty($file_lama) && file_exists(FCPATH . $file_lama)) {
                    @unlink(FCPATH . $file_lama);
                }
            } else {
                $error = $this->upload->display_errors('', '');
                $this->session->set_flashdata('error', 'Upload ' . $field_name . ' gagal: ' . $error);
                redirect('user/profile#lampiran');
                return;
            }
        } else {
            $new_file_name = $file_lama;
            if (empty($new_file_name)) {
                $this->session->set_flashdata('error', 'Pilih file yang akan di-upload.');
                redirect('user/profile#lampiran');
                return;
            }
        }

        // --- Update Database ---
        $lampiran_row = $this->db->get_where('lampiran_cv', ['cv_id' => $cv_id])->row();

        $payload = [
            $field_name => $new_file_name,
            $visible_field_name => $is_visible // NILAI 0 atau 1 DITERAPKAN DI SINI
        ];

        if ($lampiran_row) {
            $this->db->where('cv_id', $cv_id)->update('lampiran_cv', $payload);
        } else {
            $payload['cv_id'] = $cv_id;
            $this->db->insert('lampiran_cv', $payload);
        }

        $this->session->set_flashdata('success', 'Lampiran ' . $field_name . ' diperbarui.');
        redirect('user/profile#lampiran');
    }

    /* ====================== HELPERS ====================== */
    private function getByUser($user_id)
    {
        return $this->db->get_where('cv', ['user_id' => (int)$user_id])->row_array();
    }

    private function getCvIdOrCreate()
    {
        $user_id = (int)$this->session->userdata('id');
        if (!$user_id) show_error('Unauthorized', 401);

        $row = $this->getByUser($user_id);
        if ($row) return (int)$row['id'];

        $this->db->insert('cv', [
            'user_id'    => $user_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return (int)$this->db->insert_id();
    }

    private function computeDurationIndo($from, $to)
    {
        if (empty($from)) return '';
        try {
            $d1 = new DateTime($from);
            $d2 = new DateTime($to ?: 'today');
            $diff = $d1->diff($d2);
            $parts = [];
            if ($diff->y > 0) $parts[] = $diff->y.' th';
            if ($diff->m > 0) $parts[] = $diff->m.' bln';
            if ($diff->y == 0 && $diff->m == 0) $parts[] = max(1,$diff->d).' hr';
            return implode(' ', $parts);
        } catch (Exception $e) { return ''; }
    }

    /* ===== Upload helpers (user folder) ===== */
    private function userUploadBaseRel(int $user_id): string {
        return 'uploads/cv/user_' . $user_id . '/';
    }
    private function ensureDirRel(string $rel): string {
        $rel = rtrim($rel, '/') . '/';
        $abs = FCPATH . $rel;
        if (!is_dir($abs)) @mkdir($abs, 0775, true);
        return $rel;
    }
    private function relJoin(string $a, string $b): string {
        return rtrim($a, '/').'/'.ltrim($b, '/');
    }
    private function handleUploadUser(string $field, string $subdir, string $allowed = 'pdf|jpg|jpeg|png', int $maxMB = 5, ?string $oldRelPath = null): array
    {
        $user_id = (int)$this->session->userdata('id');
        if (empty($_FILES[$field]['name'])) {
            return ['ok'=>true, 'path'=>$oldRelPath, 'error'=>null, 'replaced'=>false];
        }

        $baseRel   = $this->userUploadBaseRel($user_id);
        $targetRel = $this->ensureDirRel($this->relJoin($baseRel, $subdir));
        $config = [
            'upload_path'   => FCPATH . $targetRel,
            'allowed_types' => $allowed,
            'max_size'      => $maxMB * 1024,
            'encrypt_name'  => true,
            'remove_spaces' => true,
        ];
        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload($field)) {
            return ['ok'=>false, 'path'=>null, 'error'=>$this->upload->display_errors('', ''), 'replaced'=>false];
        }
        $data = $this->upload->data();
        $newRel = $this->relJoin($targetRel, $data['file_name']);

        if ($oldRelPath) {
            $oldRelPath = ltrim($oldRelPath, '/');
            $isInside = substr($oldRelPath, 0, strlen($baseRel)) === $baseRel;
            if ($isInside && $oldRelPath !== $newRel) {
                $absOld = FCPATH . $oldRelPath;
                if (is_file($absOld)) @unlink($absOld);
            }
        }
        return ['ok'=>true, 'path'=>$newRel, 'error'=>null, 'replaced'=>true];
    }
    private function saveAndFitUser(string $field, string $subdir, int $targetW, int $targetH, int $quality = 90, int $maxMB = 3): ?string
    {
        if (empty($_FILES[$field]['name'])) return null;
        $size = (int)($_FILES[$field]['size'] ?? 0);
        if ($size <= 0 || $size > ($maxMB * 1024 * 1024)) return null;

        $tmp  = $_FILES[$field]['tmp_name'];
        $info = @getimagesize($tmp);
        if (!$info) return null;
        [$w, $h, $type] = $info;

        switch ($type) {
            case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($tmp); $ext='jpg'; break;
            case IMAGETYPE_PNG:  $src = @imagecreatefrompng($tmp);  $ext='png'; break;
            default: return null;
        }
        if (!$src) return null;

        $scale = max($targetW / $w, $targetH / $h);
        $newW  = (int)ceil($w * $scale);
        $newH  = (int)ceil($h * $scale);

        $resized = imagecreatetruecolor($newW, $newH);
        if ($type === IMAGETYPE_PNG) { imagealphablending($resized,false); imagesavealpha($resized,true); }
        imagecopyresampled($resized, $src, 0,0,0,0, $newW,$newH, $w,$h);

        $x = (int)max(0, ($newW - $targetW) / 2);
        $y = (int)max(0, ($newH - $targetH) / 2);
        $canvas = imagecreatetruecolor($targetW, $targetH);
        if ($type === IMAGETYPE_PNG) { imagealphablending($canvas,false); imagesavealpha($canvas,true); }
        imagecopy($canvas, $resized, 0,0, $x,$y, $targetW,$targetH);

        $user_id  = (int)$this->session->userdata('id');
        $baseRel  = $this->userUploadBaseRel($user_id);
        $targetRel= $this->ensureDirRel($this->relJoin($baseRel, $subdir));
        $name     = $field.'_'.date('YmdHis').'_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $absPath  = FCPATH . $targetRel . $name;

        $ok = ($type === IMAGETYPE_PNG) ? imagepng($canvas, $absPath) : imagejpeg($canvas, $absPath, $quality);
        imagedestroy($src); imagedestroy($resized); imagedestroy($canvas);

        return $ok ? ($targetRel . $name) : null;
    }

    /* ===================== DELETE HELPERS (private) ===================== */
    private function file_delete_safe($relPath) {
    // hapus file relatif dari FCPATH, ignore kalau ga ada
    if (!$relPath) return;
    $abs = FCPATH . ltrim($relPath, '/');
    if (is_file($abs)) @unlink($abs);
    }

    private function owned_row_or_404($table, $id, $cv_id) {
    $row = $this->db->get_where($table, ['id' => (int)$id])->row_array();
    if (!$row || (int)$row['cv_id'] !== (int)$cv_id) {
        show_404(); // biar ga bisa delete milik orang lain
        exit;
    }
    return $row;
    }

    /* ===================== PENGALAMAN: DELETE ===================== */
    public function experience_delete($id) {
    $cv_id = $this->getCvIdOrCreate();

    $row = $this->owned_row_or_404('pengalaman_kerja', $id, $cv_id);

    // hapus file referensi kalau ada
    if (!empty($row['referensi_file'])) {
        $this->file_delete_safe($row['referensi_file']);
    }

    $this->db->delete('pengalaman_kerja', ['id' => (int)$id, 'cv_id' => $cv_id]);
    $this->session->set_flashdata('success', 'Pengalaman dihapus.');
    redirect('user/profile');
    }

    /* ===================== PENDIDIKAN FORMAL: DELETE ===================== */
    public function education_delete($id) {
    $cv_id = $this->getCvIdOrCreate();

    $row = $this->owned_row_or_404('pendidikan_formal', $id, $cv_id);

    // hapus file ijazah kalau ada
    if (!empty($row['ijazah_file'])) {
        $this->file_delete_safe($row['ijazah_file']);
    }

    $this->db->delete('pendidikan_formal', ['id' => (int)$id, 'cv_id' => $cv_id]);
    $this->session->set_flashdata('success', 'Pendidikan dihapus.');
    redirect('user/profile');
    }

    /* ===================== SERTIFIKASI PROFESI: DELETE ===================== */
    public function cert_delete($id) {
    $cv_id = $this->getCvIdOrCreate();

    $row = $this->owned_row_or_404('sertifikasi_profesi', $id, $cv_id);

    $this->db->delete('sertifikasi_profesi', ['id' => (int)$id, 'cv_id' => $cv_id]);
    $this->session->set_flashdata('success', 'Sertifikasi dihapus.');
    redirect('user/profile');
    }

    /* ===================== PENDIDIKAN NON FORMAL: DELETE ===================== */
    public function nonformal_delete($id)
    {
        $cv_id = $this->getCvIdOrCreate(); // Panggil fungsi Anda
        if (!$cv_id) {
             $this->session->set_flashdata('error', 'CV tidak ditemukan.');
             redirect('user/profile');
             return;
        }
        
        $id = (int)$id;

        // 1. Ambil data pelatihan (Gunakan fungsi helper Anda)
        $item = $this->owned_row_or_404('pendidikan_nonformal', $id, $cv_id);

        // 2. Hapus file jika ada
        $file_path = FCPATH . 'uploads/sertifikat/' . $item->sertifikat_file;
        if (!empty($item->sertifikat_file) && file_exists($file_path)) {
            unlink($file_path);
        }

        // 3. Hapus data dari database
        $this->db->where('id', $id)->delete('pendidikan_nonformal');
        $this->session->set_flashdata('success', 'Pelatihan dihapus.');

        redirect('user/profile');
    }

    /* ===================== BAHASA: DELETE ===================== */
    public function lang_delete($id) {
    $cv_id = $this->getCvIdOrCreate();

    $row = $this->owned_row_or_404('bahasa', $id, $cv_id);

    $this->db->delete('bahasa', ['id' => (int)$id, 'cv_id' => $cv_id]);
    $this->session->set_flashdata('success', 'Bahasa dihapus.');
    redirect('user/profile');
    }

    public function test_mail($slug = 'generic')
    {
        // ganti ke emailmu sendiri dulu saat uji
        $to = $this->input->get('to') ?: 'ziyan@lapi-itb.com';

        $data = [
            'name'         => 'Ziyan',
            'email'        => 'ziyan@lapi-itb.com',
            'verify_url'   => base_url('auth/verify/DEMO_TOKEN'),
            'reset_url'    => base_url('auth/reset/DEMO_TOKEN'),
            'expires_text' => '24 jam',
            'content'      => '<p>Halo! Ini test SMTP Gmail dari <b>SISTA</b> 🎉</p>',
            'cta_url'      => base_url(),
            'cta_label'    => 'Buka SISTA',
            '_preheader'   => 'Test kirim email dari MailTest controller',
        ];

        $opts = ['subject' => 'SISTA · Test '.ucfirst($slug)];

        $ok = send_mail($slug, $to, $data, $opts);

        $this->_result($ok, "Kirim '{$slug}' ke {$to}");
    }

    /* ===================== LAMPIRAN : DELETE ===================== */
    public function lampiran_item_delete($field_name = '')
    {
        $cv_id = $this->getCvIdOrCreate();
        if (!$cv_id) {
            $this->session->set_flashdata('error', 'CV tidak ditemukan.');
            redirect('user/profile#lampiran');
            return;
        }

        // Whitelist field agar aman
        $allowed_fields = ['ktp_file', 'npwp_file', 'bukti_pajak', 'foto', 'lainnya'];
        if (!$field_name || !in_array($field_name, $allowed_fields)) {
            $this->session->set_flashdata('error', 'Field lampiran tidak valid.');
            redirect('user/profile#lampiran');
            return;
        }

        // --- Hapus File Fisik ---
        $lampiran_row = $this->db->get_where('lampiran_cv', ['cv_id' => $cv_id])->row_array();
        $file_lama = $lampiran_row[$field_name] ?? null;
        
        // PASTIKAN PATH INI BENAR: 'uploads/cv/'
        $upload_path = FCPATH . 'uploads/cv/'; 

        if (!empty($file_lama) && file_exists($upload_path . $file_lama)) {
            @unlink($upload_path . $file_lama);
        }

        // --- Update Database (set ke NULL) ---
        $this->db->where('cv_id', $cv_id)->update('lampiran_cv', [$field_name => NULL]);

        $this->session->set_flashdata('success', 'Lampiran ' . $field_name . ' dihapus.');
        redirect('user/profile#lampiran');
    }

    private function _result($ok, $label)
    {
        if ($ok) {
            echo '<h3>✅ Berhasil</h3><p>'.$label.'</p>';
            echo '<p>Cek inbox/spam email tujuan.</p>';
        } else {
            echo '<h3>❌ Gagal kirim</h3><p>'.$label.'</p>';
            echo '<pre style="white-space:pre-wrap;background:#f6f8fa;padding:12px;border-radius:8px">';
            echo $this->email->print_debugger(['headers']);
            echo '</pre>';
        }
    }


    // User View

    public function profile_view($id = null)
    {
        $id = (int)$id;
        if (!$id) {
            show_error('User ID tidak valid', 400);
            return;
        }

        $session_user_id = (int)$this->session->userdata('id');
        if (!$session_user_id) {
            redirect('auth');
            return;
        }

        $this->load->model('Profile_model');
        
        // Ambil data user
        // Asumsi: Model User_model sudah di-load/autoload
        $user = $this->User_model->getById($id); 
        if (!$user) {
            show_error('User tidak ditemukan', 404);
            return;
        }
        
        // =========================================================
        // BLOK BARU & PERBAIKAN
        // =========================================================
        
        $is_owner = ($session_user_id === $id);

        // 1. Ambil Visibility Map (Visibilitas per Bagian Utama CV)
        $visibility_rows = $this->db
            ->get_where('user_visibility', ['user_id' => $id])
            ->result_array();

        $visibility_map = [];
        foreach ($visibility_rows as $row) {
            $visibility_map[$row['section']] = (int)$row['is_visible'];
        }

        // 2. Ambil Profil Lengkap (sections)
        $profile = $this->Profile_model->get_profile_for_viewer($id, $session_user_id);
        
        // 3. Ambil Data Lampiran (Satu Baris, Termasuk Status Visibilitas Per Item)
        // Memanggil fungsi baru di Profile_model
        $lampiran = $this->Profile_model->get_lampiran_by_user_id($id); 
        if (!$lampiran) {
            $lampiran = []; // Pastikan ini array kosong jika tidak ada data
        }

        // 4. Definisikan Array Data Sekali Saja
        $data = [
            'title'          => 'Profil',
            'user'           => $user,
            'profile'        => $profile,
            'is_owner'       => $is_owner,
            'visibility_map' => $visibility_map,
            'lampiran'       => $lampiran, // <-- DATA LAMPIRAN YANG AKAN DIPAKAI DI VIEW
        ];
        
        // =========================================================
        // AKHIR BLOK BARU & PERBAIKAN
        // =========================================================

        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/profile_view', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Toggle hide/unhide item — hanya owner boleh.
     * Expects POST: table, id, action ('hide' | 'unhide')
     */
    public function hide_item()
    {
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
            return;
        }

        $session_user_id = (int)$this->session->userdata('id');
        if (!$session_user_id) {
            show_error('Unauthorized', 403);
            return;
        }

        $table  = $this->input->post('table', true);
        $row_id = (int)$this->input->post('id');
        $action = $this->input->post('action', true);

        if (!$table || !$row_id || !in_array($action, ['hide', 'unhide'])) {
            show_error('Parameter tidak lengkap', 400);
            return;
        }

        // Whitelist tabel yang bisa di-hide
        $allowed_tables = [
            'pengalaman_kerja',
            'pendidikan_formal',
            'pendidikan_nonformal',
            'sertifikasi_profesi',
            'bahasa',
            'lampiran_cv',
            'cv'
        ];

        if (!in_array($table, $allowed_tables)) {
            show_error('Table tidak diizinkan', 403);
            return;
        }

        if (!$this->db->field_exists('user_id', $table)) {
            show_error('Table ini belum mendukung hide', 400);
            return;
        }

        // Ambil data baris
        $row = $this->db->where('id', $row_id)->get($table)->row_array();
        if (!$row) {
            show_error('Item tidak ditemukan', 404);
            return;
        }

        if ((int)$row['user_id'] !== $session_user_id) {
            show_error('Hanya pemilik yang dapat menyembunyikan item ini', 403);
            return;
        }

        if (!$this->db->field_exists('is_hidden', $table)) {
            show_error('Kolom is_hidden belum ada', 400);
            return;
        }

        $newVal = ($action === 'hide') ? 1 : 0;
        $this->db->where('id', $row_id)->update($table, [
            'is_hidden'  => $newVal,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'action'  => $action
            ]));
    }


    // Hide data per section
    public function toggle_section_visibility()
    {
        // 1. Pastikan user login
        $user_id = (int) $this->session->userdata('id');
        if (!$user_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(403) // Forbidden
                ->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
        }

        // 2. Ambil data dari POST
        $section = $this->input->post('section', true);
        $is_visible = (int) $this->input->post('is_visible'); // Akan jadi 1 atau 0

        if (empty($section)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400) // Bad Request
                ->set_output(json_encode(['success' => false, 'message' => 'Section name required']));
        }

        // 3. Siapkan data untuk "UPSERT" (Update or Insert)
        $data = [
            'user_id'    => $user_id,
            'section'    => $section,
            'is_visible' => $is_visible,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // 4. Cek apakah datanya sudah ada
        $exists = $this->db
            ->where('user_id', $user_id)
            ->where('section', $section)
            ->get('user_visibility')
            ->row();

        if ($exists) {
            // Jika ada, UPDATE
            $this->db
                ->where('id', $exists->id)
                ->update('user_visibility', $data);
        } else {
            // Jika tidak ada, INSERT
            $this->db->insert('user_visibility', $data);
        }

        // 5. Kirim respon sukses
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }


}
