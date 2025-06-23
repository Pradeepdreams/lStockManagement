<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function getByModel(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $logs = ActivityLog::where('model', $request->model)
            ->where('model_id', $request->model_id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function getByUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $logs = ActivityLog::where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
