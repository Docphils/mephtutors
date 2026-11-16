import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import { TextStyleKit } from '@tiptap/extension-text-style';
import Highlight from '@tiptap/extension-highlight';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';

const mountedEditors = new Map();

function normalizeImportedStyles(html) {
    const document = new DOMParser().parseFromString(html, 'text/html');
    // Tiptap stores typography on spans. Move imported block/emphasis styles there first.
    document.body.querySelectorAll('[style]').forEach((element) => {
        if (['SPAN', 'MARK'].includes(element.tagName)) return;
        const span = document.createElement('span');
        for (const property of ['color', 'background-color', 'font-family', 'font-size']) {
            const value = element.style.getPropertyValue(property);
            if (value) {
                span.style.setProperty(property, value);
                element.style.removeProperty(property);
            }
        }
        if (span.hasAttribute('style')) {
            span.append(...element.childNodes);
            element.append(span);
        }
    });
    return document.body.innerHTML;
}

export function isSafeEditorLink(value) {
    if (!value || /[\x00-\x20\\]/.test(value)) return false;
    if (value.startsWith('/') && !value.startsWith('//')) return true;
    if (value.startsWith('#')) return true;
    return /^(https?:|mailto:)/i.test(value);
}

export class BlogRichTextEditor {
    constructor(root, wire) {
        this.root = root;
        this.wire = wire;
        this.events = new AbortController();
        this.toolbar = root.querySelector('[data-editor-toolbar]');
        this.linkPanel = root.querySelector('[data-editor-link-panel]');
        this.linkInput = root.querySelector('[data-editor-link-url]');
        const surface = root.querySelector('[data-editor-surface]');
        const content = root.querySelector('[data-editor-content]').value;
        surface.replaceChildren();
        this.editor = new Editor({
            element: surface,
            extensions: [
                StarterKit.configure({
                    heading: { levels: [1, 2, 3, 4, 5, 6] },
                    link: {
                        openOnClick: false,
                        autolink: false,
                        linkOnPaste: false,
                        HTMLAttributes: { target: null, rel: 'nofollow noopener noreferrer' },
                        isAllowedUri: isSafeEditorLink,
                    },
                }),
                TextAlign.configure({ types: ['heading', 'paragraph'] }),
                TextStyleKit.configure({ lineHeight: false }),
                Highlight.configure({ multicolor: true }),
                Subscript,
                Superscript,
            ],
            content: normalizeImportedStyles(content),
            editorProps: {
                transformPastedHTML: normalizeImportedStyles,
                attributes: {
                    role: 'textbox',
                    'aria-label': 'Article body',
                    'aria-multiline': 'true',
                    'aria-describedby': 'blog-body-help',
                    spellcheck: 'true',
                },
            },
            onUpdate: ({ editor }) => {
                // Defer the update so Save and AI actions include it without a request per keystroke.
                this.wire.$set('body', editor.isEmpty ? '' : editor.getHTML(), false);
                const status = root.querySelector('[data-editor-status]');
                if (status.textContent !== 'Choose Save draft to save changes') {
                    status.textContent = 'Choose Save draft to save changes';
                }
            },
            onTransaction: () => this.refreshToolbar(),
        });

        const listen = (element, event, handler) => element.addEventListener(event, handler, { signal: this.events.signal });
        listen(this.toolbar, 'mousedown', (event) => {
            if (event.target.closest('button')) event.preventDefault();
        });
        listen(this.toolbar, 'click', (event) => {
            const button = event.target.closest('[data-editor-command]');
            if (button && !button.disabled) this.runCommand(button.dataset.editorCommand);
        });
        listen(this.toolbar, 'change', (event) => {
            const select = event.target.dataset.editorSelect;
            const color = event.target.dataset.editorColor;
            if (select) this.setStyle(select, event.target.value);
            if (color === 'color') this.editor.chain().focus().setColor(event.target.value).run();
            if (color === 'highlight') this.editor.chain().focus().setHighlight({ color: event.target.value }).run();
        });
        listen(root.querySelector('[data-editor-link-apply]'), 'click', () => this.applyLink());
        listen(root.querySelector('[data-editor-link-cancel]'), 'click', () => this.closeLinkPanel());
        listen(this.linkInput, 'keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.applyLink();
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                this.closeLinkPanel();
            }
        });
        listen(window, 'blog-body-suggestion-applied', (event) => {
            if (typeof event.detail?.body !== 'string') return;
            this.linkPanel.hidden = true;
            this.editor.commands.setContent(normalizeImportedStyles(event.detail.body));
        });
        this.toolbar.disabled = false;
        root.querySelector('[data-editor-status]').textContent = 'Ready to edit';
        this.refreshToolbar();
    }

    runCommand(command) {
        const chain = this.editor.chain().focus();
        const toggles = {
            bold: 'toggleBold', italic: 'toggleItalic', underline: 'toggleUnderline', strike: 'toggleStrike',
            subscript: 'toggleSubscript', superscript: 'toggleSuperscript', bulletList: 'toggleBulletList',
            orderedList: 'toggleOrderedList', blockquote: 'toggleBlockquote', code: 'toggleCode', codeBlock: 'toggleCodeBlock',
        };
        if (toggles[command]) return chain[toggles[command]]().run();
        if (command.startsWith('align-')) return chain.setTextAlign(command.slice(6)).run();
        switch (command) {
            case 'indent': return chain.sinkListItem('listItem').run();
            case 'outdent': return chain.liftListItem('listItem').run();
            case 'horizontalRule': return chain.setHorizontalRule().run();
            case 'clearHighlight': return chain.unsetHighlight().run();
            case 'clearFormat': return chain.unsetAllMarks().clearNodes().unsetTextAlign().run();
            case 'unlink': return chain.extendMarkRange('link').unsetLink().run();
            case 'undo': return chain.undo().run();
            case 'redo': return chain.redo().run();
            case 'link':
                this.linkPanel.hidden = false;
                this.linkInput.value = this.editor.getAttributes('link').href || '';
                this.root.querySelector('[data-editor-link-error]').textContent = '';
                this.linkInput.removeAttribute('aria-invalid');
                this.linkInput.focus();
                break;
        }
    }

    setStyle(style, value) {
        const chain = this.editor.chain().focus();
        if (style === 'block') {
            return value === 'paragraph' ? chain.setParagraph().run() : chain.setHeading({ level: Number(value) }).run();
        }
        if (style === 'fontFamily') return value ? chain.setFontFamily(value).run() : chain.unsetFontFamily().run();
        if (style === 'fontSize') return value ? chain.setFontSize(value).run() : chain.unsetFontSize().run();
    }

    applyLink() {
        const href = this.linkInput.value.trim();
        if (!isSafeEditorLink(href)) {
            this.root.querySelector('[data-editor-link-error]').textContent = 'Use an http, https, mailto, /path, or #anchor URL.';
            this.linkInput.setAttribute('aria-invalid', 'true');
            return;
        }
        const chain = this.editor.chain().focus();
        if (this.editor.state.selection.empty && !this.editor.isActive('link')) {
            chain.insertContent({ type: 'text', text: href, marks: [{ type: 'link', attrs: { href } }] }).run();
        } else {
            chain.extendMarkRange('link').setLink({ href }).run();
        }
        this.closeLinkPanel();
    }

    closeLinkPanel() {
        this.linkPanel.hidden = true;
        this.editor.commands.focus();
    }

    refreshToolbar() {
        // The constructor can emit transactions before assigning this.editor.
        if (!this.editor) return;
        const editor = this.editor;
        this.toolbar.querySelectorAll('[aria-pressed]').forEach((button) => {
            const command = button.dataset.editorCommand;
            const active = command.startsWith('align-')
                ? editor.isActive({ textAlign: command.slice(6) })
                : editor.isActive(command);
            button.setAttribute('aria-pressed', String(active));
        });
        const enabled = {
            undo: editor.can().undo(), redo: editor.can().redo(),
            indent: editor.can().sinkListItem('listItem'), outdent: editor.can().liftListItem('listItem'),
            unlink: editor.isActive('link'), clearHighlight: editor.isActive('highlight'),
        };
        Object.entries(enabled).forEach(([command, value]) => {
            this.toolbar.querySelector(`[data-editor-command="${command}"]`).disabled = !value;
        });
        const values = {
            block: editor.isActive('heading') ? String(editor.getAttributes('heading').level) : 'paragraph',
            fontFamily: (editor.getAttributes('textStyle').fontFamily || '').replace(/["']/g, ''),
            fontSize: editor.getAttributes('textStyle').fontSize || '',
        };
        Object.entries(values).forEach(([style, value]) => {
            const select = this.toolbar.querySelector(`[data-editor-select="${style}"]`);
            select.querySelectorAll('[data-current-value]').forEach((option) => option.remove());
            if (value && !Array.from(select.options).some((option) => option.value === value)) {
                const option = new Option(value, value);
                option.dataset.currentValue = 'true';
                select.add(option);
            }
            select.value = value;
        });
        const text = editor.getText({ blockSeparator: ' ' }).trim();
        const count = text ? text.split(/\s+/u).length : 0;
        this.root.querySelector('[data-editor-word-count]').textContent = `${count} ${count === 1 ? 'word' : 'words'}`;
    }

    destroy() {
        this.events.abort();
        this.editor.destroy();
    }
}

export function mountBlogEditors() {
    for (const [root, controller] of mountedEditors) {
        if (!root.isConnected) {
            controller.destroy();
            mountedEditors.delete(root);
        }
    }
    document.querySelectorAll('[data-blog-editor]').forEach((root) => {
        if (mountedEditors.has(root)) return;
        let component = root;
        while (component && !component.hasAttribute('wire:id')) component = component.parentElement;
        const wire = component && window.Livewire?.find(component.getAttribute('wire:id'));
        if (wire) mountedEditors.set(root, new BlogRichTextEditor(root, wire));
    });
}

document.addEventListener('livewire:navigating', () => {
    mountedEditors.forEach((controller) => controller.destroy());
    mountedEditors.clear();
});
