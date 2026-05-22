<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $detail->employee->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
        }
        .info-table, .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 4px;
            vertical-align: top;
        }
        .info-table td:nth-child(odd) {
            font-weight: bold;
            width: 15%;
        }
        .info-table td:nth-child(even) {
            width: 35%;
        }
        .detail-table th, .detail-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .detail-table th {
            background-color: #f4f4f4;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .section-title {
            font-weight: bold;
            background-color: #f4f4f4;
        }
        .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .net-salary {
            font-size: 14px;
            font-weight: bold;
            background-color: #d1e7dd;
        }
        .footer {
            margin-top: 40px;
            width: 100%;
        }
        .footer-table {
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>PT HRIS Company</h2>
        <p>Jl. Jendral Sudirman No. 123, Jakarta Selatan</p>
        <p>Email: hr@hriscompany.com | Telp: (021) 12345678</p>
    </div>

    <h3 style="text-align: center; text-decoration: underline;">SLIP GAJI KARYAWAN</h3>

    <table class="info-table">
        <tr>
            <td>Periode</td>
            <td>: {{ $detail->payroll->period_label }}</td>
            <td>Departemen</td>
            <td>: {{ $detail->employee->department->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>: {{ $detail->employee->nik }}</td>
            <td>Jabatan</td>
            <td>: {{ $detail->employee->position->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>: {{ $detail->employee->name }}</td>
            <td>Hari Kerja</td>
            <td>: {{ $detail->working_days }} hari</td>
        </tr>
    </table>

    <table class="detail-table">
        <thead>
            <tr>
                <th width="50%">PENERIMAAN</th>
                <th width="50%">POTONGAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="vertical-align: top; padding: 0; border: none;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border:none; padding: 8px;">Gaji Pokok</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->basic_salary, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 8px;">Tunjangan</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->total_allowance, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 8px;">Uang Lembur ({{ $detail->overtime_hours }} jam)</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->overtime_pay, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 8px;">Insentif</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->incentive, 0, ',', '.') }}</td>
                        </tr>
                        @if($detail->thr > 0)
                        <tr>
                            <td style="border:none; padding: 8px;">Tunjangan Hari Raya (THR)</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->thr, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
                <td style="vertical-align: top; padding: 0; border: none; border-left: 1px solid #ddd;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border:none; padding: 8px;">Potongan Manual</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->total_deduction, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 8px;">BPJS Kesehatan</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->bpjs_kesehatan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 8px;">BPJS Ketenagakerjaan</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->bpjs_ketenagakerjaan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 8px;">PPh 21</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->pph21, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 8px;">Cicilan Kasbon</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->cash_advance_installment, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="total-row">
                <td style="padding: 0; border: none; border-top: 1px solid #ddd;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border:none; padding: 8px;">Total Penerimaan</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->gross_salary, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 0; border: none; border-top: 1px solid #ddd; border-left: 1px solid #ddd;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border:none; padding: 8px;">Total Potongan</td>
                            <td style="border:none; padding: 8px;" class="text-right">Rp {{ number_format($detail->total_cuts, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="net-salary">
                <td colspan="2" style="padding: 0; border: none; border-top: 1px solid #000;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border:none; padding: 12px; font-size: 16px;">GAJI BERSIH (TAKE HOME PAY)</td>
                            <td style="border:none; padding: 12px; font-size: 16px;" class="text-right">Rp {{ number_format($detail->net_salary, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td width="50%">
                    Penerima,
                    <br><br><br><br>
                    <strong>({{ $detail->employee->name }})</strong>
                </td>
                <td width="50%">
                    Mengetahui,
                    <br><br><br><br>
                    <strong>( HRD Manager )</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
