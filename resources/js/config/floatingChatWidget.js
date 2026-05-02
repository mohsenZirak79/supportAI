const defaults = {
    enableFloatingChatWidget: true,
    assistantName: 'دستیار هوشمند',
    openChatPath: '/chat',
    loginPath: '/login',
};

const runtimeConfig = typeof window !== 'undefined' ? (window.SupportAIConfig || {}) : {};

export const floatingChatWidgetConfig = {
    ...defaults,
    ...runtimeConfig,
};
