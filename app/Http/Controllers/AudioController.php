<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AudioController extends Controller
{
    public function stream(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'lang' => 'required|string|in:en,ar',
        ]);

        $text = $request->text;
        $lang = $request->lang;

        // Google Translate TTS Endpoint (Unofficial but widely used)
        $url = 'https://translate.google.com/translate_tts';

        $response = Http::get($url, [
            'ie' => 'UTF-8',
            'q' => $text,
            'tl' => $lang,
            'client' => 'tw-ob',
        ]);

        if ($response->successful()) {
            return response($response->body())
                ->header('Content-Type', 'audio/mpeg');
        }

        return response()->json(['error' => 'Failed to fetch audio'], 500);
    }
}
