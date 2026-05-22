<?php

namespace App\Services;

use App\Models\ImportLog;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeService
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
        protected ActivityLogService $activityLogService,
        protected ?DepartmentRepositoryInterface $departmentRepository = null,
        protected ?PositionRepositoryInterface $positionRepository = null,
    ) {}

    public function createEmployee(array $data)
    {
        DB::beginTransaction();
        try {
            if (isset($data['profile_photo']) && $data['profile_photo']->isValid()) {
                $data['profile_photo'] = $data['profile_photo']->store('employees/photos', 'public');
            } else {
                unset($data['profile_photo']);
            }

            $employee = $this->employeeRepository->create($data);

            if (isset($data['allowances'])) {
                foreach ($data['allowances'] as $allowance) {
                    if (!empty($allowance['allowance_type_id']) && !empty($allowance['amount'])) {
                        $employee->allowances()->create($allowance);
                    }
                }
            }

            if (isset($data['deductions'])) {
                foreach ($data['deductions'] as $deduction) {
                    if (!empty($deduction['deduction_type_id']) && !empty($deduction['amount'])) {
                        $employee->deductions()->create($deduction);
                    }
                }
            }

            $this->activityLogService->log('create_employee', 'Menambahkan karyawan NIK: ' . $employee->nik);

            DB::commit();
            return $employee;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['profile_photo'])) {
                Storage::disk('public')->delete($data['profile_photo']);
            }
            throw $e;
        }
    }

    public function updateEmployee(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $employee = $this->employeeRepository->find($id);

            if (isset($data['profile_photo']) && $data['profile_photo']->isValid()) {
                // Delete old photo
                if ($employee->profile_photo) {
                    Storage::disk('public')->delete($employee->profile_photo);
                }
                $data['profile_photo'] = $data['profile_photo']->store('employees/photos', 'public');
            } else {
                unset($data['profile_photo']);
            }

            $this->employeeRepository->update($id, $data);

            // Sync Allowances
            $employee->allowances()->delete();
            if (isset($data['allowances'])) {
                foreach ($data['allowances'] as $allowance) {
                    if (!empty($allowance['allowance_type_id']) && !empty($allowance['amount'])) {
                        $employee->allowances()->create($allowance);
                    }
                }
            }

            // Sync Deductions
            $employee->deductions()->delete();
            if (isset($data['deductions'])) {
                foreach ($data['deductions'] as $deduction) {
                    if (!empty($deduction['deduction_type_id']) && !empty($deduction['amount'])) {
                        $employee->deductions()->create($deduction);
                    }
                }
            }

            $this->activityLogService->log('update_employee', 'Memperbarui karyawan NIK: ' . $employee->nik);

            DB::commit();
            return $employee;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteEmployee(string $id)
    {
        DB::beginTransaction();
        try {
            $employee = $this->employeeRepository->find($id);
            $this->employeeRepository->delete($id);

            $this->activityLogService->log('delete_employee', 'Menghapus karyawan NIK: ' . $employee->nik);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =========================================================
    //  IMPORT DARI EXCEL
    // =========================================================

    /**
     * Import data karyawan dari file Excel.
     * Mengembalikan array ringkasan hasil import.
     */
    public function importFromExcel(UploadedFile $file): array
    {
        $filename     = $file->getClientOriginalName();
        $totalRows    = 0;
        $successRows  = 0;
        $failedRows   = 0;
        $errors       = [];

        // Load spreadsheet
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true); // kolom A-N

        // Baris pertama = header, mulai proses dari baris ke-2
        // Build lookup index untuk department & position berdasarkan code
        $departments = DB::table('departments')->whereNull('deleted_at')->pluck('id', 'code');
        $positions   = DB::table('positions')->whereNull('deleted_at')->pluck('id', 'code');

        foreach ($rows as $rowIndex => $row) {
            // Lewati header
            if ($rowIndex === 1) {
                continue;
            }

            // Berhenti jika seluruh baris kosong (NIK minimal harus ada)
            $nik = trim($row['A'] ?? '');
            if ($nik === '') {
                break;
            }

            $totalRows++;

            // ---- Validasi per baris ----
            $rowErrors = [];

            if (empty($nik)) {
                $rowErrors[] = 'NIK wajib diisi.';
            }

            if (empty(trim($row['B'] ?? ''))) {
                $rowErrors[] = 'Nama lengkap wajib diisi.';
            }

            $deptCode = trim($row['C'] ?? '');
            if (empty($deptCode)) {
                $rowErrors[] = 'Kode Departemen wajib diisi.';
            } elseif (!isset($departments[$deptCode])) {
                $rowErrors[] = "Kode Departemen '{$deptCode}' tidak ditemukan.";
            }

            $posCode = trim($row['D'] ?? '');
            if (empty($posCode)) {
                $rowErrors[] = 'Kode Posisi wajib diisi.';
            } elseif (!isset($positions[$posCode])) {
                $rowErrors[] = "Kode Posisi '{$posCode}' tidak ditemukan.";
            }

            $gender = strtoupper(trim($row['E'] ?? ''));
            if (!in_array($gender, ['L', 'P'])) {
                $rowErrors[] = "Jenis Kelamin harus 'L' atau 'P'.";
            }

            $joinDate = trim($row['F'] ?? '');
            if (empty($joinDate) || !strtotime($joinDate)) {
                $rowErrors[] = 'Tanggal Masuk wajib diisi dengan format YYYY-MM-DD.';
            }

            $statusKerja = trim($row['G'] ?? 'Tetap');
            if (!in_array($statusKerja, ['Tetap', 'Kontrak', 'Freelance', 'Nonaktif'])) {
                $rowErrors[] = "Status Kerja tidak valid. Pilih: Tetap, Kontrak, Freelance, atau Nonaktif.";
            }

            $statusPajak = trim($row['H'] ?? 'TK/0');
            if (!in_array($statusPajak, ['TK/0', 'K/0', 'K/1', 'K/2', 'K/3'])) {
                $rowErrors[] = "Status Pajak tidak valid. Pilih: TK/0, K/0, K/1, K/2, atau K/3.";
            }

            $basicSalary = str_replace([',', '.'], '', trim($row['I'] ?? '0'));
            if (!is_numeric($basicSalary) || (float)$basicSalary < 0) {
                $rowErrors[] = 'Gaji Pokok harus berupa angka positif.';
            }

            // Cek duplikat NIK (hanya data aktif)
            $nikExists = DB::table('employees')->whereNull('deleted_at')->where('nik', $nik)->exists();
            if ($nikExists) {
                $rowErrors[] = "NIK '{$nik}' sudah terdaftar di sistem.";
            }

            if (!empty($rowErrors)) {
                $failedRows++;
                $errors[] = [
                    'row'     => $rowIndex,
                    'nik'     => $nik ?: '-',
                    'name'    => trim($row['B'] ?? '-'),
                    'errors'  => $rowErrors,
                ];
                continue;
            }

            // ---- Simpan ke database ----
            try {
                DB::table('employees')->insert([
                    'nik'                  => $nik,
                    'name'                 => trim($row['B']),
                    'department_id'        => $departments[$deptCode],
                    'position_id'          => $positions[$posCode],
                    'gender'               => $gender,
                    'join_date'            => date('Y-m-d', strtotime($joinDate)),
                    'status_kerja'         => $statusKerja,
                    'status_pajak'         => $statusPajak,
                    'basic_salary'         => (float) $basicSalary,
                    'place_of_birth'       => trim($row['J'] ?? null) ?: null,
                    'date_of_birth'        => !empty(trim($row['K'] ?? '')) ? date('Y-m-d', strtotime(trim($row['K']))) : null,
                    'phone'                => trim($row['L'] ?? null) ?: null,
                    'email'                => trim($row['M'] ?? null) ?: null,
                    'npwp'                 => trim($row['N'] ?? null) ?: null,
                    'bpjs_kesehatan'       => trim($row['O'] ?? null) ?: null,
                    'bpjs_ketenagakerjaan' => trim($row['P'] ?? null) ?: null,
                    'nama_bank'            => trim($row['Q'] ?? null) ?: null,
                    'rekening_bank'        => trim($row['R'] ?? null) ?: null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
                $successRows++;
            } catch (\Exception $e) {
                $failedRows++;
                $errors[] = [
                    'row'    => $rowIndex,
                    'nik'    => $nik,
                    'name'   => trim($row['B'] ?? '-'),
                    'errors' => ['Gagal menyimpan: ' . $e->getMessage()],
                ];
            }
        }

        // Tentukan status keseluruhan
        if ($failedRows === 0 && $successRows > 0) {
            $status = 'success';
        } elseif ($successRows === 0) {
            $status = 'failed';
        } else {
            $status = 'partial';
        }

        // Catat ke import_logs
        ImportLog::create([
            'user_id'      => auth()->id(),
            'type'         => 'employees',
            'filename'     => $filename,
            'status'       => $status,
            'total_rows'   => $totalRows,
            'success_rows' => $successRows,
            'failed_rows'  => $failedRows,
            'errors'       => !empty($errors) ? $errors : null,
            'notes'        => "Import karyawan dari file: {$filename}",
        ]);

        $this->activityLogService->log(
            'import_employees',
            "Import {$totalRows} baris karyawan dari file '{$filename}'. Sukses: {$successRows}, Gagal: {$failedRows}."
        );

        return [
            'status'       => $status,
            'total_rows'   => $totalRows,
            'success_rows' => $successRows,
            'failed_rows'  => $failedRows,
            'errors'       => $errors,
        ];
    }

    // =========================================================
    //  DOWNLOAD TEMPLATE EXCEL
    // =========================================================

    /**
     * Buat dan kembalikan file Excel template kosong.
     */
    public function downloadTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Karyawan');

        // ---- Definisi kolom header ----
        $headers = [
            'A' => 'NIK *',
            'B' => 'Nama Lengkap *',
            'C' => 'Kode Departemen *',
            'D' => 'Kode Posisi *',
            'E' => 'Jenis Kelamin * (L/P)',
            'F' => 'Tanggal Masuk * (YYYY-MM-DD)',
            'G' => 'Status Kerja * (Tetap/Kontrak/Freelance/Nonaktif)',
            'H' => 'Status Pajak * (TK/0/K/0/K/1/K/2/K/3)',
            'I' => 'Gaji Pokok *',
            'J' => 'Tempat Lahir',
            'K' => 'Tanggal Lahir (YYYY-MM-DD)',
            'L' => 'No HP',
            'M' => 'Email',
            'N' => 'NPWP',
            'O' => 'BPJS Kesehatan',
            'P' => 'BPJS Ketenagakerjaan',
            'Q' => 'Nama Bank',
            'R' => 'Nomor Rekening Bank',
        ];

        // Set lebar kolom
        $widths = [
            'A' => 15, 'B' => 28, 'C' => 22, 'D' => 20, 'E' => 22,
            'F' => 28, 'G' => 42, 'H' => 35, 'I' => 18, 'J' => 20,
            'K' => 28, 'L' => 18, 'M' => 25, 'N' => 20, 'O' => 22,
            'P' => 28, 'Q' => 18, 'R' => 22,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Tulis header di baris 1
        foreach ($headers as $col => $label) {
            $cell = $col . '1';
            $sheet->setCellValue($cell, $label);
        }

        // Style header: latar biru tua, teks putih tebal, border, rata tengah
        $lastCol    = 'R';
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F4E79'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFB8CCE4'],
                ],
            ],
        ]);

        // Tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(40);

        // ---- Contoh baris data (baris 2) ----
        $exampleData = [
            'A' => 'EMP001',
            'B' => 'Budi Santoso',
            'C' => 'IT',         // sesuaikan dengan kode dept di sistem
            'D' => 'DEV',        // sesuaikan dengan kode posisi di sistem
            'E' => 'L',
            'F' => '2024-01-15',
            'G' => 'Tetap',
            'H' => 'TK/0',
            'I' => '5000000',
            'J' => 'Jakarta',
            'K' => '1990-05-20',
            'L' => '081234567890',
            'M' => 'budi@email.com',
            'N' => '',
            'O' => '',
            'P' => '',
            'Q' => 'BCA',
            'R' => '1234567890',
        ];

        foreach ($exampleData as $col => $value) {
            $sheet->setCellValue($col . '2', $value);
        }

        // Style baris contoh: latar kuning muda
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFF2CC'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFBFBFBF'],
                ],
            ],
        ]);

        // ---- Sheet kedua: daftar departemen & posisi ----
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi');
        $refSheet->setCellValue('A1', 'KODE DEPARTEMEN YANG TERSEDIA');
        $refSheet->setCellValue('B1', 'NAMA DEPARTEMEN');
        $refSheet->setCellValue('D1', 'KODE POSISI YANG TERSEDIA');
        $refSheet->setCellValue('E1', 'NAMA POSISI');

        $refSheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF375623']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $departments = DB::table('departments')->whereNull('deleted_at')->select('code', 'name')->get();
        foreach ($departments as $i => $dept) {
            $refSheet->setCellValue('A' . ($i + 2), $dept->code);
            $refSheet->setCellValue('B' . ($i + 2), $dept->name);
        }

        $positions = DB::table('positions')->whereNull('deleted_at')->select('code', 'name')->get();
        foreach ($positions as $i => $pos) {
            $refSheet->setCellValue('D' . ($i + 2), $pos->code);
            $refSheet->setCellValue('E' . ($i + 2), $pos->name);
        }

        $refSheet->getColumnDimension('A')->setWidth(25);
        $refSheet->getColumnDimension('B')->setWidth(35);
        $refSheet->getColumnDimension('C')->setWidth(5);
        $refSheet->getColumnDimension('D')->setWidth(25);
        $refSheet->getColumnDimension('E')->setWidth(35);

        // Aktifkan sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        // Simpan ke storage temp
        $tmpPath = storage_path('app/temp_template_karyawan.xlsx');
        $writer  = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return $tmpPath;
    }
}
