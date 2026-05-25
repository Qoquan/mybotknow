import { marked } from 'marked'
import hljs from 'highlight.js'

const renderer = new marked.Renderer()

renderer.code = function({ text, lang }: { text: string, lang?: string }) {
    const language = lang && hljs.getLanguage(lang) ? lang : 'plaintext'
    const highlighted = hljs.highlight(text, { language }).value
    return `<div class="code-block">
        <div class="code-header">
            <span class="code-lang">${language}</span>
            <button class="copy-btn" onclick="navigator.clipboard.writeText(this.closest('.code-block').querySelector('code').innerText)">Copier</button>
        </div>
        <pre><code class="hljs language-${language}">${highlighted}</code></pre>
    </div>`
}

marked.use({ renderer, breaks: true, gfm: true })

export function useMarkdown() {
    function renderMarkdown(content: string): string {
        return marked(content) as string
    }

    return { renderMarkdown }
}
