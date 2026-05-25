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

        while (true) {
            const { done, value } = await reader.read()
            if (done) break

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
    router.delete(`/conversations/${id}`, {
        preserveScroll: true,
        onSuccess: () => router.visit('/chat'),
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
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900 overflow-hidden">

            <!-- Sidebar -->
            <aside class="w-72 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col shrink-0">

                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        🤖 MyBotKnows
                    </h1>
                </div>

                <!-- Sélecteur de modèle -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">
                        Modèle IA
                    </label>
                    <select
                        v-model="selectedModel"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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

                <!-- Nouvelle conversation -->
                <div class="p-4">
                    <button
                        @click="router.visit('/chat')"
                        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    >
                        + Nouvelle conversation
                    </button>
                </div>

                <!-- Liste des conversations -->
                <nav class="flex-1 overflow-y-auto px-2 pb-4">
                    <template v-for="conv in conversations" :key="conv.id">
                        <div
                            class="group flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors mb-1 cursor-pointer"
                            :class="{ 'bg-blue-50 dark:bg-blue-900/30': conv.id === conversation.id }"
                            @click="router.visit(`/conversations/${conv.id}`)"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ conv.title }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
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
                <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900 dark:text-white truncate">
                        {{ conversationTitle }}
                    </h2>
                    <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                        {{ selectedModel }}
                    </span>
                </div>

                <!-- Messages -->
                <div
                    ref="messagesContainer"
                    class="flex-1 overflow-y-auto px-6 py-4 space-y-6"
                >
                    <p v-if="messages.length === 0" class="text-center text-gray-400 mt-12">
                        Envoie un message pour commencer !
                    </p>

                    <template v-for="message in messages" :key="message.id">
                        <div
                            class="flex flex-col"
                            :class="message.role === 'user' ? 'items-end' : 'items-start'"
                        >
                            <!-- Message utilisateur -->
                            <div
                                v-if="message.role === 'user'"
                                class="max-w-2xl rounded-2xl rounded-br-sm px-4 py-3 text-sm leading-relaxed whitespace-pre-wrap bg-blue-600 text-white"
                            >
                                {{ message.content }}
                            </div>

                            <!-- Message assistant avec Markdown -->
                            <div
                                v-else
                                class="max-w-2xl rounded-2xl rounded-bl-sm px-4 py-3 text-sm leading-relaxed bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700"
                            >
                                <div
                                    v-html="renderMarkdown(message.content)"
                                    class="prose prose-sm dark:prose-invert max-w-none"
                                />
                            </div>

                            <!-- Tokens -->
                            <span
                                v-if="message.role === 'assistant' && message.tokens_used"
                                class="text-xs text-gray-400 mt-1 px-1"
                            >
                                {{ message.tokens_used }} tokens
                            </span>
                        </div>
                    </template>

                    <!-- Message en streaming -->
                    <div v-if="isStreaming" class="flex justify-start">
                        <div class="max-w-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-sm px-4 py-3 text-sm leading-relaxed text-gray-900 dark:text-white">
                            <div
                                v-if="streamingContent"
                                v-html="renderMarkdown(streamingContent)"
                                class="prose prose-sm dark:prose-invert max-w-none"
                            />
                            <span v-else class="flex gap-1 items-center">
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Input -->
                <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex gap-3 items-end">
                        <textarea
                            v-model="input"
                            @keydown="handleKeydown"
                            :disabled="isStreaming"
                            placeholder="Envoie un message... (Entrée pour envoyer, Maj+Entrée pour saut de ligne)"
                            rows="1"
                            class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:opacity-50"
                        />
                        <button
                            @click="sendMessage"
                            :disabled="isStreaming || !input.trim()"
                            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-xl px-5 py-3 text-sm font-medium transition-colors"
                        >
                            <span v-if="isStreaming">...</span>
                            <span v-else>Envoyer</span>
                        </button>
                    </div>
                </div>

            </main>
        </div>
    </AppLayout>
</template>
