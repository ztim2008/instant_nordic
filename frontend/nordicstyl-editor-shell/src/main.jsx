import { createRoot } from 'react-dom/client';
import 'flexlayout-react/style/light.css';
import { EditorApp } from './EditorApp.jsx';
import './editor.css';

const mountNode = document.getElementById('nordic-editor-shell-root');

if (mountNode) {
    createRoot(mountNode).render(<EditorApp />);
}