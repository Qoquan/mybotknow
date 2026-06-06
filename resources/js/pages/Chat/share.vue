<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Member {
    id: number
    name: string
    email: string
    role: string
    joined_at: string
}

interface Conversation {
    id: number
    title: string
    model: string
    user_id: number
}

const props = defineProps<{
    conversation: Conversation
    members: Member[]
}>()

const emailInput = ref('')
const isInviting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

function invite() {
    if (!emailInput.value.trim()) return
    isInviting.value = true
    errorMessage.value = ''
    successMessage.value = ''

    router.post(`/conversations/${props.conversation.id}/invite`, {
        email: emailInput.value.trim(),
    }, {
        onSuccess: () => {
            successMessage.value = `Invitation envoyée !`
            emailInput.value = ''
        },
        onError: (errors) => {
            errorMessage.value = errors.email ?? 'Une erreur est survenue.'
        },
        onFinish: () => {
            isInviting.value = false
        },
    })
}

function removeMember(userId: number, name: string) {
    if (!confirm(`Retirer ${name} de la conversation ?`)) return

    router.delete(`/conversations/${props.conversation.id}/members/${userId}`, {
        onSuccess: () => {
            successMessage.value = `${name} a été retiré.`
        },
    })
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}
</script>

<template>
    <AppLayout title="Partager la conversation">
        <div class="max-w-2xl mx-auto px-6 py-8">

            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white epic:text-amber-300">
                        🔗 Partager la quête
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 epic:text-amber-500 mt-1 truncate">
                        {{ conversation.title }}
                    </p>
                </div>
                <button
                    @click="router.visit(`/conversations/${conversation.id}`)"
                    class="text-sm text-purple-600 dark:text-purple-400 epic:text-amber-400 hover:underline font-medium"
                >
                    ← Retour au chat
                </button>
            </div>

            <!-- Message succès -->
            <div
                v-if="successMessage"
                class="mb-4 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl border border-green-200 dark:border-green-800 flex items-center justify-between"
            >
                <span>✅ {{ successMessage }}</span>
                <button @click="successMessage = ''" class="text-green-500 hover:text-green-700">×</button>
            </div>

            <!-- Message erreur -->
            <div
                v-if="errorMessage"
                class="mb-4 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl border border-red-200 dark:border-red-800 flex items-center justify-between"
            >
                <span>❌ {{ errorMessage }}</span>
                <button @click="errorMessage = ''" class="text-red-500 hover:text-red-700">×</button>
            </div>

            <!-- Inviter un utilisateur -->
            <div class="bg-white dark:bg-gray-800 epic:bg-amber-900/30 rounded-2xl border border-gray-200 dark:border-gray-700 epic:border-amber-800/50 p-6 mb-6">
                <h2 class="font-semibold text-gray-900 dark:text-white epic:text-amber-300 mb-1">
                    ⚔️ Inviter un aventurier
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 epic:text-amber-500 mb-4">
                    Entre l'adresse email d'un utilisateur QuestMaster pour l'inviter.
                </p>

                <div class="flex gap-3">
                    <input
                        v-model="emailInput"
                        type="email"
                        placeholder="aventurier@royaume.com"
                        @keydown.enter="invite"
                        class="flex-1 rounded-xl border border-gray-300 dark:border-gray-600 epic:border-amber-700 bg-gray-50 dark:bg-gray-700 epic:bg-amber-900/50 text-gray-900 dark:text-white epic:text-amber-100 placeholder:text-gray-400 epic:placeholder:text-amber-600 px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 epic:focus:ring-amber-500 focus:outline-none"
                    />
                    <button
                        @click="invite"
                        :disabled="isInviting || !emailInput.trim()"
                        class="bg-purple-600 hover:bg-purple-700 epic:bg-amber-600 epic:hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl px-5 py-2 text-sm font-medium transition-colors"
                    >
                        <span v-if="isInviting">Invitation...</span>
                        <span v-else>Inviter</span>
                    </button>
                </div>
            </div>

            <!-- Liste des membres -->
            <div class="bg-white dark:bg-gray-800 epic:bg-amber-900/30 rounded-2xl border border-gray-200 dark:border-gray-700 epic:border-amber-800/50 p-6">
                <h2 class="font-semibold text-gray-900 dark:text-white epic:text-amber-300 mb-4">
                    🧙 Membres de la quête
                </h2>

                <div v-if="members.length === 0" class="text-center py-8">
                    <p class="text-gray-400 epic:text-amber-600">
                        Aucun membre pour l'instant — invite des aventuriers !
                    </p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="member in members"
                        :key="member.id"
                        class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 epic:bg-amber-800/20 border border-gray-100 dark:border-gray-700 epic:border-amber-800/30"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-900/30 epic:bg-amber-700/30 flex items-center justify-center text-purple-600 dark:text-purple-400 epic:text-amber-400 font-bold text-sm">
                                {{ member.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white epic:text-amber-200">
                                    {{ member.name }}
                                    <span
                                        v-if="member.role === 'owner'"
                                        class="ml-2 text-xs bg-purple-100 dark:bg-purple-900/30 epic:bg-amber-700/30 text-purple-600 dark:text-purple-400 epic:text-amber-400 px-2 py-0.5 rounded-full"
                                    >
                                        Propriétaire
                                    </span>
                                </p>
                                <p class="text-xs text-gray-400 epic:text-amber-600">
                                    {{ member.email }} · Rejoint le {{ formatDate(member.joined_at) }}
                                </p>
                            </div>
                        </div>

                        <button
                            v-if="member.role !== 'owner'"
                            @click="removeMember(member.id, member.name)"
                            class="text-gray-400 hover:text-red-500 transition-colors text-sm px-3 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                        >
                            Retirer
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
