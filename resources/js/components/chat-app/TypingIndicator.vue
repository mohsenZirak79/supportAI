<template>
    <div class="cg-msg-row cg-msg-row--bot cg-typing" role="status" aria-live="polite">
        <div class="cg-msg-bubble cg-typing__bubble">
            <span class="cg-typing__dot" />
            <span class="cg-typing__dot" />
            <span class="cg-typing__dot" />
        </div>
    </div>
</template>

<style scoped>
.cg-typing__bubble {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 14px 18px;
    background: var(--cg-surface-2, #f1f5f9);
    border: 1px solid var(--cg-border, rgba(15, 23, 42, 0.08));
    box-shadow: none;
}

.cg-typing__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #94a3b8;
    animation: cg-bounce 1.2s infinite ease-in-out both;
}

.cg-typing__dot:nth-child(1) {
    animation-delay: -0.24s;
}
.cg-typing__dot:nth-child(2) {
    animation-delay: -0.12s;
}

@keyframes cg-bounce {
    0%,
    80%,
    100% {
        transform: scale(0.65);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

@media (prefers-reduced-motion: reduce) {
    .cg-typing__dot {
        animation: none;
        opacity: 0.7;
    }
}
</style>
