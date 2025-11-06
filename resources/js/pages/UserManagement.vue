<script setup lang="ts">
import { ref, computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import UserListTab from '@/components/UserManagement/UserListTab.vue'
import AddUserTab from '@/components/UserManagement/AddUserTab.vue'
import UserOverviewTab from '@/components/UserManagement/UserOverviewTab.vue'

// Props
interface User {
    id: number
    name: string
    email: string
    student_id: string
    program: string
    year: string
    role: 'Member' | 'Officer' | 'Admin'
    status: 'active' | 'inactive'
    last_login: string
}

const props = defineProps<{
    users: User[]
}>()

const activeTab = ref("overview")

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Management', href: '/users' },
]

const tabs = [
    { key: "overview", label: "User Overview", icon: "chart" },
    { key: "users", label: "User List", icon: "users" },
    { key: "add", label: "Add User", icon: "plus" },
]

// Computed
const userStats = computed(() => {
    return {
        total: props.users.length,
        active: props.users.filter(u => u.status === 'active').length,
        inactive: props.users.filter(u => u.status === 'inactive').length,
        admins: props.users.filter(u => u.role === 'Admin').length,
        officers: props.users.filter(u => u.role === 'Officer').length,
        members: props.users.filter(u => u.role === 'Member').length,
    }
})

// Methods
const switchToUserList = () => {
    activeTab.value = "users"
}

const switchToAddUser = () => {
    activeTab.value = "add"
}

// Tab icons
const getTabIcon = (iconType: string) => {
    const icons = {
        chart: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>`,
        users: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>`,
        plus: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>`
    }
    return (icons as Record<string,string>)[iconType] ?? icons.chart

}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 text-gray-200">
            <!-- Tabs Navigation -->
            <div class="flex space-x-6 border-b border-gray-800 mb-6">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="pb-2 flex items-center gap-2 transition-all duration-200"
                    :class="[
                        activeTab === tab.key
                            ? 'border-b-2 border-black text-red-600'
                            : 'text-blue-500 hover:text-red-600'
                    ]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <g v-html="getTabIcon(tab.icon)"></g>
                    </svg>
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tab Content -->
            <div class="min-h-96">
                <UserOverviewTab 
                    v-if="activeTab === 'overview'"
                    :users="users"
                    :stats="userStats"
                    @switch-to-users="switchToUserList"
                    @switch-to-add="switchToAddUser"
                />
                
                <UserListTab 
                    v-else-if="activeTab === 'users'"
                    :users="users"
                />
                
                <AddUserTab 
                    v-else-if="activeTab === 'add'"
                    @user-created="switchToUserList"
                />
            </div>
        </div>
    </AppLayout>
</template>