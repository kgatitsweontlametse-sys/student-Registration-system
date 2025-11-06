import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import './index.css'; // optional: create this for app-level styles

const container = document.getElementById('root');
const root = createRoot(container);
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);