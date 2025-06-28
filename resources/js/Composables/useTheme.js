import { ref, onMounted } from 'vue';

export function useTheme() {
    const theme = ref(localStorage.getItem('theme') || 'light');

    const setTheme = (newTheme) => {
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);

        // Limpia clases de temas anteriores y aplica la nueva
        document.documentElement.classList.remove('theme-dark', 'theme-female', 'theme-elderly');
        if (newTheme !== 'light') {
            document.documentElement.classList.add(`theme-${newTheme}`);
        }
    };

    const initTheme = () => {
        setTheme(theme.value);
    };

    return {
        theme,
        setTheme,
        initTheme
    };
}
