import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms'; 
/** @type {import('tailwindcss').Config} */ 
export default { 
    content: [ 
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php', 
        './storage/framework/views/*.php', 
        './resources/views/**/*.blade.php', 
        './resources/**/*.js',
        './resources/**/*.vue',
    ], 
    theme: { 
        extend: { 
            fontFamily: { 
                sans: ['Figtree', 
                    ...defaultTheme.fontFamily.sans], 
                }, 
                keyframes: {
                    float: {
                        '0%, 100%': { transform: 'translateY(0)' },
                        '50%': { transform: 'translateY(-10px)' },
                    },
                },
                animation: {
                    float: 'float 3s ease-in-out infinite',
                }, 
                colors: { 
                    primary: '#1E9FD8', 
                    'primary-foreground': '#ffffff',
                     accent: '#14b8a6', 
                     background: '#f9fafb', 
                     foreground: '#111827', 
                     muted: '#6b7280', 
                     'muted-foreground': '#9ca3af', 
                     border: '#e5e7eb', 
                     card: '#ffffff', 
                     secondary: '#f3f4f6',
                     // DalyTrac brand palette (centralized, reusable).
                     // Usage: bg-brand-primary, text-brand-secondary,
                     // border-brand-accent, bg-brand-primary/90, etc.
                     brand: {
                         primary: '#0B2638',
                         secondary: '#2D7080',
                         accent: '#E7B84B',
                     },
                    }, 
                }, 
            }, 
            plugins: [forms], 
        };