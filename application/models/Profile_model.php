<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile_model extends CI_Model
{
    public function get_user_by_email($email)
    {
        return $this->db->get_where('user', ['email' => $email])->row_array();
    }

    public function get_latest_cv($user_id)
    {
        if (!$user_id) return null;
        return $this->db->from('cv')
            ->where('user_id', $user_id)
            ->order_by('updated_at', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()->row_array();
    }

    public function get_cv_counts($cv_id)
    {
        if (!$cv_id) return [
            'pengalaman'=>0,'pendidikan'=>0,'pendidikan_formal'=>0,
            'pelatihan'=>0,'sertifikasi'=>0,'bahasa'=>0,'lampiran'=>0
        ];

        $cnt = function($table) use ($cv_id){
            return (int)$this->db->where('cv_id', $cv_id)->count_all_results($table);
        };

        $formal    = $cnt('pendidikan_formal');
        $nonformal = $cnt('pendidikan_nonformal');   // << ini yang ngisi Pelatihan

        return [
            'pengalaman'        => $cnt('pengalaman_kerja'),
            'pendidikan'        => $formal,              // formal only
            'pendidikan_formal' => $formal,
            'pelatihan'         => $nonformal,           // << penting
            'sertifikasi'       => $cnt('sertifikasi_profesi'),
            'bahasa'            => $cnt('bahasa'),
            'lampiran'          => $cnt('lampiran_cv'),
        ];
    }



    // placeholder: kalau lo punya tabel activity sendiri, ganti logicnya
    public function get_activity_count($user_id)
    {
        // contoh: return (int)$this->db->where('user_id',$user_id)->count_all_results('activity_log');
        return 0;
    }

    public function get_suggested_people($me_id, $limit = 5)
    {
        // subquery: latest CV per user
        $latestCvSub = $this->db->select('user_id, MAX(id) AS last_cv_id', false)
                                ->from('cv')
                                ->group_by('user_id')
                                ->get_compiled_select();

        // subquery count per section (biar efisien)
        $pf = $this->db->select('cv_id, COUNT(*) cnt', false)->from('pendidikan_formal')->group_by('cv_id')->get_compiled_select();
        $pn = $this->db->select('cv_id, COUNT(*) cnt', false)->from('pendidikan_nonformal')->group_by('cv_id')->get_compiled_select();
        $pk = $this->db->select('cv_id, COUNT(*) cnt', false)->from('pengalaman_kerja')->group_by('cv_id')->get_compiled_select();
        $sp = $this->db->select('cv_id, COUNT(*) cnt', false)->from('sertifikasi_profesi')->group_by('cv_id')->get_compiled_select();
        $bb = $this->db->select('cv_id, COUNT(*) cnt', false)->from('bahasa')->group_by('cv_id')->get_compiled_select();
        // lampiran: cukup cek ada baris + minimal satu kolom file terisi (anggap baris = cukup)
        $lc = $this->db->select('cv_id, COUNT(*) cnt', false)->from('lampiran_cv')->group_by('cv_id')->get_compiled_select();

        $this->db->select("
            u.id, u.name, u.email, u.photo, u.cover,
            c.posisi, c.perusahaan, c.domisili_kota, c.domisili_negara, c.updated_at,
            COALESCE(pf.cnt,0) AS cnt_pf,
            COALESCE(pn.cnt,0) AS cnt_pn,
            COALESCE(pk.cnt,0) AS cnt_pk,
            COALESCE(sp.cnt,0) AS cnt_sp,
            COALESCE(bb.cnt,0) AS cnt_bb,
            COALESCE(lc.cnt,0) AS cnt_lc,
            /* skor kelengkapan: 0..6 berdasarkan ada/tidaknya tiap bagian */
            (CASE WHEN COALESCE(pf.cnt,0) > 0 THEN 1 ELSE 0 END
            + CASE WHEN COALESCE(pn.cnt,0) > 0 THEN 1 ELSE 0 END
            + CASE WHEN COALESCE(pk.cnt,0) > 0 THEN 1 ELSE 0 END
            + CASE WHEN COALESCE(sp.cnt,0) > 0 THEN 1 ELSE 0 END
            + CASE WHEN COALESCE(bb.cnt,0) > 0 THEN 1 ELSE 0 END
            + CASE WHEN COALESCE(lc.cnt,0) > 0 THEN 1 ELSE 0 END) AS completeness_score
        ", false);

        $this->db->from('user u');
        $this->db->join("($latestCvSub) lcsub", 'lcsub.user_id = u.id', 'left', false);
        $this->db->join('cv c', 'c.id = lcsub.last_cv_id', 'left');

        $this->db->join("($pf) pf", 'pf.cv_id = c.id', 'left', false);
        $this->db->join("($pn) pn", 'pn.cv_id = c.id', 'left', false);
        $this->db->join("($pk) pk", 'pk.cv_id = c.id', 'left', false);
        $this->db->join("($sp) sp", 'sp.cv_id = c.id', 'left', false);
        $this->db->join("($bb) bb", 'bb.cv_id = c.id', 'left', false);
        $this->db->join("($lc) lc", 'lc.cv_id = c.id', 'left', false);

        $this->db->where('u.is_active', 1);
        $this->db->where('u.id !=', (int)$me_id);

        // prioritas: paling lengkap dulu, lalu paling baru diperbarui
        $this->db->order_by('completeness_score', 'DESC');
        $this->db->order_by('c.updated_at', 'DESC');
        $this->db->order_by('u.last_login', 'DESC');

        $this->db->limit((int)$limit);

        return $this->db->get()->result_array();
    }

    public function get_cv_counts_by_user($user_id)
    {
        $user_id = (int)$user_id;
        if (!$user_id) {
            return ['pengalaman'=>0,'pendidikan'=>0,'pendidikan_formal'=>0,'pelatihan'=>0,'sertifikasi'=>0,'bahasa'=>0,'lampiran'=>0];
        }

        // ambil semua cv_id milik user
        $cv_ids = $this->db->select('id')->from('cv')->where('user_id', $user_id)->get()->result_array();
        if (empty($cv_ids)) {
            return ['pengalaman'=>0,'pendidikan'=>0,'pendidikan_formal'=>0,'pelatihan'=>0,'sertifikasi'=>0,'bahasa'=>0,'lampiran'=>0];
        }
        $cv_ids = array_map(function($r){ return (int)$r['id']; }, $cv_ids);

        $cnt = function($table) use ($cv_ids){
            $this->db->where_in('cv_id', $cv_ids);
            return (int)$this->db->count_all_results($table);
        };

        $formal    = $cnt('pendidikan_formal');
        $nonformal = $cnt('pendidikan_nonformal'); // << ini “Pelatihan”

        return [
            'pengalaman'        => $cnt('pengalaman_kerja'),
            'pendidikan'        => $formal,          // formal only
            'pendidikan_formal' => $formal,
            'pelatihan'         => $nonformal,       // << harus > 0 kalau DB ada 5
            'sertifikasi'       => $cnt('sertifikasi_profesi'),
            'bahasa'            => $cnt('bahasa'),
            'lampiran'          => $cnt('lampiran_cv'),
        ];
    }
    

    // view profile
    public function get_profile_for_viewer(int $profile_user_id, int $viewer_user_id = 0)
    {
        $is_owner = ($profile_user_id === (int)$viewer_user_id);

        // user basic
        $user = $this->db->where('id', $profile_user_id)->get('user')->row_array();

        // latest cv
        $latest_cv = $this->get_latest_cv($profile_user_id);

        // ambil semua cv id milik user (untuk section queries)
        $cv_ids = $this->db->select('id')->from('cv')->where('user_id', $profile_user_id)->get()->result_array();
        $cv_ids = array_map(function($r){ return (int)$r['id']; }, $cv_ids);

        // helper: get section rows safely (with optional filter)
        $get_section = function($table, $where_add = []) use ($cv_ids, $is_owner, $latest_cv) {
            if (empty($cv_ids) && $table !== 'cv') return [];

            $this->db->reset_query();
            if ($table === 'cv') {
                // Sebaiknya gunakan ID CV terbaru jika tabelnya adalah 'cv' itu sendiri
                $this->db->from('cv')->where('id', (int)($latest_cv['id'] ?? 0));
            } else {
                $this->db->from($table);
                $this->db->where_in('cv_id', $cv_ids);
            }

            // apply additional where if provided
            foreach ($where_add as $k => $v) {
                $this->db->where($k, $v);
            }

            // ==========================================================
            // PERBAIKAN 1: Menggunakan 'is_visible' = 1
            // ==========================================================
            // if table has 'is_visible' and viewer is NOT owner -> filter is_visible = 1
            if (!$is_owner && $this->db->field_exists('is_visible', $table)) {
                $this->db->where('is_visible', 1); // <-- DIUBAH DARI is_hidden = 0
            }

            return $this->db->get()->result_array();
        };

        // ambil sections umum (sesuaikan daftar tabel dengan DB kamu)
        $sections = [
            'pengalaman_kerja'     => $get_section('pengalaman_kerja'),
            'pendidikan_formal'    => $get_section('pendidikan_formal'),
            'pendidikan_nonformal' => $get_section('pendidikan_nonformal'),
            'sertifikasi_profesi'  => $get_section('sertifikasi_profesi'),
            'bahasa'               => $get_section('bahasa'),
            'lampiran_cv'          => $get_section('lampiran_cv'),
            // tambahkan section lain sesuai DB (misal 'project', 'portofolio', dll)
        ];

        // ==========================================================
        // PERBAIKAN 2: Hitung 'counts' dari data YANG SUDAH DIFILTER
        // ==========================================================
        $counts = [
            'pengalaman'        => count($sections['pengalaman_kerja']),
            'pendidikan'        => count($sections['pendidikan_formal']), // 'pendidikan' = formal
            'pendidikan_formal' => count($sections['pendidikan_formal']),
            'pelatihan'         => count($sections['pendidikan_nonformal']),
            'sertifikasi'       => count($sections['sertifikasi_profesi']),
            'bahasa'            => count($sections['bahasa']),
            'lampiran'          => count($sections['lampiran_cv']),
        ];

        return [
            'user'      => $user,
            'latest_cv' => $latest_cv,
            'cv_ids'    => $cv_ids,
            'sections'  => $sections,
            'counts'    => $counts // <-- Menggunakan counts yang baru (bukan dari get_cv_counts_by_user)
        ];
    }

    public function get_lampiran_by_user_id(int $user_id): ?array
    {
        $latest_cv = $this->get_latest_cv($user_id);
        if (empty($latest_cv)) { return null; }
        $cv_id = (int)($latest_cv['id'] ?? 0);

        // Ambil SEMUA baris lampiran untuk cv_id ini
        $all_lampiran_rows = $this->db
            ->get_where('lampiran_cv', ['cv_id' => $cv_id])
            ->result_array();
        
        if (empty($all_lampiran_rows)) {
            return null;
        }

        $final_lampiran = [];
        
        foreach ($all_lampiran_rows as $row) {
            foreach ($row as $key => $value) {
                // PERBAIKAN DI SINI:
                // Hanya update $final_lampiran jika $value dari DB TIDAK NULL dan TIDAK KOSONG.
                // Ini mencegah baris baru yang kosong menimpa data file yang sudah ada di baris sebelumnya.
                if ($value !== null && $value !== '') {
                    $final_lampiran[$key] = $value;
                }
                
                // Catatan: Jika $final_lampiran[$key] belum ada, otomatis terisi.
                // Jika sudah ada, akan ditimpa HANYA jika $value baru ada isinya.
            }
        }
        
        return $final_lampiran;
    }


}
