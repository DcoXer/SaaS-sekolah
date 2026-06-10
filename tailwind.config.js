import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50:  'rgb(var(--p-50)  / <alpha-value>)',
                    100: 'rgb(var(--p-100) / <alpha-value>)',
                    200: 'rgb(var(--p-200) / <alpha-value>)',
                    300: 'rgb(var(--p-300) / <alpha-value>)',
                    400: 'rgb(var(--p-400) / <alpha-value>)',
                    500: 'rgb(var(--p-500) / <alpha-value>)',
                    600: 'rgb(var(--p-600) / <alpha-value>)',
                    700: 'rgb(var(--p-700) / <alpha-value>)',
                    800: 'rgb(var(--p-800) / <alpha-value>)',
                    900: 'rgb(var(--p-900) / <alpha-value>)',
                    950: 'rgb(var(--p-950) / <alpha-value>)',
                },
            },
        },
    },

    plugins: [forms],
};
