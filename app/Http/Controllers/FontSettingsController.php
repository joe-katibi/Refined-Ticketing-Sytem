<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FontSettingsController extends Controller
{
    /**
     * Update font size setting
     */
    public function updateFontSize(Request $request)
    {
        $request->validate([
            'fontSize' => 'required|in:text-xs,text-sm,text-base'
        ]);

        $fontSize = $request->input('fontSize');
        
        // Set cookie for 1 year with explicit settings for better persistence
        $cookie = cookie(
            'fontSize', 
            $fontSize, 
            60 * 24 * 365, // 1 year
            '/', // path
            null, // domain (null = current domain)
            false, // secure (false for local development)
            false // httpOnly (false so JavaScript can read it)
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Font size updated successfully',
            'fontSize' => $fontSize
        ])->cookie($cookie);
    }
}
