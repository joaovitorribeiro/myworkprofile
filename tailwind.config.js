export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.tsx',
        './resources/**/*.ts',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#6a11cb',
                secondary: '#2575fc',
                accent: '#00f2fe',
                dark: '#0f0c29',
                darker: '#09071c',
                light: '#f0f0f0',
            },
            fontFamily: {
                poppins: ['Poppins', 'sans-serif'],
                orbitron: ['Orbitron', 'sans-serif'],
            },
            boxShadow: {
                glow: '0 10px 20px rgba(37,117,252,0.3)',
            },
            height: {
                navbar: '80px',
            },
            transitionTimingFunction: {
                playful: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            },
            keyframes: {
                float: {
                    '0%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-15px)' },
                    '100%': { transform: 'translateY(0)' },
                },
                pulseglow: {
                    '0%': { boxShadow: '0 0 0 0 rgba(0,242,254,0.4)' },
                    '70%': { boxShadow: '0 0 0 20px rgba(0,242,254,0)' },
                    '100%': { boxShadow: '0 0 0 0 rgba(0,242,254,0)' },
                },
                rotate3d: {
                    '0%': { transform: 'rotateX(0) rotateY(0) rotateZ(0)' },
                    '100%': {
                        transform:
                            'rotateX(360deg) rotateY(360deg) rotateZ(360deg)',
                    },
                },
            },
            animation: {
                float: 'float 3s ease-in-out infinite',
                pulseglow: 'pulseglow 2s infinite',
                rotate3d: 'rotate3d 15s linear infinite',
            },
        },
    },
    plugins: [],
};
