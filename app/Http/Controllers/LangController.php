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
        $lang = $request->input('lang', 'fr');
        
        // Validate language code
        if (!in_array($lang, $this->availableLocales)) {
            $lang = 'fr'; // Fallback to French
        }
        
        // Persist on the authenticated account (so it sticks after login).
        if ($request->input('context') === 'manager' && auth('manager')->check()) {
            $manager = auth('manager')->user();
            if (($manager->locale ?? null) !== $lang) {
                $manager->forceFill(['locale' => $lang])->save();
            }
        } elseif (auth()->check()) {
            $user = auth()->user();
            if (($user->locale ?? null) !== $lang) {
                $user->forceFill(['locale' => $lang])->save();
            }
        } elseif (auth('manager')->check()) {
            $manager = auth('manager')->user();
            if (($manager->locale ?? null) !== $lang) {
                $manager->forceFill(['locale' => $lang])->save();
            }
        }

        // Set locale in session
        Session::put('locale', $lang);
        
        // Set locale for current request
        App::setLocale($lang);
        
        // Old language-switch links still lead to the equivalent stable public URL.
        $previous = parse_url(url()->previous(), PHP_URL_PATH) ?: '/';
        $path = trim($previous, '/');
        if (preg_match('#^/(fr|en|nl|es)(/.*)?$#', $previous, $matches)) {
            $path = trim($matches[2] ?? '', '/');
        }
        $name = array_search($path, config('seo.pages'), true);
        if ($name !== false) {
            return redirect()->route($name, ['locale' => $lang]);
        }

        return redirect()->back();
    }
}
