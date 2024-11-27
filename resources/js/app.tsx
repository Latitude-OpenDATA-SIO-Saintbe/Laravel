import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        return resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx')
        ).then((component) => {
            console.log(`Resolved component for ${name}:`, component);
            return component;
        });
    },
    /**
     * Sets up the application by creating a root element and rendering the provided App component with the given props.
     *
     * @param {Object} param0 - The setup parameters.
     * @param {HTMLElement} param0.el - The HTML element to render the App into.
     * @param {React.ComponentType} param0.App - The App component to be rendered.
     * @param {Object} param0.props - The props to be passed to the App component.
     */
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
