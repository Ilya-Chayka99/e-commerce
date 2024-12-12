<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'metadata_id' => 'required',
            'info_id' => 'required',
            'matrix_id' => 'required',
        ]);
        $table = Table::create([
            'metadata_id' => $validatedData['metadata_id'],
            'info_id' => $validatedData['info_id'],
            'matrix_id' => $validatedData['matrix_id'],
        ]);

        return response()->json([
            'message' => 'good',
            'data' => $table,
        ]);
    }

    public function getAll()
    {
       //return Carbon::now()->addHours(4);
        $tables = Table::with(['metadata', 'info','rentals','matrix'])->get()->map(function ($table) {

            return [
                'id' => $table->id,
                'metadata' => $table->metadata,
                'info' => $table->info,
                'rentals' => $table->rentals,
                'matrix_id' => $table->matrix->id,
                'matrix' => [
                    'id' => $table->matrix->id,
                    'x' => $table->matrix->x,
                    'y' => $table->matrix->y,
                    'width' => $table->matrix->width,
                    'height' => $table->matrix->height,
                    'info' => $table->name,
                    'status' => $table->status
                ],
            ];
        });

        return response()->json([
            'message' => 'good',
            'data' => $tables,
        ]);
    }
}
