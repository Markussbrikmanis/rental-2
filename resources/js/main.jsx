import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';

import CoreUiApp from './CoreUiApp';

const element = document.getElementById('app');

if (element) {
    createRoot(element).render(
        <React.StrictMode>
            <CoreUiApp />
        </React.StrictMode>,
    );
}
