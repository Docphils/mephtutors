import assert from 'node:assert/strict';
import { afterEach, test } from 'node:test';
import { JSDOM } from 'jsdom';

const dom = new JSDOM('<!doctype html><body></body>', { pretendToBeVisual: true, url: 'http://localhost' });
for (const name of ['window', 'document', 'HTMLElement', 'Element', 'Node', 'MutationObserver', 'DOMParser', 'Option', 'AbortController', 'getComputedStyle']) {
    globalThis[name] = dom.window[name];
}
Object.defineProperty(globalThis, 'navigator', { configurable: true, value: dom.window.navigator });
globalThis.requestAnimationFrame = dom.window.requestAnimationFrame.bind(dom.window);
globalThis.cancelAnimationFrame = dom.window.cancelAnimationFrame.bind(dom.window);
// jsdom has no layout engine; ProseMirror needs these for scrolling the selection.
dom.window.Range.prototype.getClientRects = () => [];
dom.window.Range.prototype.getBoundingClientRect = () => ({ top: 0, left: 0, bottom: 0, right: 0 });

const { BlogRichTextEditor, isSafeEditorLink, mountBlogEditors } = await import('../../resources/js/blog-editor.js');
const controllers = [];

function createRoot(content) {
    const root = document.createElement('div');
    root.dataset.blogEditor = '';
    root.innerHTML = `
        <input data-editor-content type="hidden">
        <fieldset data-editor-toolbar disabled>
            <select data-editor-select="block"><option value="paragraph">Paragraph</option><option value="2">Heading 2</option></select>
            <select data-editor-select="fontFamily"><option value="">Default font</option><option value="Georgia">Georgia</option></select>
            <select data-editor-select="fontSize"><option value="">Default size</option><option value="24px">24 px</option></select>
            ${['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'align-left', 'align-center', 'align-right', 'align-justify', 'bulletList', 'orderedList', 'blockquote', 'code', 'codeBlock', 'link'].map((command) => `<button type="button" data-editor-command="${command}" aria-pressed="false"></button>`).join('')}
            ${['indent', 'outdent', 'horizontalRule', 'unlink', 'clearHighlight', 'clearFormat', 'undo', 'redo'].map((command) => `<button type="button" data-editor-command="${command}"></button>`).join('')}
            <input type="color" data-editor-color="color"><input type="color" data-editor-color="highlight">
        </fieldset>
        <div data-editor-link-panel hidden><input data-editor-link-url><button type="button" data-editor-link-apply></button><button type="button" data-editor-link-cancel></button><p data-editor-link-error></p></div>
        <div data-editor-surface></div><span data-editor-status></span><span data-editor-word-count></span>`;
    root.querySelector('[data-editor-content]').value = content;
    document.body.append(root);
    return root;
}

function createEditor(content) {
    const root = createRoot(content);
    const updates = [];
    const controller = new BlogRichTextEditor(root, { $set: (...args) => updates.push(args) });
    controllers.push(controller);
    return { root, controller, editor: controller.editor, updates };
}

afterEach(() => {
    document.dispatchEvent(new dom.window.Event('livewire:navigating'));
    controllers.splice(0).forEach((controller) => controller.destroy());
    document.body.replaceChildren();
});

test('loading, editing, and reopening preserves headings, lists, links, and inline formatting', () => {
    const original = '<h2 style="text-align: center">Heading</h2><p><span style="font-family: Georgia; font-size: 24px; color: #0e7490"><u>Styled text</u></span> <mark style="background-color: #fef08a">Highlight</mark> H<sub>2</sub>O x<sup>2</sup></p><ol start="3"><li><p>Item</p><ul><li><p>Nested</p></li></ul></li></ol><blockquote><p>Quote</p></blockquote><pre><code>code &lt;example&gt;</code></pre><p><a href="/blog/guide">Read more</a></p><hr>';
    const { editor, updates } = createEditor(original);
    assert.equal(updates.length, 0, 'Initialization must not overwrite a draft');
    editor.commands.insertContentAt(editor.state.doc.content.size, '<p>An additional paragraph</p>');
    const html = editor.getHTML();
    assert.match(html, /<h2 style="text-align: center;?">Heading<\/h2>/);
    assert.match(html, /font-family: Georgia/);
    assert.match(html, /font-size: 24px/);
    assert.match(html, /<u>Styled text<\/u>/);
    assert.match(html, /<mark[^>]+background-color:/);
    assert.match(html, /<sub>2<\/sub>/);
    assert.match(html, /<sup>2<\/sup>/);
    assert.match(html, /<ol start="3">/);
    assert.match(html, /<ul><li><p>Nested<\/p><\/li><\/ul>/);
    assert.match(html, /<blockquote><p>Quote<\/p><\/blockquote>/);
    assert.match(html, /<pre><code>code &lt;example&gt;<\/code><\/pre>/);
    assert.match(html, /href="\/blog\/guide"/);
    assert.deepEqual(updates.at(-1), ['body', html, false]);
    const reopened = createEditor(html);
    assert.equal(reopened.editor.getHTML(), html);
});

