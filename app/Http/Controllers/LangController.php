<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LangController extends Controller
{
    /**
     * Available languages
     */
    private $availableLocales = ['en', 'fr', 'es', 'nl'];

    /**
     * Change the application language
     */
    public function change(Request $request)
    {
        $lang = $request->input('lang', 'en');
        
        // Validate language code
        if (!in_array($lang, $this->availableLocales)) {
            $lang = 'en'; // Fallback to English
        }
        
        // Set locale in session
        Session::put('locale', $lang);
        
        // Set locale for current request
        App::setLocale($lang);
        
        return redirect()->back();
    }
}
