<?php

use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\FileController;
use App\Models\Record;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::post('import', [FileController::class,'import'])->name('import');
Route::get('export', [FileController::class,'exportAll'])->name('exportAll');


Route::get('test',function() {
    $records = Record::limit(20)->get()->toArray();

    $filePath = public_path('/records/Record.xlsx');

    $spreadsheet = IOFactory::load($filePath);
    $totalSheet = $spreadsheet->getSheetCount();
    $spreadsheet->createSheet($totalSheet + 1);
    $spreadsheet->setActiveSheetIndex($totalSheet);
    $activeWorksheet = $spreadsheet->getActiveSheet()->setTitle('Batch - '.$totalSheet);
    $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
    // $writer->setOffice2003Compatibility(true);

    foreach($records as $key => $record) {
        $val = $key + 1;
        $activeWorksheet->setCellValue("A{$val}", $record['id']);
        $activeWorksheet->setCellValue("B{$val}", $record['name']);
        $activeWorksheet->setCellValue("C{$val}", $record['bio']);
        $activeWorksheet->setCellValue("D{$val}", $record['contact']);
        $activeWorksheet->setCellValue("E{$val}", $record['age']);
        $activeWorksheet->setCellValue("F{$val}", $record['is_active']);
        $activeWorksheet->setCellValue("G{$val}", $record['born_on']);

    }
    $writer->save($filePath);
    // $writer = new Xlsx($spreadsheet);
    // $writer->save('hello world.xlsx');
});