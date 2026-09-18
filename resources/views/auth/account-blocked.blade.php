@extends('layouts.auth')

@section('title', 'Compte bloqué')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>

                        <h2 class="mt-4 text-xl font-semibold text-gray-900">Compte bloqué</h2>

                        <p class="mt-2 text-sm text-gray-600">
                            Votre compte entreprise est actuellement restreint.
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            Contactez l'administrateur de votre entreprise ou écrivez à
                            <a href="mailto:support@dalytrac.com" class="text-blue-600 hover:underline font-medium">support@dalytrac.com</a>
                            pour obtenir de l'aide.
                        </p>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                                Se déconnecter
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection