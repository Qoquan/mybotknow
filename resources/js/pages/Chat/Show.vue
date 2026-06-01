<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useMarkdown } from '@/composables/useMarkdown'

const { renderMarkdown } = useMarkdown()

interface Message {
    id: number
    role: 'user' | 'assistant'
    content: string
    tokens_used?: number
    created_at: string
}

interface Conversation {
    id: number
    title: string
    model: string
    messages: Message[]
}

interface ConversationItem {
    id: number
    title: string
    model: string
    updated_at: string
}

interface Model {
    id: string
    name: string
    description: string
    provider: string
}

const props = defineProps<{
    conversation: Conversation
    conversations: ConversationItem[]
    models: Model[]
}>()

const messages = ref<Message[]>([...props.conversation.messages])
const selectedModel = ref(props.conversation.model)
const input = ref('')
const isStreaming = ref(false)
const streamingContent = ref('')
const messagesContainer = ref<HTMLElement | null>(null)
const conversationTitle = ref(props.conversation.title)
const showDeleteSuccess = ref(false)

async function scrollToBottom() {
    await nextTick()
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
}

function getCsrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

async function sendMessage() {
    if (!input.value.trim() || isStreaming.value) return

    const userContent = input.value.trim()
    input.value = ''

    messages.value.push({
        id: Date.now(),
        role: 'user',
        content: userContent,
        created_at: new Date().toISOString(),
    })

    await scrollToBottom()

    if (selectedModel.value !== props.conversation.model) {
        await fetch(`/conversations/${props.conversation.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ model: selectedModel.value }),
        })
    }

    isStreaming.value = true
    streamingContent.value = ''

    try {
        const response = await fetch(`/conversations/${props.conversation.id}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'text/event-stream',
            },
            body: JSON.stringify({ content: userContent }),
        })

        const reader = response.body!.getReader()
        const decoder = new TextDecoder()
        let buffer = ''

        const streamingTimeout = setTimeout(() => {
            isStreaming.value = false
            streamingContent.value = ''
        }, 60000)

        while (true) {
            const { done, value } = await reader.read()

            if (done) {
                clearTimeout(streamingTimeout)
                if (streamingContent.value) {
                    messages.value.push({
                        id: Date.now(),
                        role: 'assistant',
                        content: streamingContent.value,
                        created_at: new Date().toISOString(),
                    })
                    streamingContent.value = ''
                }
                isStreaming.value = false
                await scrollToBottom()
                break
            }

            buffer += decoder.decode(value, { stream: true })
            const lines = buffer.split('\n')
            buffer = lines.pop() ?? ''

            for (const line of lines) {
                if (!line.startsWith('data: ')) continue
                const jsonStr = line.slice(6).trim()
                if (!jsonStr) continue

                try {
                    const data = JSON.parse(jsonStr)

                    if (data.done) {
                        clearTimeout(streamingTimeout)
                        const savedContent = streamingContent.value

                        messages.value.push({
                            id: data.message_id,
                            role: 'assistant',
                            content: savedContent,
                            created_at: new Date().toISOString(),
                        })

                        if (data.title) {
                            conversationTitle.value = data.title
                        }

                        streamingContent.value = ''
                        isStreaming.value = false
                        await scrollToBottom()

                    } else if (data.content) {
                        streamingContent.value += data.content
                        await scrollToBottom()
                    }
                } catch {
                    // ignorer les lignes non JSON
                }
            }
        }
    } catch (error) {
        console.error('Erreur streaming:', error)
        isStreaming.value = false
        streamingContent.value = ''
    }
}

function deleteConversation(id: number, e: Event) {
    e.preventDefault()
    e.stopPropagation()
    if (!confirm('Abandonner cette quête ?')) return

    router.delete(`/conversations/${id}`, {
        onSuccess: () => {
            if (id === props.conversation.id) {
                router.visit('/chat')
            } else {
                showDeleteSuccess.value = true
                setTimeout(() => showDeleteSuccess.value = false, 3000)
            }
        },
    })
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
    })
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        sendMessage()
    }
}

onMounted(() => {
    scrollToBottom()
})
</script>

<template>
    <AppLayout :title="conversationTitle">
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900 epic:bg-amber-950 overflow-hidden">

            <!-- Sidebar -->
            <aside class="w-72 bg-white dark:bg-gray-800 epic:bg-amber-900/30 border-r border-gray-200 dark:border-gray-700 epic:border-amber-800/50 flex flex-col shrink-0">

                <div class="p-4 border-b border-gray-200 dark:border-gray-700 epic:border-amber-800/50">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white epic:text-amber-300 flex items-center gap-2">
                        🎲 QuestMaster
                    </h1>
                    <p class="text-xs text-gray-400 dark:text-gray-500 epic:text-amber-600 mt-0.5">
                        Ton Maître de Jeu IA
                    </p>
                </div>

                <!-- Sélecteur de modèle -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 epic:border-amber-800/50">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 epic:text-amber-500 mb-2 uppercase tracking-wide">
                        ⚡ Puissance du sort
                    </label>
                    <select
                        v-model="selectedModel"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 epic:border-amber-700 bg-white dark:bg-gray-700 epic:bg-amber-900/50 text-gray-900 dark:text-white epic:text-amber-200 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                    >
                        <option
                            v-for="model in models"
                            :key="model.id"
                            :value="model.id"
                        >
                            {{ model.name }} — {{ model.description }}
                        </option>
                    </select>
                </div>

                <!-- Nouvelle quête -->
                <div class="p-4">
                    <button
                        @click="router.visit('/chat')"
                        class="w-full flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 epic:bg-amber-600 epic:hover:bg-amber-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    >
                        ⚔️ Nouvelle quête
                    </button>
                </div>

                <!-- Liste des quêtes -->
                <nav class="flex-1 overflow-y-auto px-2 pb-4">
                    <template v-for="conv in conversations" :key="conv.id">
                        <div
                            class="group flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 epic:hover:bg-amber-900/30 transition-colors mb-1 cursor-pointer"
                            :class="{ 'bg-purple-50 dark:bg-purple-900/20 epic:bg-amber-800/20': conv.id === conversation.id }"
                            @click="router.visit(`/conversations/${conv.id}`)"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white epic:text-amber-200 truncate">
                                    {{ conv.title }}
                                </p>
                                <p class="text-xs text-gray-400 epic:text-amber-700 mt-0.5">
                                    {{ formatDate(conv.updated_at) }}
                                </p>
                            </div>
                            <button
                                @click.stop="deleteConversation(conv.id, $event)"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-all ml-2 text-lg leading-none"
                            >
                                &times;
                            </button>
                        </div>
                    </template>
                </nav>

            </aside>

            <!-- Zone de chat -->
            <main class="flex-1 flex flex-col overflow-hidden">

                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 epic:bg-amber-900/30 border-b border-gray-200 dark:border-gray-700 epic:border-amber-800/50 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⚔️</span>
                        <h2 class="font-semibold text-gray-900 dark:text-white epic:text-amber-300 truncate">
                            {{ conversationTitle }}
                        </h2>
                    </div>
                    <span class="text-xs text-gray-400 epic:text-amber-600 bg-gray-100 dark:bg-gray-700 epic:bg-amber-900/50 px-2 py-1 rounded-full">
                        {{ selectedModel }}
                    </span>
                </div>

                <!-- Messages -->
                <div
                    ref="messagesContainer"
                    class="flex-1 overflow-y-auto px-6 py-4 space-y-6"
                >
                    <div v-if="messages.length === 0" class="text-center mt-12">
                        <p class="text-5xl mb-4">🎲</p>
                        <p class="text-gray-400 dark:text-gray-500 epic:text-amber-700 text-lg font-medium">
                            Lance les dés et commence ton aventure !
                        </p>
                        <p class="text-gray-300 dark:text-gray-600 epic:text-amber-800 text-sm mt-2">
                            🐉 Des donjons mystérieux t'attendent...
                        </p>
                    </div>

                    <template v-for="message in messages" :key="message.id">
                        <div
                            class="flex flex-col"
                            :class="message.role === 'user' ? 'items-end' : 'items-start'"
                        >
                            <span class="text-xs text-gray-400 epic:text-amber-700 mb-1 px-1">
                                {{ message.role === 'user' ? '🧙 Aventurier' : '🎲 Maître de Jeu' }}
                            </span>

                            <div
                                v-if="message.role === 'user'"
                                class="max-w-2xl rounded-2xl rounded-br-sm px-4 py-3 text-sm leading-relaxed whitespace-pre-wrap bg-purple-600 epic:bg-amber-600 text-white"
                            >
                                {{ message.content }}
                            </div>

                            <div
                                v-else
                                class="max-w-2xl rounded-2xl rounded-bl-sm px-4 py-3 text-sm leading-relaxed bg-white dark:bg-gray-800 epic:bg-amber-900/40 text-gray-900 dark:text-white epic:text-amber-100 border border-gray-200 dark:border-gray-700 epic:border-amber-800/50"
                            >
                                <div
                                    v-html="renderMarkdown(message.content)"
                                    class="prose prose-sm dark:prose-invert max-w-none"
                                />
                            </div>

                            <span
                                v-if="message.role === 'assistant' && message.tokens_used"
                                class="text-xs text-gray-400 epic:text-amber-700 mt-1 px-1"
                            >
                                ⚡ {{ message.tokens_used }} tokens
                            </span>
                        </div>
                    </template>

                    <!-- Streaming -->
                    <div v-if="isStreaming" class="flex justify-start">
                        <div class="flex flex-col items-start">
                            <span class="text-xs text-gray-400 epic:text-amber-700 mb-1 px-1">🎲 Maître de Jeu</span>
                            <div class="max-w-2xl bg-white dark:bg-gray-800 epic:bg-amber-900/40 border border-gray-200 dark:border-gray-700 epic:border-amber-800/50 rounded-2xl rounded-bl-sm px-4 py-3 text-sm leading-relaxed text-gray-900 dark:text-white epic:text-amber-100">
                                <div
                                    v-if="streamingContent"
                                    v-html="renderMarkdown(streamingContent)"
                                    class="prose prose-sm dark:prose-invert max-w-none"
                                />
                                <span v-else class="flex gap-1 items-center">
                                    <span class="w-2 h-2 bg-purple-400 epic:bg-amber-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                    <span class="w-2 h-2 bg-purple-400 epic:bg-amber-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                    <span class="w-2 h-2 bg-purple-400 epic:bg-amber-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Input -->
                <div class="bg-white dark:bg-gray-800 epic:bg-amber-900/30 border-t border-gray-200 dark:border-gray-700 epic:border-amber-800/50 px-6 py-4">
                    <div class="flex gap-3 items-end">
                        <textarea
                            v-model="input"
                            @keydown="handleKeydown"
                            :disabled="isStreaming"
                            placeholder="Parle à ton Maître de Jeu... (Entrée pour agir, Maj+Entrée pour un saut de ligne)"
                            rows="1"
                            class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 epic:border-amber-700 bg-gray-50 dark:bg-gray-700 epic:bg-amber-900/50 text-gray-900 dark:text-white epic:text-amber-100 px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 epic:focus:ring-amber-500 focus:outline-none disabled:opacity-50"
                        />
                        <button
                            @click="sendMessage"
                            :disabled="isStreaming || !input.trim()"
                            class="bg-purple-600 hover:bg-purple-700 epic:bg-amber-600 epic:hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl px-5 py-3 text-sm font-bold transition-colors"
                        >
                            <span v-if="isStreaming">🎲</span>
                            <span v-else>⚔️ Agir</span>
                        </button>
                    </div>
                </div>

            </main>

            <!-- Toast suppression -->
            <div
                v-if="showDeleteSuccess"
                class="fixed bottom-6 right-6 bg-green-700 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 z-50"
            >
                <span>⚔️ Quête abandonnée</span>
                <button
                    @click="showDeleteSuccess = false"
                    class="text-white hover:text-green-200 text-lg leading-none"
                >
                    &times;
                </button>
            </div>

        </div>
    </AppLayout>
</template>
