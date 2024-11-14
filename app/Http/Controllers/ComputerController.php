<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use Illuminate\Http\Request;

class ComputerController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'metadata_id' => 'required|exists:computer_metadata,id',
            'info_id' => 'required|exists:computer_infos,id',
            'matrix_id' => 'required',
        ]);

        $computer = Computer::create([
            'metadata_id' => $validatedData['metadata_id'],
            'info_id' => $validatedData['info_id'],
            'matrix_id' => $validatedData['matrix_id'],
        ]);

        return response()->json([
            'message' => 'good',
            'data' => $computer,
        ]);
    }

    public function getAll()
    {
        $computers = Computer::with(['metadata', 'info','rentals','matrix'])->get()->map(function ($computer) {

            return [
                'id' => $computer->id,
                'metadata' => $computer->metadata,
                'info' => $computer->info,
                'rentals' => $computer->rentals,
                'matrix_id' => $computer->matrix->id,
                'matrix' => [
                    'id' => $computer->matrix->id,
                    'x' => $computer->matrix->x,
                    'y' => $computer->matrix->y,
                    'width' => $computer->matrix->width,
                    'height' => $computer->matrix->height,
                    'info' => $computer->matrix->info,
                    'status' => $computer->status
                ],
            ];
        });

        return response()->json([
            'message' => 'good',
            'data' => $computers,
        ]);
    }
}
