import mermaid from 'mermaid';

mermaid.initialize({
    startOnLoad: false,
    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'default',
    securityLevel: 'strict',
});

window.initWikiMermaid = async function initWikiMermaid() {
    const nodes = document.querySelectorAll('.wiki-content .mermaid:not([data-mermaid-rendered])');

    if (nodes.length === 0) {
        return;
    }

    nodes.forEach((node) => {
        node.setAttribute('data-mermaid-rendered', 'true');
    });

    try {
        await mermaid.run({ nodes });
    } catch (error) {
        console.error('Mermaid render failed', error);
    }
};

document.addEventListener('DOMContentLoaded', () => window.initWikiMermaid());
document.addEventListener('livewire:navigated', () => window.initWikiMermaid());
