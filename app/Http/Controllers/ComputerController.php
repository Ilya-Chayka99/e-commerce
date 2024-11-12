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
        ]);

        $computer = Computer::create([
            'metadata_id' => $validatedData['metadata_id'],
            'info_id' => $validatedData['info_id'],
        ]);

        return response()->json([
            'message' => 'good',
            'data' => $computer,
        ]);
    }

    public function getAll()
    {
        $computers = Computer::with(['metadata', 'info','rentals'])->get();

        return response()->json([
            'message' => 'good',
            'data' => $computers,
        ]);
    }
}
