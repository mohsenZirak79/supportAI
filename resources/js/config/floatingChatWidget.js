const defaults = {
    enableFloatingChatWidget: true,
    enableGreetingSound: false,
    greetingDelayMs: 1400,
    greetingVisibleMs: 5200,
    assistantName: 'دستیار هوشمند',
    openChatPath: '/chat',
};

const runtimeConfig = typeof window !== 'undefined' ? (window.SupportAIConfig || {}) : {};

export const floatingChatWidgetConfig = {
    ...defaults,
    ...runtimeConfig,
};
