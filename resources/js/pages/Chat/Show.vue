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
    files?: { path: string; url: string; filename: string }[]
}

interface Conversation {
    id: number
    title: string
    model: string
    user_id: number
    messages: Message[]
}

interface ConversationItem {
    id: number
    title: string
    model: string
    updated_at: string
    user_id: number
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
    sharedConversations: ConversationItem[]
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
const uploadedImages = ref<{ path: string; url: string; filename: string }[]>([])
const isUploading = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const textareaRef = ref<HTMLTextAreaElement | null>(null)

// ── Système de dés ──
const showDiceMenu = ref(false)

function parseDiceCommand(text: string): string | null {
    const match = text.trim().match(/^\/(\d+)?d(\d+)([+-]\d+)?$/i)
    if (!match) return null

    const count  = parseInt(match[1] ?? '1')
    const faces  = parseInt(match[2])
    const mod    = parseInt(match[3] ?? '0')

    if (![4, 6, 8, 10, 12, 20, 100].includes(faces)) return null
    if (count < 1 || count > 10) return null

    const rolls  = Array.from({ length: count }, () => Math.floor(Math.random() * faces) + 1)
    const total  = rolls.reduce((a, b) => a + b, 0) + mod

    let result = `🎲 Lancer ${count}d${faces}`
    if (mod !== 0) result += `${mod > 0 ? '+' : ''}${mod}`
    if (count > 1) result += ` [${rolls.join(', ')}]`
    result += ` = **${total}**`

    if (faces === 20 && count === 1) {
        if (rolls[0] === 20) result += ` 🌟 CRITIQUE !`
        else if (rolls[0] === 1)  result += ` 💀 ÉCHEC CRITIQUE !`
    }

    return result
}

function insertDice(faces: number) {
    input.value = `/d${faces}`
    showDiceMenu.value = false
}
async function scrollToBottom() {
    await nextTick()
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
}

function getCsrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

async function handleImageUpload(e: Event) {
    const files = (e.target as HTMLInputElement).files
    if (!files || files.length === 0) return
    isUploading.value = true
    for (const file of Array.from(files)) {
        const formData = new FormData()
        formData.append('image', file)
        try {
            const response = await fetch('/upload/image', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: formData,
            })
            const data = await response.json()
            uploadedImages.value.push({ path: data.path, url: data.url, filename: data.filename })
        } catch (error) {
            console.error('Erreur upload:', error)
        }
    }
    isUploading.value = false
    if (fileInput.value) fileInput.value.value = ''
}

function removeImage(index: number) {
    uploadedImages.value.splice(index, 1)
}

