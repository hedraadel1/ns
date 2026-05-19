import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                'surface-high': '#0B0E14',
                'surface-mid': '#161B22',
                'action-primary': '#007AFF',
                'ai-core': '#6366F1',
                'status-success': '#10B981',
                'status-warning': '#F59E0B',
                'status-error': '#EF4444',
            },
            letterSpacing: {
                'tight': '-0.02em',
            },
        },
    },
    plugins: [],
};
