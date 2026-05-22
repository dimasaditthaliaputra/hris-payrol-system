<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollDetail;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function payrollIndex()
    {
        return view('reports.payroll');
    }

    public function exportPayrollExcel(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020|max:2099',
        ]);

        $month = (int) $request->month;
        $year  = (int) $request->year;

        $payroll = Payroll::where('month', $month)->where('year', $year)->first();

        if (!$payroll) {
            return back()->with('error', 'Data Payroll untuk periode tersebut tidak ditemukan.');
        }

        $details = PayrollDetail::with(['employee.department', 'employee.position'])
            ->where('payroll_id', $payroll->id)
            ->get();

        if ($details->isEmpty()) {
            return back()->with('error', 'Tidak ada detail penggajian untuk periode tersebut.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Payroll');

        // Headers
        $headers = [
            'No', 'NIK', 'Nama Karyawan', 'Departemen', 'Jabatan',
            'Gaji Pokok', 'Total Tunjangan', 'Uang Lembur', 'Insentif', 'THR', 'Total Pendapatan Kotor',
            'Potongan Manual', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'PPh 21', 'Cicilan Kasbon', 'Total Potongan',
            'Gaji Bersih', 'Hari Kerja', 'Jam Lembur'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Data
        $rowNum = 2;
        foreach ($details as $idx => $detail) {
            $sheet->setCellValue('A' . $rowNum, $idx + 1);
            $sheet->setCellValue('B' . $rowNum, $detail->employee->nik);
            $sheet->setCellValue('C' . $rowNum, $detail->employee->name);
            $sheet->setCellValue('D' . $rowNum, $detail->employee->department->name ?? '-');
            $sheet->setCellValue('E' . $rowNum, $detail->employee->position->name ?? '-');
            
            $sheet->setCellValue('F' . $rowNum, $detail->basic_salary);
            $sheet->setCellValue('G' . $rowNum, $detail->total_allowance);
            $sheet->setCellValue('H' . $rowNum, $detail->overtime_pay);
            $sheet->setCellValue('I' . $rowNum, $detail->incentive);
            $sheet->setCellValue('J' . $rowNum, $detail->thr);
            $sheet->setCellValue('K' . $rowNum, $detail->gross_salary);

            $sheet->setCellValue('L' . $rowNum, $detail->total_deduction);
            $sheet->setCellValue('M' . $rowNum, $detail->bpjs_kesehatan);
            $sheet->setCellValue('N' . $rowNum, $detail->bpjs_ketenagakerjaan);
            $sheet->setCellValue('O' . $rowNum, $detail->pph21);
            $sheet->setCellValue('P' . $rowNum, $detail->cash_advance_installment);
            $sheet->setCellValue('Q' . $rowNum, $detail->total_cuts);

            $sheet->setCellValue('R' . $rowNum, $detail->net_salary);
            $sheet->setCellValue('S' . $rowNum, $detail->working_days);
            $sheet->setCellValue('T' . $rowNum, $detail->overtime_hours);

            $rowNum++;
        }

        // Styling
        $styleArray = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'FFE9ECEF']
            ],
            'borders' => [
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle('A1:T1')->applyFromArray($styleArray);

        $fileName = 'Laporan_Payroll_' . $payroll->period_label . '.xlsx';
        $fileName = str_replace(' ', '_', $fileName);

        $response = new StreamedResponse(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
