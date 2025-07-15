<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Topic;

use Illuminate\Support\Facades\Log;

class ModuleTopicApiController extends Controller
{
    public function getTopics(Module $module)
    {
        return response()->json($module->topics()->select('id', 'name')->get());
    }
}
