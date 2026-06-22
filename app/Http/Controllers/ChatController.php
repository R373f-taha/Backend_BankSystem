<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
   public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        $userMessage = $validated['message'];

        $systemInstruction =
            "أنت مساعد بنكي رسمي ومتخصص. أنت تعمل في بنك موثوق.

            ⚠️ **تعليمات التنسيق:**
            1. استخدم الإيموجيات المناسبة مثل 📌 ✅ 💡 📊 🔹 ✨.
            2. استخدم علامات الترقيم بشكل صحيح.
            3. تترك سطراً فارغاً بين الفقرات.
            4. استخدم **النص العريض** للعناوين المهمة.

            📌 **المواضيع البنكية المسموح بها:**
            الحسابات، التحويلات، البطاقات، القروض، الودائع، الفوائد، الأمان المصرفي.

            ❌ **إذا سألك عن موضوع خارج البنك:**
            قل بأدب: '❌💵 عذراً، أنا مساعد بنكي متخصص فقط في الإجابة عن استفسارات الخدمات البنكية.'";

        try {
            $apiKey = env('GEMINI_API_KEY');

            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'error' => 'مفتاح API غير موجود',
                ], 500);
            }

            $model = 'gemini-2.5-flash';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemInstruction . "💬 سؤال العميل:" . $userMessage]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 2048,
                    'topP' => 0.95,
                    'topK' => 40,
                ]
            ];

            $response = Http::withOptions([
                'verify' => false,//make SSL check disable
                'timeout' => 120,
            ])->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'عذراً، لم أستطع معالجة طلبك.';

                return response()->json([

                    'reply' => $reply,

                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'فشل الاتصال بـ Gemini API',
                'debug' => $response->body(),
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ غير متوقع',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }


     public function listAvailableModels()
    {
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'error' => 'مفتاح API غير موجود في ملف .env'
            ]);
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}";

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 60,
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $models = $data['models'] ?? [];

                $supportedModels = array_filter($models, function($model) {
                    return in_array('generateContent', $model['supportedGenerationMethods'] ?? []);
                });

                usort($supportedModels, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });

                return response()->json([
                    'success' => true,
                    'api_key' => substr($apiKey, 0, 10) . '...' . substr($apiKey, -5),
                    'total_models' => count($supportedModels),
                    'models' => array_map(function($model) {
                        return [
                            'name' => str_replace('models/', '', $model['name']),
                            'display_name' => $model['displayName'] ?? $model['name'],
                            'version' => $model['version'] ?? 'unknown',
                            'supports_generateContent' => in_array('generateContent', $model['supportedGenerationMethods'] ?? []),
                            'description' => $model['description'] ?? 'No description',
                        ];
                    }, $supportedModels),
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'فشل جلب النماذج',
                'status' => $response->status(),
                'details' => $response->body(),
            ]);

        } catch (Exception  $e) {
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ',
                'debug' => $e->getMessage(),
            ]);
        }
    }
}
