<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ParticipantsImport implements ToCollection, WithHeadingRow
{
    public $data = [];
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena row 1 adalah header
            
            // Basic validation
            $errors = [];
            
            if (empty($row['nama_lengkap'])) {
                $errors[] = "Nama lengkap wajib diisi";
            }
            
            if (empty($row['email'])) {
                $errors[] = "Email wajib diisi";
            } elseif (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format email tidak valid";
            }
            
            // Check if email already exists in database
            if (!empty($row['email']) && \App\Models\User::where('email', $row['email'])->exists()) {
                $errors[] = "Email sudah terdaftar di sistem";
            }
            
            // Store data
            $this->data[] = [
                'row_number' => $rowNumber,
                'name' => $row['nama_lengkap'] ?? '',
                'email' => $row['email'] ?? '',
                'valid' => empty($errors),
                'errors' => $errors,
            ];
        }
    }
    
    public function isValid()
    {
        foreach ($this->data as $row) {
            if (!$row['valid']) {
                return false;
            }
        }
        return true;
    }
    
    public function getValidData()
    {
        return array_filter($this->data, function($row) {
            return $row['valid'];
        });
    }
    
    public function getErrors()
    {
        $errors = [];
        foreach ($this->data as $row) {
            if (!$row['valid']) {
                $errors[] = [
                    'row' => $row['row_number'],
                    'errors' => $row['errors']
                ];
            }
        }
        return $errors;
    }
}