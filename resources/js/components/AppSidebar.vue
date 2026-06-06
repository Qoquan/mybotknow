<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Bot, BookOpen, FolderGit2, MessageSquare, Settings, User } from 'lucide-vue-next'
import NavFooter from '@/components/NavFooter.vue'
import NavUser from '@/components/NavUser.vue'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarGroupContent,
} from '@/components/ui/sidebar'
import { useAppearance } from '@/composables/useAppearance'
import { usePage } from '@inertiajs/vue3'
import type { NavItem } from '@/types'

const { appearance, updateAppearance } = useAppearance()
const page = usePage()

const themeIcon = computed(() => {
    if (appearance.value === 'dark') return '🌙'
    if (appearance.value === 'epic') return '⚔️'
    return '☀️'
})

const themeLabel = computed(() => {
    if (appearance.value === 'dark') return 'Donjon (Sombre)'
    if (appearance.value === 'epic') return 'Épique (Or)'
    return 'Lumière (Clair)'
})

function cycleTheme() {
    if (appearance.value === 'light' || appearance.value === 'system') updateAppearance('dark')
    else if (appearance.value === 'dark') updateAppearance('epic')
    else updateAppearance('light')
}

const conversations = computed(() => (page.props.conversations as any[]) ?? [])
const sharedConversations = computed(() => (page.props.sharedConversations as any[]) ?? [])
const currentConversationId = computed(() => (page.props.conversation as any)?.id ?? null)

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
}

const mainNavItems: NavItem[] = [
    { title: 'Quêtes', href: '/chat', icon: MessageSquare },
    { title: 'Mes Agents', href: '/agents', icon: Bot },
    { title: 'Instructions', href: '/instructions', icon: Settings },
    { title: 'Mon Héros', href: '/settings/profile', icon: User },
]

const footerNavItems: NavItem[] = [
    { title: 'GitHub', href: 'https://github.com/laravel/vue-starter-kit', icon: FolderGit2 },
    { title: 'Documentation', href: 'https://laravel.com/docs/starter-kits#vue', icon: BookOpen },
]
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">

        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/chat" class="flex items-center gap-2">
                            <span class="text-2xl">🎲</span>
                            <div class="flex flex-col leading-tight">
                                <span class="font-bold text-sm">QuestMaster</span>
                                <span class="text-xs opacity-60">Ton Maître de Jeu IA</span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <!-- Navigation principale -->
            <SidebarGroup>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="item in mainNavItems" :key="item.title">
                            <SidebarMenuButton as-child>
                                <Link :href="item.href" class="flex items-center gap-2">
                                    <component :is="item.icon" class="h-4 w-4" />
                                    <span>{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Mes conversations -->
            <SidebarGroup v-if="conversations.length > 0">
                <SidebarGroupLabel>🗡️ Mes quêtes</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="conv in conversations" :key="conv.id">
                            <SidebarMenuButton
                                as-child
                                :is-active="conv.id === currentConversationId"
                            >
                                <Link :href="`/conversations/${conv.id}`" class="flex flex-col items-start">
                                    <span class="truncate text-sm font-medium w-full">{{ conv.title }}</span>
                                    <span class="text-xs opacity-60">{{ formatDate(conv.updated_at) }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Conversations partagées -->
            <SidebarGroup v-if="sharedConversations.length > 0">
                <SidebarGroupLabel>🔗 Partagées</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="conv in sharedConversations" :key="`shared-${conv.id}`">
                            <SidebarMenuButton
                                as-child
                                :is-active="conv.id === currentConversationId"
                            >
                                <Link :href="`/conversations/${conv.id}`" class="flex flex-col items-start">
                                    <span class="truncate text-sm font-medium w-full">🔗 {{ conv.title }}</span>
                                    <span class="text-xs opacity-60">{{ formatDate(conv.updated_at) }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton @click="cycleTheme" class="cursor-pointer">
                        <span class="text-base">{{ themeIcon }}</span>
                        <span>{{ themeLabel }}</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>

    </Sidebar>
</template>
