import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                heim: {
                    50: '#f0f8f5',
                    100: '#dcf0e9',
                    200: '#bee1d5',
                    300: '#93cbb9',
                    400: '#64b09a',
                    500: '#3f947e',
                    600: '#2c7865',
                    700: '#155d49', // Exact Brand Green from Logo
                    800: '#114a3b',
                    900: '#0c352a',
                    950: '#061f19',
                },
                brand: {
                    DEFAULT: '#155d49',
                    dark: '#0f4435',
                    light: '#2c7865',
                    surface: '#f4faf7',
                }
            },
        },
    },

    plugins: [forms],
};
