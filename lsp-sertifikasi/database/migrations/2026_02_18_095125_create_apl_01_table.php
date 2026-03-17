<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel APL-01 (Permohonan Sertifikasi)
        Schema::create('apl_01', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained()->onDelete('cascade');
            
            // ── BAGIAN 1: DATA PRIBADI (pre-filled dari asesmen) ──
            $table->string('nama_lengkap');
            $table->string('nik');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('kebangsaan')->default('Indonesia');
            $table->text('alamat_rumah');
            $table->string('kode_pos')->nullable();
            $table->string('telp_rumah')->nullable();
            $table->string('telp_kantor')->nullable();
            $table->string('hp')->nullable();
            $table->string('email');
            $table->string('kualifikasi_pendidikan')->nullable();
            
            // ── DATA PEKERJAAN ──
            $table->string('nama_institusi')->nullable();
            $table->string('jabatan')->nullable();
            $table->text('alamat_kantor')->nullable();
            $table->string('kode_pos_kantor')->nullable();
            $table->string('telp_kantor_detail')->nullable();
            $table->string('fax_kantor')->nullable();
            $table->string('email_kantor')->nullable();
            
            // ── BAGIAN 2: DATA SERTIFIKASI (auto dari skema) ──
            $table->enum('tujuan_asesmen', ['Sertifikasi', 'PKT', 'RPL', 'Lainnya'])
                  ->default('Sertifikasi');
            $table->string('tujuan_asesmen_lainnya')->nullable();
            
            // ── TANDA TANGAN ──
            $table->text('ttd_pemohon')->nullable(); // base64 signature image
            $table->date('tanggal_ttd_pemohon')->nullable();
            $table->string('nama_ttd_pemohon')->nullable();
            
            $table->text('ttd_admin')->nullable(); // base64 signature admin
            $table->date('tanggal_ttd_admin')->nullable();
            $table->string('nama_ttd_admin')->nullable();
            
            // ── STATUS & METADATA ──
            $table->enum('status', ['draft', 'submitted', 'verified', 'approved'])
                  ->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });

        // Tabel Bukti Kelengkapan (untuk upload file ke Google Drive)
        Schema::create('apl_01_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apl_01_id')->constrained('apl_01')->onDelete('cascade');
            $table->enum('kategori', ['persyaratan_dasar', 'administratif']);
            $table->string('nama_dokumen');
            $table->string('deskripsi')->nullable();
            
            // Google Drive info
            $table->string('gdrive_file_id')->nullable();
            $table->string('gdrive_file_url')->nullable();
            $table->string('original_filename')->nullable();
            
            // Status verifikasi
            $table->enum('status', ['Ada Memenuhi Syarat', 'Ada Tidak Memenuhi Syarat', 'Tidak Ada'])
                  ->default('Tidak Ada');
            $table->text('catatan')->nullable();
            
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('uploaded_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apl_01_bukti');
        Schema::dropIfExists('apl_01');
    }
};