<?php

$names = [
    'Safi Salsabila',
    'Tiara Maharlika',
    'Okta Tri Mulasih',
    'Zahra Putri Sagita',
    'Rizta Mufidatun Nisa',
    'Oktavian Alisya',
];

echo "=== CEK DATA USER (email persis + status) ===\n\n";

foreach ($names as $name) {
    $users = \App\Models\User::where('name', 'like', "%{$name}%")->get();

    if ($users->isEmpty()) {
        echo "[NOT FOUND] {$name}\n\n";
        continue;
    }

    foreach ($users as $u) {
        echo "Nama         : [{$u->name}]\n";
        echo "Email (raw)  : [{$u->email}]\n";
        echo "Email length : " . strlen($u->email) . "\n";
        echo "Punya spasi? : " . (preg_match('/\s/', $u->email) ? 'YA - ADA SPASI!' : 'tidak') . "\n";
        echo "Verified at  : " . ($u->email_verified_at ?? 'BELUM VERIFIED') . "\n";
        echo "Created at   : {$u->created_at}\n";
        echo "-------------------------------------------\n";
    }
    echo "\n";
}

echo "\n=== CEK FAILED JOBS TERKAIT EMAIL (10 terakhir) ===\n\n";
$failed = \DB::table('failed_jobs')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

if ($failed->isEmpty()) {
    echo "Tidak ada failed_jobs.\n";
} else {
    foreach ($failed as $f) {
        $payload = json_decode($f->payload, true);
        $jobName = $payload['displayName'] ?? 'unknown';
        echo "ID: {$f->id} | Job: {$jobName} | Failed at: {$f->failed_at}\n";
        // tampilkan sedikit exception message
        $excerpt = substr($f->exception, 0, 200);
        echo "Exception: {$excerpt}...\n";
        echo "-------------------------------------------\n";
    }
}

echo "\n=== CEK PENDING JOBS DI QUEUE (jika ada) ===\n\n";
$pending = \DB::table('jobs')->count();
echo "Total pending jobs: {$pending}\n";
if ($pending > 0) {
    $sample = \DB::table('jobs')->limit(5)->get();
    foreach ($sample as $j) {
        $payload = json_decode($j->payload, true);
        $jobName = $payload['displayName'] ?? 'unknown';
        echo "ID: {$j->id} | Job: {$jobName} | Attempts: {$j->attempts} | Created: " . date('Y-m-d H:i:s', $j->created_at) . "\n";
    }
}

echo "\nSELESAI.\n";