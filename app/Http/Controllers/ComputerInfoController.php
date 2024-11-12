<?php

namespace App\Http\Controllers;

use App\Models\ComputerInfo;
use Illuminate\Http\Request;

class ComputerInfoController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            '*.RAM' => 'required|string|max:255',
            '*.processor' => 'required|string|max:255',
            '*.GPU' => 'required|string|max:255',
            '*.monitor' => 'required|string|max:255',
            '*.headphones' => 'required|string|max:255',
            '*.mouse' => 'required|string|max:255',
            '*.keyboard' => 'required|string|max:255',
        ]);

        $createdRecords = [];

        foreach ($validatedData as $data) {
            $existing = ComputerInfo::where('RAM', $data['RAM'])
                ->where('processor', $data['processor'])
                ->where('monitor', $data['monitor'])
                ->where('headphones', $data['headphones'])
                ->where('mouse', $data['mouse'])
                ->where('keyboard', $data['keyboard'])
                ->first();

            if (!$existing) {
                $computerInfo = ComputerInfo::create($data);
                $createdRecords[] = $computerInfo;
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