async function sendMessage() {
    if ((!input.value.trim() && uploadedImages.value.length === 0) || isStreaming.value) return

    const userContent = input.value.trim()
    const imagesToSend = [...uploadedImages.value]
    input.value = ''
    uploadedImages.value = []

    messages.value.push({
        id: Date.now(),
        role: 'user',
        content: userContent,
        created_at: new Date().toISOString(),
        files: imagesToSend.length > 0 ? imagesToSend : undefined,
    })

    await scrollToBottom()

    if (selectedModel.value !== props.conversation.model) {
        await fetch(`/conversations/${props.conversation.id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
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
            body: JSON.stringify({
                content: userContent,
                images: imagesToSend.map(img => img.path),
            }),
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
                        if (data.title) conversationTitle.value = data.title
                        streamingContent.value = ''
                        isStreaming.value = false
                        await scrollToBottom()
                        await nextTick()
                        textareaRef.value?.focus()
                    } else if (data.content) {
                        streamingContent.value += data.content
                        await scrollToBottom()
                    }
                } catch {
                    // ignorer
                }
            }
        }
    } catch (error) {
        console.error('Erreur streaming:', error)
            isStreaming.value = false
            streamingContent.value = ''
            await nextTick()
            textareaRef.value?.focus()
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
    return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        const diceResult = parseDiceCommand(input.value)
        if (diceResult) {
            input.value = diceResult
        }
        sendMessage()
    }
    // Fermer le menu dés avec Escape
    if (e.key === 'Escape') {
        showDiceMenu.value = false
    }
}

onMounted(() => { scrollToBottom() })
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
                    <p class="text-xs text-gray-400 dark:text-gray-500 epic:text-amber-500 mt-0.5">
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
                        <option v-for="model in models" :key="model.id" :value="model.id">
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

                    <!-- Mes conversations -->
                    <p class="text-xs font-medium text-gray-400 epic:text-amber-600 uppercase tracking-wide px-3 mb-2 mt-2">
                        🗡️ Mes quêtes
                    </p>
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
                                <p class="text-xs text-gray-400 epic:text-amber-600 mt-0.5">
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

                    <!-- Conversations partagées -->
                    <div v-if="sharedConversations.length > 0" class="mt-4">
                        <p class="text-xs font-medium text-gray-400 epic:text-amber-600 uppercase tracking-wide px-3 mb-2">
                            🔗 Partagées avec moi
                        </p>
                        <template v-for="conv in sharedConversations" :key="`shared-${conv.id}`">
                            <div
                                class="group flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 epic:hover:bg-amber-900/30 transition-colors mb-1 cursor-pointer"
                                :class="{ 'bg-purple-50 dark:bg-purple-900/20 epic:bg-amber-800/20': conv.id === conversation.id }"
                                @click="router.visit(`/conversations/${conv.id}`)"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white epic:text-amber-200 truncate">
                                        🔗 {{ conv.title }}
                                    </p>
                                    <p class="text-xs text-gray-400 epic:text-amber-600 mt-0.5">
                                        {{ formatDate(conv.updated_at) }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>

                </nav>
            </aside>

            <!-- Zone de chat -->
            <main class="flex-1 flex flex-col overflow-hidden">

                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 epic:bg-amber-900/40 border-b border-gray-200 dark:border-gray-700 epic:border-amber-800/50 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⚔️</span>
                        <h2 class="font-semibold text-gray-900 dark:text-white epic:text-amber-300 truncate">
                            {{ conversationTitle }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="router.visit(`/conversations/${conversation.id}/share`)"
                            class="text-xs text-gray-400 epic:text-amber-500 bg-gray-100 dark:bg-gray-700 epic:bg-amber-900/50 hover:bg-gray-200 dark:hover:bg-gray-600 px-3 py-1.5 rounded-full transition-colors"
                        >
                            🔗 Partager
                        </button>
                        <span class="text-xs text-gray-400 epic:text-amber-500 bg-gray-100 dark:bg-gray-700 epic:bg-amber-900/50 px-2 py-1 rounded-full">
                            {{ selectedModel }}
                        </span>
                    </div>
                </div>

                <!-- Messages -->
                <div ref="messagesContainer" class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
                    <div v-if="messages.length === 0" class="text-center mt-12">
                        <p class="text-5xl mb-4">🎲</p>
                        <p class="text-gray-400 dark:text-gray-500 epic:text-amber-500 text-lg font-medium">
                            Lance les dés et commence ton aventure !
                        </p>
                        <p class="text-gray-300 dark:text-gray-600 epic:text-amber-600 text-sm mt-2">
                            🐉 Des donjons mystérieux t'attendent...
                        </p>
                    </div>

                    <template v-for="message in messages" :key="message.id">
                        <div
                            class="flex flex-col"
                            :class="message.role === 'user' ? 'items-end' : 'items-start'"
                        >
                            <span class="text-xs text-gray-400 epic:text-amber-500 mb-1 px-1">
                                {{ message.role === 'user' ? '🧙 Aventurier' : '🎲 Maître de Jeu' }}
                            </span>

                            <!-- Message utilisateur -->
                            <div
                                v-if="message.role === 'user'"
                                class="max-w-2xl rounded-2xl rounded-br-sm px-4 py-3 text-sm leading-relaxed bg-purple-600 epic:bg-amber-600 text-white"
                            >
                                <div v-if="message.files && message.files.length > 0" class="flex flex-wrap gap-2 mb-2">
                                    <img
                                        v-for="file in message.files"
                                        :key="file.path"
                                        :src="file.url"
                                        :alt="file.filename"
                                        class="max-w-xs max-h-48 rounded-lg object-cover"
                                    />
                                </div>
                                <span v-if="message.content" class="whitespace-pre-wrap">{{ message.content }}</span>
                            </div>

                            <!-- Message assistant -->
                            <div
                                v-else
                                class="max-w-2xl rounded-2xl rounded-bl-sm px-4 py-3 text-sm leading-relaxed bg-white dark:bg-gray-800 epic:bg-amber-800/50 text-gray-900 dark:text-white epic:text-black border border-gray-200 dark:border-gray-700 epic:border-amber-700/50"
                            >
                                <div v-html="renderMarkdown(message.content)" class="prose prose-sm dark:prose-invert max-w-none" />
                            </div>

                            <span
                                v-if="message.role === 'assistant' && message.tokens_used"
                                class="text-xs text-gray-400 epic:text-amber-500 mt-1 px-1"
                            >
                                ⚡ {{ message.tokens_used }} tokens
                            </span>
                        </div>
                    </template>

                    <!-- Streaming -->
                    <div v-if="isStreaming" class="flex justify-start">
                        <div class="flex flex-col items-start">
                            <span class="text-xs text-gray-400 epic:text-amber-500 mb-1 px-1">🎲 Maître de Jeu</span>
                            <div class="max-w-2xl bg-white dark:bg-gray-800 epic:bg-amber-600/50 border border-gray-200 dark:border-gray-700 epic:border-amber-700/50 rounded-2xl rounded-bl-sm px-4 py-3 text-sm leading-relaxed text-gray-900 dark:text-white epic:text-black-900">
                                <div v-if="streamingContent" v-html="renderMarkdown(streamingContent)" class="prose prose-sm dark:prose-invert max-w-none" />
                                <span v-else class="flex gap-1 items-center">
                                    <span class="w-2 h-2 bg-purple-400 epic:bg-amber-600 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                    <span class="w-2 h-2 bg-purple-400 epic:bg-amber-600 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                    <span class="w-2 h-2 bg-purple-400 epic:bg-amber-600 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview images -->
                <div v-if="uploadedImages.length > 0" class="px-6 py-2 bg-white dark:bg-gray-800 epic:bg-amber-600/40 border-t border-gray-100 dark:border-gray-700 epic:border-amber-800/50 flex gap-2 flex-wrap">
                    <div v-for="(img, index) in uploadedImages" :key="img.path" class="relative group">
                        <img :src="img.url" :alt="img.filename" class="h-16 w-16 object-cover rounded-lg border border-gray-200 dark:border-gray-600" />
                        <button
                            @click="removeImage(index)"
                            class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            &times;
                        </button>
                    </div>
                </div>

                <!-- Input -->
                <!-- Input -->
<div class="bg-white dark:bg-gray-800 epic:bg-amber-900/40 border-t border-gray-200 dark:border-gray-700 epic:border-amber-800/50 px-6 py-4 shrink-0">

    <!-- Aide commandes dés -->
    <div
        v-if="input.startsWith('/')"
        class="mb-2 bg-gray-50 dark:bg-gray-700 epic:bg-amber-900/50 rounded-xl border border-gray-200 dark:border-gray-600 epic:border-amber-700 p-3"
    >
        <p class="text-xs text-gray-500 dark:text-gray-400 epic:text-amber-500 font-medium mb-2">
            🎲 Commandes de dés disponibles :
        </p>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="faces in [4, 6, 8, 10, 12, 20, 100]"
                :key="faces"
                @click="input = `/d${faces}`"
                class="text-xs bg-purple-100 dark:bg-purple-900/30 epic:bg-amber-700/30 text-purple-700 dark:text-purple-300 epic:text-amber-300 px-2 py-1 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-800/40 transition-colors font-mono"
            >
                /d{{ faces }}
            </button>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 epic:text-amber-600 mt-2">
            Exemples : /d20 · /d6 · /3d6 · /d20+5 · /2d8-1
        </p>
    </div>

    <!-- Menu dés rapide -->
    <div
        v-if="showDiceMenu"
        class="mb-2 bg-white dark:bg-gray-700 epic:bg-amber-900/50 rounded-xl border border-gray-200 dark:border-gray-600 epic:border-amber-700 p-3 shadow-lg"
    >
        <p class="text-xs text-gray-500 dark:text-gray-400 epic:text-amber-500 font-medium mb-2">
            Lancer rapidement :
        </p>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="faces in [4, 6, 8, 10, 12, 20, 100]"
                :key="faces"
                @click="insertDice(faces)"
                class="text-sm bg-purple-600 epic:bg-amber-600 hover:bg-purple-700 epic:hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg transition-colors font-mono font-bold"
            >
                d{{ faces }}
            </button>
        </div>
    </div>

    <div class="flex gap-3 items-end">
        <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/png,image/gif,image/webp"
            multiple
            class="hidden"
            @change="handleImageUpload"
        />

        <!-- Bouton image -->
        <button
            @click="fileInput?.click()"
            :disabled="isStreaming || isUploading"
            class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 epic:bg-amber-800/50 hover:bg-gray-200 dark:hover:bg-gray-600 epic:hover:bg-amber-700/50 text-gray-500 dark:text-gray-400 epic:text-amber-400 rounded-xl px-3 py-3 text-lg transition-colors disabled:opacity-50"
            title="Joindre une image"
        >
            <span v-if="isUploading">⏳</span>
            <span v-else>🖼️</span>
        </button>

        <!-- Bouton dés -->
        <button
            @click="showDiceMenu = !showDiceMenu"
            :disabled="isStreaming"
            class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 epic:bg-amber-800/50 hover:bg-gray-200 dark:hover:bg-gray-600 epic:hover:bg-amber-700/50 text-gray-500 dark:text-gray-400 epic:text-amber-400 rounded-xl px-3 py-3 text-lg transition-colors disabled:opacity-50"
            title="Lancer des dés"
        >
            🎲
        </button>

        <textarea
            ref="textareaRef"
            v-model="input"
            @keydown="handleKeydown"
            :disabled="isStreaming"
            placeholder="Parle à ton Maître de Jeu... ou tape / pour lancer des dés"
            rows="1"
            class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 epic:border-amber-700 bg-gray-50 dark:bg-gray-700 epic:bg-amber-900/50 text-gray-900 dark:text-white epic:text-amber-100 placeholder:text-gray-400 epic:placeholder:text-amber-600 px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 epic:focus:ring-amber-500 focus:outline-none disabled:opacity-50"
        />

        <button
            @click="sendMessage"
            :disabled="isStreaming || (!input.trim() && uploadedImages.length === 0)"
            class="bg-purple-600 hover:bg-purple-700 epic:bg-amber-600 epic:hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl px-5 py-3 text-sm font-bold transition-colors"
        >
            <span v-if="isStreaming">🎲</span>
            <span v-else>⚔️ Agir</span>
        </button>
    </div>
</div>

            </main>

            <!-- Toast suppression -->
            <div v-if="showDeleteSuccess" class="fixed bottom-6 right-6 bg-green-700 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 z-50">
                <span>⚔️ Quête abandonnée</span>
                <button @click="showDeleteSuccess = false" class="text-white hover:text-green-200 text-lg leading-none">&times;</button>
            </div>

        </div>
    </AppLayout>
</template>
