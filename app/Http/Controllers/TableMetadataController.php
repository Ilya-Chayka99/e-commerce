<?php

namespace App\Http\Controllers;

use App\Models\TableMetadata;
use Illuminate\Http\Request;

class TableMetadataController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            '*.name' => 'required|string|max:255',
            '*.price' => 'required|numeric',
        ]);

        $createdRecords = [];

        foreach ($validatedData as $data) {
            $existing = TableMetadata::where('name', $data['name'])->first();

            if (!$existing) {
                $tableMetadata = TableMetadata::create($data);
                $createdRecords[] = $tableMetadata;
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
