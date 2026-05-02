/**
 * همان رندر مینی‌مارک‌داونِ AiAnswer.vue — برای Blade ادمین از طریق window.__supportAiRenderMiniMd.
 * ### / ## / # ، لیست * و - ، شماره‌دار ، **bold**
 */

function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
}

function inlineFormat(s) {
    return s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
}

/**
 * @param {string} src
 * @returns {string}
 */
export function renderMiniMarkdownToHtml(src) {
    const lines = String(src || '').split(/\r?\n/);
    const out = [];
    let inUL = false;
    let inOL = false;
    let pBuffer = [];

    const flushP = () => {
        if (pBuffer.length) {
            const txt = inlineFormat(escapeHtml(pBuffer.join(' ')));
            out.push(`<p>${txt}</p>`);
            pBuffer = [];
        }
    };
    const closeLists = () => {
        if (inUL) {
            out.push('</ul>');
            inUL = false;
        }
        if (inOL) {
            out.push('</ol>');
            inOL = false;
        }
    };

    for (const raw of lines) {
        const line = raw.trim();

        if (/^###\s+/.test(line)) {
            flushP();
            closeLists();
            const h = line.replace(/^###\s+/, '');
            out.push(`<h3 class="md-h3">${inlineFormat(escapeHtml(h))}</h3>`);
            continue;
        }
        if (/^##\s+/.test(line)) {
            flushP();
            closeLists();
            const h = line.replace(/^##\s+/, '');
            out.push(`<h2 class="md-h2">${inlineFormat(escapeHtml(h))}</h2>`);
            continue;
        }
        if (/^#\s+/.test(line)) {
            flushP();
            closeLists();
            const h = line.replace(/^#\s+/, '');
            out.push(`<h1 class="md-h1">${inlineFormat(escapeHtml(h))}</h1>`);
            continue;
        }

        const mUL = line.match(/^[*-]\s+(.+)/);
        if (mUL) {
            flushP();
            if (!inUL) {
                closeLists();
                out.push('<ul>');
                inUL = true;
            }
            out.push(`<li>${inlineFormat(escapeHtml(mUL[1]))}</li>`);
            continue;
        }

        const mOL = line.match(/^\d+\.\s+(.+)/);
        if (mOL) {
            flushP();
            if (!inOL) {
                closeLists();
                out.push('<ol>');
                inOL = true;
            }
            out.push(`<li>${inlineFormat(escapeHtml(mOL[1]))}</li>`);
            continue;
        }

        if (line === '') {
            flushP();
            closeLists();
            continue;
        }

        pBuffer.push(line);
    }

    flushP();
    closeLists();
    return out.join('\n');
}
