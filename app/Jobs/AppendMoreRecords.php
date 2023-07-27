<?php

namespace App\Jobs;

use App\Models\Record;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Queue\InteractsWithQueue;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;



class AppendMoreRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(
        public $chunkIndex,
        public $chunkSize,
        public $folder
    ) { }

    public function handle()
    {
        $filePath = public_path("/records/$this->folder.xlsx");
        $records = Record::query()
            ->skip($this->chunkIndex * $this->chunkSize)
            ->take($this->chunkSize)
            ->get()
            ->map(function ($record) {
                return [
                    'id'            => $record->id ?? "",
                    'name'          => $record->name ?? "",
                    'bio'           => $record->bio ?? "",
                    'contact'       => $record->contact ?? "",
                    'age'           => $record->age ?? "",
                    'is_active'     => $record->is_active ? 'Yes' : 'No',
                    'born_on'       => $record->born_on?->format('d-m-Y') ?? "",
                ];
            });

        
        // $open = fopen($filePath, 'a+');
        // foreach ($records as $user) {
        //     fputcsv($open, $user);
        // }
        // fclose($open);

        $spreadsheet = IOFactory::load($filePath);
        $totalSheet = $spreadsheet->getSheetCount();
        $spreadsheet->createSheet($totalSheet + 1);
        $spreadsheet->setActiveSheetIndex($totalSheet);
        $activeWorksheet = $spreadsheet->getActiveSheet()->setTitle('Batch - '. ($totalSheet + 1));
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');

        $activeWorksheet->setCellValue("A1", 'id');
        $activeWorksheet->setCellValue("B1", 'name');
        $activeWorksheet->setCellValue("C1", 'bio');
        $activeWorksheet->setCellValue("D1", 'contact');
        $activeWorksheet->setCellValue("E1", 'age');
        $activeWorksheet->setCellValue("F1", 'is_active');
        $activeWorksheet->setCellValue("G1", 'born_on');
    
        foreach($records as $key => $record) {
            $val = $key + 2;
            $activeWorksheet->setCellValue("A{$val}", $record['id']);
            $activeWorksheet->setCellValue("B{$val}", $record['name']);
            $activeWorksheet->setCellValue("C{$val}", $record['bio']);
            $activeWorksheet->setCellValue("D{$val}", $record['contact']);
            $activeWorksheet->setCellValue("E{$val}", $record['age']);
            $activeWorksheet->setCellValue("F{$val}", $record['is_active']);
            $activeWorksheet->setCellValue("G{$val}", $record['born_on']);
    
        }
        $writer->save($filePath);

    }
}
