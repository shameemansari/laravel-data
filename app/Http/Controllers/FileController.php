<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Record;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use App\Jobs\AppendMoreRecords;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\CreateRecordsExportFile;
use Symfony\Component\Console\Input\Input;

class FileController extends Controller
{
    public function index(Request $request) {
        
    }

    public function import(Request $request) {
        // $request->validate([
        //     'csv' => 'required|mimes:csv,txt'
        // ]);
     ;
        $filePath = public_path('records/'.$request->csv);
        $recordFile = fopen($filePath, 'r');
        $header = [
            'id',
            'name',
            'bio',
            'contact',
            'age',
            'is_active',
            'born_on',
        ];
        $records = [];
        while ($row = fgetcsv($recordFile)) {
            $records[] = array_combine($header, $row);
        }

        fclose($recordFile);
        dd($records);
        
    }

    public function exportAll() {
        $chunkSize = 10000;
        // $recordsCount = Record::count();
        $recordsCount = 1000;
        $numberOfChunks = ceil($recordsCount / $chunkSize);

        $folder = now()->toDateString() . '-' . str_replace(':', '-', now()->toTimeString());

        $batches = [
            new CreateRecordsExportFile($chunkSize, $folder)
        ];

        if ($recordsCount > $chunkSize) {
            $numberOfChunks = $numberOfChunks - 1;
            for ($numberOfChunks; $numberOfChunks > 0; $numberOfChunks--) {
                $batches[] = new AppendMoreRecords($numberOfChunks, $chunkSize, $folder);
            }
        }

        Bus::batch($batches)
            ->name('Export Users')
            ->then(function (Batch $batch) use ($folder) {
                // $file = public_path("records.csv");
            })
            ->catch(function (Batch $batch, Throwable $e) {
                // send email to admin or log error
                Log::warning($e->getMessage());
            })
            ->finally(function (Batch $batch) use ($folder) {
                // delete local file
            })
            ->dispatch();

        return redirect()->back();
    }
}
