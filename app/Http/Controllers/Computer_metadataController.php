<?php

namespace App\Http\Controllers;

use App\Models\Computer_metadata;
use Illuminate\Http\Request;

class Computer_metadataController extends Controller
{
    public function frontend(){
        return Computer_metadata::all();
    }
}
