const defaults = {
    enableFloatingChatWidget: true,
    enableGreetingSound: false,
    greetingDelayMs: 1400,
    assistantName: 'دستیار هوشمند',
    openChatPath: '/chat',
    loginPath: '/login',
};

const runtimeConfig = typeof window !== 'undefined' ? (window.SupportAIConfig || {}) : {};

export const floatingChatWidgetConfig = {
    ...defaults,
    ...runtimeConfig,
};
