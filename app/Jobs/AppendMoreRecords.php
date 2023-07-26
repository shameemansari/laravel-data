<?php

namespace App\Jobs;

use App\Models\Record;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
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
        $users = Record::query()
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

        $file = public_path("/records/$this->folder.csv");
        $open = fopen($file, 'a+');
        foreach ($users as $user) {
            fputcsv($open, $user);
        }
        fclose($open);
    }
}