test('toolbar changes alignment, emphasis, font size, and colors and supports undo and redo', () => {
    const { root, controller, editor, updates } = createEditor('<p>Selected words</p>');
    editor.commands.selectAll();
    root.querySelector('[data-editor-command="align-center"]').click();
    root.querySelector('[data-editor-command="bold"]').click();
    controller.setStyle('fontFamily', 'Georgia');
    controller.setStyle('fontSize', '24px');
    const color = root.querySelector('[data-editor-color="color"]');
    color.value = '#ff0000';
    color.dispatchEvent(new dom.window.Event('change', { bubbles: true }));
    assert.match(editor.getHTML(), /text-align: center/);
    assert.match(editor.getHTML(), /<strong>/);
    assert.match(editor.getHTML(), /font-size: 24px/);
    assert.match(editor.getHTML(), /color: rgb\(255, 0, 0\)/);
    assert.equal(root.querySelector('[data-editor-command="bold"]').getAttribute('aria-pressed'), 'true');
    const formatted = editor.getHTML();
    controller.runCommand('undo');
    assert.notEqual(editor.getHTML(), formatted);
    controller.runCommand('redo');
    assert.equal(editor.getHTML(), formatted);
    assert.equal(updates.at(-1)[2], false);
    controller.runCommand('clearFormat');
    assert.equal(editor.getHTML(), '<p>Selected words</p>');
});

test('formatting on imported paragraphs and emphasis is retained when edited', () => {
    const { editor } = createEditor('<h2 style="color: #ff0000">Heading</h2><p style="font-family: Georgia; font-size: 24px"><strong style="color: #0e7490">Styled text</strong></p>');
    editor.commands.insertContentAt(editor.state.doc.content.size, '<p>New paragraph</p>');
    const surface = document.createElement('div');
    surface.innerHTML = editor.getHTML();
    assert.equal(surface.querySelector('h2 span').style.color, 'rgb(255, 0, 0)');
    assert.match(surface.querySelector('p').innerHTML, /font-family: Georgia/);
    assert.match(surface.querySelector('p').innerHTML, /font-size: 24px/);
    assert.match(surface.querySelector('p').innerHTML, /color: rgb\(14, 116, 144\)/);
});

test('link controls retain the selection, reject unsafe URLs, and remove links', () => {
    const { root, controller, editor } = createEditor('<p>Selected words</p>');
    editor.commands.selectAll();
    root.querySelector('[data-editor-command="link"]').click();
    controller.linkInput.value = 'javascript:alert(1)';
    controller.applyLink();
    assert.equal(editor.getHTML(), '<p>Selected words</p>');
    assert.equal(controller.linkInput.getAttribute('aria-invalid'), 'true');
    controller.linkInput.value = '/blog/guide';
    root.querySelector('[data-editor-link-apply]').click();
    assert.match(editor.getHTML(), /href="\/blog\/guide"[^>]*>Selected words<\/a>/);
    assert.equal(controller.linkPanel.hidden, true);
    root.querySelector('[data-editor-command="unlink"]').click();
    assert.equal(editor.getHTML(), '<p>Selected words</p>');
    for (const url of ['javascript:alert(1)', '//example.com', 'https://example.com\\path', 'data:text/html,test']) assert.equal(isSafeEditorLink(url), false);
    for (const url of ['https://example.com', '/blog/guide', '#heading', 'mailto:editor@example.com']) assert.equal(isSafeEditorLink(url), true);
});

test('AI replacement is synchronized and removed editor listeners do not update Livewire', () => {
    const { controller, editor, updates } = createEditor('<p>Original</p>');
    window.dispatchEvent(new dom.window.CustomEvent('blog-body-suggestion-applied', { detail: { body: '<h3>Suggestion</h3><p style="text-align: right">New body</p>' } }));
    assert.match(editor.getHTML(), /<h3>Suggestion<\/h3>/);
    assert.match(updates.at(-1)[1], /text-align: right/);
    controller.destroy();
    const count = updates.length;
    window.dispatchEvent(new dom.window.CustomEvent('blog-body-suggestion-applied', { detail: { body: '<p>Later</p>' } }));
    assert.equal(updates.length, count);
});

test('Livewire navigation mounts an editor once per component and cleans it up', () => {
    const component = document.createElement('section');
    component.setAttribute('wire:id', 'blog-component');
    document.body.append(component);
    const root = createRoot('<p>Draft</p>');
    component.append(root);
    assert.equal(document.querySelector('[data-blog-editor]'), root);
    window.Livewire = { find: (id) => { assert.equal(id, 'blog-component'); return { $set() {} }; } };
    mountBlogEditors();
    mountBlogEditors();
    assert.equal(root.querySelectorAll('.tiptap').length, 1);
    document.dispatchEvent(new dom.window.Event('livewire:navigating'));
    assert.equal(root.querySelectorAll('.tiptap').length, 0);
});
