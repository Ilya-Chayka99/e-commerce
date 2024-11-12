<?php

namespace App\Http\Controllers;

use App\Models\ComputerMetadata;
use Illuminate\Http\Request;

class ComputerMetadataController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            '*.name' => 'required|string|max:255',
            '*.price' => 'required|numeric',
        ]);

        $createdRecords = [];

        foreach ($validatedData as $data) {
            $existing = ComputerMetadata::where('name', $data['name'])->first();

            if (!$existing) {
                $computerMetadata = ComputerMetadata::create($data);
                $createdRecords[] = $computerMetadata;
            }
        }
        if (count($createdRecords) > 0) {
            return response()->json([
                'message' => 'good',
                'data' => $createdRecords,
            ]);
        } else {
            return response()->json([
                'message' => 'no',
            ]);
        }
    }
}
