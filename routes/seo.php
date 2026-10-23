<?php

use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');

// Existing inbound URLs remain usable. Default redirects are deterministic, independent of cookies.
foreach (config('seo.pages') as $name => $path) {
    Route::get('/'.$path, function (\Illuminate\Http\Request $request) use ($name) {
        $target = route($name, ['locale' => config('seo.default_locale')]);
        if ($request->getQueryString()) {
            $target .= '?'.$request->getQueryString();
        }

        return redirect($target, 301);
    })->name('legacy.'.$name);
}
