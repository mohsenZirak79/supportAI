import { createApp } from 'vue';
import FloatingChatWidget from './components/floating-chat/FloatingChatWidget.vue';
import { floatingChatWidgetConfig } from './config/floatingChatWidget';
import { installSupportAiErrorHooks } from './lib/notifySupportAiOfferHelp';

const ROOT_ID = 'supportai-floating-chat-root';

export function mountFloatingChatWidget() {
    if (typeof window === 'undefined' || !floatingChatWidgetConfig.enableFloatingChatWidget) return;
    if (document.getElementById(ROOT_ID)) return;

    const root = document.createElement('div');
    root.id = ROOT_ID;
    document.body.appendChild(root);

    createApp(FloatingChatWidget).mount(root);
    installSupportAiErrorHooks();
}
