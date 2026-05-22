<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Models\ImportLog;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Exception;

class AttendanceService
{
    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected EmployeeRepositoryInterface $employeeRepository,
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Placeholder untuk fungsi pengecekan apakah periode terkunci (payroll).
     */
    public function isLocked(string $date): bool
    {
        $carbonDate = \Carbon\Carbon::parse($date);
        return \App\Models\Payroll::where('month', $carbonDate->month)
            ->where('year', $carbonDate->year)
            ->where('status', 'locked')
            ->exists();
    }

    /**
     * Import Absensi dari Excel
     */
    public function importExcel($file): array
    {
        $filename     = $file->getClientOriginalName();
        $totalRows    = 0;
        $successRows  = 0;
        $failedRows   = 0;
        $errors       = [];
        
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);
            
            $employees = $this->employeeRepository->model()->whereNull('deleted_at')->get()->keyBy('nik');
            
            DB::beginTransaction();
            
            foreach ($rows as $rowIndex => $row) {
                // Lewati header
                if ($rowIndex === 1) {
                    continue;
                }
                
                // Berhenti jika NIK kosong
                $nik = trim((string)($row['A'] ?? ''));
                if ($nik === '') {
                    break;
                }
                
                $totalRows++;
                $rowErrors = [];
                
                $date = trim((string)($row['B'] ?? ''));
                $timeIn = trim((string)($row['C'] ?? ''));
                $timeOut = trim((string)($row['D'] ?? ''));
                $status = strtolower(trim((string)($row['E'] ?? 'hadir')));
                
                if (empty($date)) {
                    $rowErrors[] = 'Tanggal wajib diisi.';
                }
                
                $employee = $employees->get($nik);
                if (!$employee) {
                    $rowErrors[] = "NIK '{$nik}' tidak ditemukan.";
                }
                
                if (!empty($date) && $this->isLocked($date)) {
                    $rowErrors[] = "Periode untuk tanggal {$date} sudah dikunci (Payroll).";
                }
                
                if ($employee && !empty($date) && $this->attendanceRepository->existsForEmployeeAndDate($employee->id, $date)) {
                    $rowErrors[] = "Absensi untuk NIK '{$nik}' pada tanggal {$date} sudah ada (Duplikat).";
                }
                
                $validStatuses = ['hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'cuti'];
                if (!in_array($status, $validStatuses)) {
                    $rowErrors[] = "Status '{$status}' tidak valid. (Pilih: hadir, terlambat, izin, sakit, alpha, cuti)";
                }
                
                if (!empty($rowErrors)) {
                    $failedRows++;
                    $errors[] = [
                        'row'     => $rowIndex,
                        'nik'     => $nik,
                        'name'    => $employee ? $employee->name : '-',
                        'errors'  => $rowErrors,
                    ];
                    continue;
                }
                
                try {
                    $this->attendanceRepository->create([
                        'employee_id' => $employee->id,
                        'date' => $date,
                        'time_in' => empty($timeIn) ? null : $timeIn,
                        'time_out' => empty($timeOut) ? null : $timeOut,
                        'status' => $status
                    ]);
                    $successRows++;
                } catch (\Exception $e) {
                    $failedRows++;
                    $errors[] = [
                        'row'    => $rowIndex,
                        'nik'    => $nik,
                        'name'   => $employee ? $employee->name : '-',
                        'errors' => ['Gagal menyimpan: ' . $e->getMessage()],
                    ];
                }
            }
            
            DB::commit();
            
            if ($failedRows === 0 && $successRows > 0) {
                $statusLog = 'success';
            } elseif ($successRows === 0) {
                $statusLog = 'failed';
            } else {
                $statusLog = 'partial';
            }
            
            ImportLog::create([
                'user_id'      => auth()->id(),
                'type'         => 'attendances',
                'filename'     => $filename,
                'status'       => $statusLog,
                'total_rows'   => $totalRows,
                'success_rows' => $successRows,
                'failed_rows'  => $failedRows,
                'errors'       => !empty($errors) ? $errors : null,
                'notes'        => "Import absensi dari file: {$filename}",
            ]);
            
            $this->activityLogService->log(
                'import_attendances',
                "Import {$totalRows} baris absensi dari file '{$filename}'. Sukses: {$successRows}, Gagal: {$failedRows}."
            );
            
            return [
                'success'      => true,
                'status'       => $statusLog,
                'total_rows'   => $totalRows,
                'success_rows' => $successRows,
                'failed_rows'  => $failedRows,
                'errors'       => $errors,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Download Template Excel Absensi
     */
    public function downloadTemplate(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Absensi');
        
        $headers = [
            'A' => 'NIK *',
            'B' => 'Tanggal * (YYYY-MM-DD)',
            'C' => 'Jam Masuk (HH:MM)',
            'D' => 'Jam Keluar (HH:MM)',
            'E' => 'Status * (hadir/terlambat/izin/sakit/alpha/cuti)',
        ];
        
        $widths = ['A' => 20, 'B' => 25, 'C' => 20, 'D' => 20, 'E' => 45];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        
        foreach ($headers as $col => $label) {
            $cell = $col . '1';
            $sheet->setCellValue($cell, $label);
        }
        
        $lastCol = 'E';
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFB8CCE4']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);
        
        $exampleData = [
            'A' => 'EMP001',
            'B' => date('Y-m-d'),
            'C' => '08:00',
            'D' => '17:00',
            'E' => 'hadir',
        ];
        foreach ($exampleData as $col => $value) {
            $sheet->setCellValue($col . '2', $value);
        }
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF2CC']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFBFBFBF']]],
        ]);
        
        $tmpPath = storage_path('app/temp_template_absensi.xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpPath);
        
        return $tmpPath;
    }
}
