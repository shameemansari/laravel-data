<?php

namespace App\Jobs;

use App\Models\Record;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\File;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\SheetCollection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateRecordsExportFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(
        public $chunkSize,
        public $folder
    ) { }

    public function handle() : void
    {
        $records = Record::query()
            ->take($this->chunkSize)
            ->get();

        if(!File::isDirectory(public_path("/records"))){
            File::makeDirectory(public_path("/records"), 0777, true, true);
        } 

        $style = (new Style())
        ->setCellAlignment('left')
        ->setShouldShrinkToFit(false)
        ->setShouldWrapText(false);

        $sheets = new SheetCollection([
            'Batch - 1' => $this->recordsGenerator($records)
        ]);

        // $generate = new SheetCollection($this->recordsGenerator($records));
        $generate = $this->recordsGenerator($records);
        (new FastExcel($sheets))
            ->headerStyle($style)
            ->rowsStyle($style)
            ->export(public_path("/records/$this->folder.xlsx"), function ($record) {
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
    }

    private function recordsGenerator($records)
    {
        foreach ($records as $record) {
            yield $record;
        }
    }

}
